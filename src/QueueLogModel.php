<?php

namespace somov\qm;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;

/**
 * This is the model class for table "{{%queue_log}}".
 *
 * @property int $id
 * @property string $channel
 * @property string $name
 * @property resource $job
 * @property string $data
 * @property int $pushed_at
 * @property int $ttr
 * @property int $delay
 * @property int $priority
 * @property int $reserved_at
 * @property int $attempt
 * @property int $done_at
 * @property int $status
 * @property int $pid
 * @property int $processed_at [timestamp]
 * @property int $type [bigint(20)]
 */
class QueueLogModel extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%queue_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['channel', 'job', 'pushed_at', 'ttr', 'delay', 'type'], 'required', 'on' => 'create'],
            [['job', 'data'], 'string'],
            [['pushed_at', 'ttr', 'delay', 'priority', 'reserved_at', 'attempt', 'done_at', 'status', 'type'], 'integer'],
            [['channel', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return QueueLogModelQuery|
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(QueueLogModelQuery::class, [static::class]);
    }

    /**
     * @return array
     */
    public static function getStatusCaptions()
    {
        return [
            QueueDbLogInterface::LOG_STATUS_EXEC => 'In progress',
            QueueDbLogInterface::LOG_STATUS_WAIT => 'Waiting',
            QueueDbLogInterface::LOG_STATUS_ERROR => 'Complete with error',
            QueueDbLogInterface::LOG_STATUS_DONE => 'Complete',
        ];
    }

    /**
     * @param  Query $query
     * @return array
     */
    public static function findStat($query = null)
    {
        $query = isset($query) ? $query : new Query();

        $query->select('[[status]], count([[status]]) as cnt ')
            ->from(self::tableName())
            ->groupBy('[[status]]');

        $raw = ArrayHelper::index($query->all(), 'status');

        $items = array_map(function ($s) {
            return [
                'caption' => $s
            ];
        }, self::getStatusCaptions());

        foreach ($items as $id => &$item) {
            $item['id'] = (int)$id;
            $item['count'] = isset($raw[$id]) ? (int)$raw[$id]['cnt'] : 0;
        }

        return $items;
    }

    /**
     * @param null|integer $statusId
     * @return string|boolean
     * @throws \Exception
     */
    public function getStatusCaption($statusId = null)
    {
        $captions = self::getStatusCaptions();

        $statusId = isset($statusId) ? $statusId : $this->status;

        return ArrayHelper::getValue($captions, $statusId, false);
    }

    /**
     * @var array
     */
    private $_data;

    /**
     * @return array
     */
    public function getData()
    {
        if (is_array($this->_data)) {
            return $this->_data;
        }

        try {
            return $this->_data = Json::decode($this->data);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setData(array $value)
    {
        $this->_data = ArrayHelper::merge($this->getData(), $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->data = Json::encode($this->_data);
            return true;
        }
        return false;
    }


    /**
     * @param string|array|\closure $attribute
     * @return bool
     * @throws \Exception
     */
    public function hasDataAttribute($attribute)
    {
        return ArrayHelper::getValue($this->getData(), $attribute, false) !== false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasDataError()
    {
        return $this->hasDataAttribute('error');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasDataProgress()
    {
        return $this->hasDataAttribute('progress');
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return ArrayHelper::merge(Json::decode($this->job), [
            'class' => new  UnsetArrayValue(),
        ]);
    }


    /**
     * @return array
     */
    public function getDetails()
    {
        $details = [
            'taskTitle' => $this->getTitle()
        ];


        if ($data = $this->getData()) {
            $details = ArrayHelper::merge($details, $data, [
                'progress' => new UnsetArrayValue()
            ]);
        }

        $details['parameters'] = $this->getParameters();

        return $details;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return trim(strtr('Task[id] name title', [
            'id' => $this->id,
            'name' => $this->name,
            'title' => ArrayHelper::getValue($this->getData(), 'title', '')
        ]));
    }

    /**
     * @param bool $withKeys
     * @return integer[]|integer
     * @throws \Exception
     */
    public function getProgress($withKeys = false)
    {
        $data = ArrayHelper::getValue($this->getData(), 'progress');
        if (is_array($data)) {
            return ($withKeys) ? array_map('intval', $data) : array_values(array_map('intval', $data));
        }
        return (int)$data;
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getProgressText()
    {
        return ArrayHelper::getValue($this->getData(), 'text', '');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTaskErrors()
    {
        return array_values(ArrayHelper::map(ArrayHelper::getValue($this->getData(), 'error', []), 'attempt', 'message'));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTaskError()
    {
        if (!$this->hasDataError()) {
            return '';
        }

        return implode(',', ArrayHelper::map(ArrayHelper::getValue($this->getData(), 'error'), 'attempt', function ($item) {
            return strtr("[attempt] - message", $item);
        }));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getResult()
    {
        return ArrayHelper::getValue($this->getData(), 'result');
    }

    /**
     * @return int
     */
    public function getExecutionTime()
    {
        $time = $this->pushed_at + ($this->delay ?: 0);

        if (isset($this->attempt)) {
            $time += ($this->ttr ?: 0);
        }

        if ($time < time()) {
            $time = time();
        }

        return $time;
    }


}
