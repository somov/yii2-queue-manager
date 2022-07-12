<?php

namespace unit;

use Codeception\TestCase\Test;
use somov\qm\QueueDbLogInterface;
use somov\qm\QueueLogModel;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\queue\sqs\Queue;

class NonExistsTest extends Test
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
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    public function testAddByExternal($queueId)
    {
        /** @var Queue|QueueDbLogInterface $queue */
        $queue = Yii::$app->get($queueId);
        $id = $queue->pushJob(new Job(['time' => time()]));

        //Delete Log
        QueueLogModel::find()->byQueueId($id)->one()->delete();

        $queue->run(false);

        /** @var QueueLogModel $model */
        $model = $queue->getLogListQuery()->byQueueId($id)->one();
        $this->assertSame(QueueDbLogInterface::LOG_STATUS_DONE, $model->status);
        $progress  = $model->getProgress();

        $this->assertSame(100, reset($progress));

    }

}