<?php

namespace unit;

use somov\qm\QueueDbLogInterface;
use somov\qm\QueueLogModel;
use yii\queue\sqs\Queue;

class AddTest extends \Codeception\TestCase\Test
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

        $queue->run(false);

        /** @var QueueLogModel $model */
        $model = $queue->getLogListQuery()->byQueueId($id)->one();
        $this->assertSame(QueueDbLogInterface::LOG_STATUS_DONE, $model->status);
        $progress  = $model->getProgress();

        $this->assertSame(100, reset($progress));

    }

}