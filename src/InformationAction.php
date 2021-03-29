<?php

namespace somov\qm;



use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * Class AppQueueAction
 * @package app\components\queue
 */
class InformationAction extends Action
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
     * @param array $t
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function run(array $t = null)
    {
        if (empty($t)) {
            $t = \Yii::$app->request->post('t', []);
        }

        $tasks = array_filter(array_map(function ($item) {
            return (integer)$item;
        }, $t));

        if (count($tasks) > 0) {

            $map = ($this->mapExtend) ? ArrayHelper::merge($this->map, $this->mapExtend) : $this->map;

            $logs = ArrayHelper::toArray(QueueLogModel::find()->where(['[[id]]' => $tasks])->indexBy('id')->all(), [
                QueueLogModel::class => $map
            ]);

            $defaults = array_fill_keys(array_keys($map ), '');

            foreach ($tasks as &$id){
                $id = ArrayHelper::getValue($logs, $id,  array_merge($defaults,[
                    'status' => 99,
                    'id' => $id
                ]));
            }

        }

        return $this->controller->asJson($tasks);
    }
}