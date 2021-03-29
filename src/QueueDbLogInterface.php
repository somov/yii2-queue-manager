<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 22.04.19
 * Time: 14:54
 */

namespace somov\qm;



use yii\db\Query;
use yii\queue\JobInterface;

/**
 * Interface QueueDbLogInterface
 * @package app\components\interfaces
 *
 *
 */
interface QueueDbLogInterface
{
    const LOG_STATUS_WAIT = 0;
    const LOG_STATUS_EXEC = 1;
    const LOG_STATUS_DONE = 2;
    const LOG_STATUS_ERROR = 3;

    /**
     * @param int|int[] $status
     * @param JobInterface|string $type
     * @param \Closure|null$queryCallback
     * @return QueueLogModel[]|Query
     */
    public function getLogsList($status = QueueDbLogInterface::LOG_STATUS_WAIT, $type = null, $queryCallback = null);


    /**
     * @param JobInterface $job
     * @param integer $done
     * @param integer $total
     * @param string $text
     * @param integer|null $percent
     * @param array|null $data
     */
    public function setProgress($job, $done, $total, $text = null, $percent = null, $data = null);

    /**
     * @param JobInterface|string $job job instance or class name
     * @return string name my class
     */
    public function getTaskName($job);


    /**
     * Удаление задания
     * @param QueueLogModel $model
     * @return bool
     */
    public function deleteJob(QueueLogModel $model);

    



}