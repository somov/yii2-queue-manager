<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 28.05.21
 * Time: 13:55
 */

namespace somov\qm;


use yii\helpers\ArrayHelper;

/**
 * Trait InformationConverterTrait
 * @package somov\qm
 */
trait InformationConverterTrait
{

    /**
     * @var array
     */
    protected $map = [
        'id' => 'id',
        'title' => 'title',
        'text' => 'progressText',
        'progress' => 'progressPercent',
        'statusCaption' => 'statusCaption',
        'status' => 'status',
        'errors' => 'taskErrors'
    ];

    /**
     * @var
     */
    public $mapExtend;

    /**
     * @param QueueLogModel[] $logs
     * @param array $ids
     * @return array
     * @throws \Exception
     */
    public function convertInformation(array $logs, $ids = [])
    {

        if (empty($ids)) {
            $ids = ArrayHelper::getColumn($logs, ArrayHelper::getValue($this->map, 'id'));
        }

        $map = ($this->mapExtend) ? ArrayHelper::merge($this->map, $this->mapExtend) : $this->map;

        $logs = ArrayHelper::toArray($logs, [QueueLogModel::class => $map]);

        $defaults = array_fill_keys(array_keys($map), '');

        foreach ($ids as &$id){
            $id = ArrayHelper::getValue($logs, $id,  array_merge($defaults,[
                'status' => QueueDbLogInterface::LOG_STATUS_DELETE,
                'id' =>  $id
            ]));
        }

        return array_values($logs);
    }

}