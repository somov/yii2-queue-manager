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
    use InformationConverterTrait;


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

        $tasks = $t;

        if (count($tasks) > 0) {
            return $this->controller->asJson(
                $this->convertInformation(QueueLogModel::find()->byId($tasks)->all(), $tasks)
            );
        }

        return $this->controller->asJson([]);
    }


}