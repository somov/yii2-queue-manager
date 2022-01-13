<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 31.03.21
 * Time: 11:40
 */

namespace somov\qm;


use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * Class QueueLogModelQuery
 * @package somov\qm
 * @see QueueLogModel
 */
class QueueLogModelQuery extends ActiveQuery
{
    /**
     * @var QueueDbLogBehavior
     */
    public $behavior;

    /**
     * @return QueueLogModelQuery
     */
    public function asArrayIds()
    {
        return $this->select('[[id]]')->asArray(true);
    }

    /**
     * @param null $db
     * @return bool
     */
    public function exists($db = null)
    {
        $this->asArrayIds();
        return parent::exists($db);
    }

    /**
     * @param string|string[]|QueueManagerJobInterface|QueueManagerJobInterface[] $type
     * @return QueueLogModelQuery
     * @throws  InvalidConfigException
     */
    public function byType($type)
    {
        if (is_string($type) || $type instanceof QueueManagerJobInterface) {
            return $this->andWhere(['[[type]]' => $this->behavior->getTaskType($type)]);
        } else if (is_array($type)) {
            return $this->andWhere(['or',
                [
                    '[[type]]' => array_map(function ($t) {
                        return $this->behavior->getTaskType($t);
                    }, $type)
                ]
            ]);
        }
        throw  new InvalidConfigException('Unelected param type');
    }

    /**
     * @param int|int[] $status
     * @return QueueLogModelQuery
     */
    public function byStatus($status)
    {
        return $this->andWhere(['[[status]]' => $status]);
    }

    /**
     * @param string $channel
     * @return QueueLogModelQuery
     */
    public function byChannel($channel)
    {
        return $this->andWhere(['[[channel]]' => $channel]);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return QueueLogModelQuery
     */
    public function byProperty($name, $value)
    {
        return $this->andWhere(['rlike', '[[job]]', "$name.*" . $value]);
    }


    /**
     * @param int|int[] $tasks
     * @return QueueLogModelQuery
     */
    public function byId(&$tasks)
    {
        if (is_array($tasks) === false) {
            $tasks = [$tasks];
        }

        $tasks = array_filter(array_map(function ($item) {
            return (integer)$item;
        }, $tasks));

        return $this->where(['[[id]]' => $tasks])->indexBy('id');
    }

}