<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 31.03.21
 * Time: 11:40
 */

namespace somov\qm;


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
     * @param $type
     * @return QueueLogModelQuery
     */
    public function byType($type)
    {
        return $this->andWhere(['[[type]]' => $this->behavior->getTaskType($type)]);
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