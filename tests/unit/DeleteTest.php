<?php

namespace unit;

use somov\qm\QueueDbLogInterface;
use yii\queue\sqs\Queue;

class DeleteTest extends \Codeception\TestCase\Test
{


    /**
     * @return array
     */
    public function getQueueComponents()
    {
        return [
            ['sqs'],
            ['dbq']
        ];
    }


    /**
     * @dataProvider getQueueComponents
     * @param $queueId
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function testAdd($queueId)
    {
        /** @var Queue|QueueDbLogInterface $queue */
        $queue = \Yii::$app->get($queueId);
        $id = $queue->pushJob(new Job(['time' => time()]));

        $model = $queue->getLogListQuery()->byQueueId($id)->one();
        $queue->deleteJob($model);

        $queue->run(false);

        $this->assertFalse($queue->getLogListQuery()->byQueueId($id)->exists());

    }

}