<?php

namespace unit;

use somov\qm\QueueLogModel;
use somov\qm\QueueManagerJobInterface;
use yii\base\BaseObject;
use yii\queue\cli\Queue;

/**
 * @method integer getRestartPushPriority()
 * @method false getRestartDelay($error = null)
 * @method  beforeDelete(Queue $queue, QueueLogModel $model)
 * @method string getJobName()
 */
class Job extends BaseObject implements QueueManagerJobInterface
{

    public $time;

    public $max = 2;


    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $cnt = -1;
        while (true) {
            $cnt++;
            $queue->setProgress($this, $cnt, $this->max);
            if ($cnt >= $this->max) {
                return;
            }
            sleep(1);
        }
    }

}