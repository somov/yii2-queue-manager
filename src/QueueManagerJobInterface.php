<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 02.11.19
 * Time: 15:51
 */

namespace somov\qm;

use yii\queue\cli\Queue;
use yii\queue\JobInterface;

/**
 * Interface AppQueueJobInterface
 * @package app\components\queue
 *
 * @method integer getRestartPushPriority();
 *@method integer|false getRestartDelay($error = null);
 *@method beforeDelete(Queue $queue, QueueLogModel $model )
 *@method string getJobName()
 */
interface QueueManagerJobInterface extends JobInterface
{
    /**
     * @param \yii\queue\Queue|QueueDbLogInterface $queue
     * @return mixed|void
     */
    public function execute($queue);
}