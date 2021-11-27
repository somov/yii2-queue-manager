<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 19.04.19
 * Time: 13:32
 */

namespace somov\qm;

use yii\base\Behavior;
use yii\base\Exception;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\StringHelper;
use yii\queue\ExecEvent;
use yii\queue\JobInterface;
use yii\queue\PushEvent;
use yii\queue\Queue;

/**
 * Class QueueDbLogBehavior
 * @package somov\qm
 */
class QueueDbLogBehavior extends Behavior implements QueueDbLogInterface
{

    /** @var \yii\queue\db\Queue */
    public $owner;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Queue::EVENT_AFTER_PUSH => 'afterPush',
            Queue::EVENT_BEFORE_EXEC => 'beforeExec',
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
            Queue::EVENT_BEFORE_PUSH => 'beforePush'
        ];
    }


    /**
     * @param array|QueueManagerJobInterface $job
     * @param QueueLogModelQuery $existQuery
     * @param array|null $config ttr, delay,priority
     * @return int
     */
    public function pushJob($job, $existQuery = null, $config = null)
    {
        if (is_array($job)) {
            $job = \Yii::createObject($job);
        }

        if ($existQuery instanceof QueueLogModelQuery) {
            if ($existQuery->byType($job)->exists()) {
                return -1;
            }
        }
        if (isset($config) && is_array($config)) {
            $handler = null;
            $handler = function (PushEvent $event) use (&$handler, $config) {
                \Yii::configure($event, $config);
                $this->owner->off(Queue::EVENT_BEFORE_PUSH, $handler);
            };

            $this->owner->on(Queue::EVENT_BEFORE_PUSH, $handler);
        }
        return $this->owner->push($job);
    }

    /**
     * @param JobInterface $job
     * @param integer $done
     * @param integer $total
     * @param string $text
     * @param integer|null $percent
     * @throws \Exception
     */
    public function setProgress($job, $done, $total, $text = '', $percent = null)
    {
        if (!$model = $this->getLog($job)) {
            return;
        }

        $raw = [
            'done' => $done,
            'total' => $total,
        ];

        if ($total > 0 || isset($percent)) {
            $doneMax = isset($data) ? (int)ArrayHelper::getValue($data, 'max', 100) : 100;
            $raw = [
                'percent' => isset($percent) ? $percent : (integer)round(((integer)$done * $doneMax / (integer)$total), 0),
            ];
        }

        if (empty($text) === false) {
            $this->setProgressText($job, $text);
        }

        $model->setData(['progress' => new ReplaceArrayValue($raw)])->save();

    }

    /**
     *
     * @param JobInterface $job
     * @param array $data
     * @param string $text
     * @param integer|null $streamId
     */
    public function setProgressStream($job, array $data, $text = '', $streamId = null)
    {

        if (!$model = $this->getLog($job)) {
            return;
        }

        $streamPercent = function ($data) {
            $percent = ArrayHelper::getValue($data, 'percent', false);
            if ($percent) {
                return $percent;
            }
            $max = ArrayHelper::getValue($data, 'maz', 100);
            $total = ArrayHelper::getValue($data, 'total', 0);
            $done = ArrayHelper::getValue($data, 'done', 0);
            return $done > 0 ? (integer)round(((integer)$done * $max / (integer)$total), 0) : 0;
        };

        if (empty($streamId)) {
            $raw = [];
            foreach ($data as $id => $stream) {
                $raw[$id] = (false === is_array($data)) ? $streamPercent($stream) : (int)$stream;
            }
        } else {
            $raw = $model->getProgress(true);
            ArrayHelper::setValue($raw, $streamId, $streamPercent($data));
        }

        if (empty($text) === false) {
            $this->setProgressText($job, $text);
        }

        $model->setData(['progress' => new ReplaceArrayValue($raw)])->save(false);
    }

    /**
     * @param JobInterface $job
     * @param string $text
     * @param bool $save
     */
    public function setProgressText($job, $text, $save = false)
    {
        if (!$model = $this->getLog($job)) {
            return;
        }
        $model->setData(['text' => new ReplaceArrayValue($text)]);

        if ($save) {
            $model->save(false);
        }

    }

    /**
     * @return QueueLogModelQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getLogListQuery()
    {
        $query = QueueLogModel::find()->byChannel($this->owner->channel);
        $query->behavior = $this;
        return $query;
    }

    /**
     * @param int|int[]|null $status
     * @param JobInterface|string $type
     * @param \Closure|null $queryCallback
     * @return QueueLogModel[]|QueueLogModelQuery
     */
    public function getLogsList($status = QueueDbLogInterface::LOG_STATUS_WAIT, $type = null, $queryCallback = null)
    {
        $query = $this->getLogListQuery();

        if (isset($status)) {
            $query->byStatus($status);
        }
        if (isset($type)) {
            $query->byType($this->getTaskType($type));
        }

        if (isset($queryCallback)) {
            return $queryCallback($query);
        }

        return $query->all();
    }


    /**
     * @throws \yii\db\Exception
     */
    public function beforePush()
    {
        $this->normalizeAutoIncrement();
    }


    /**
     * @param PushEvent $event
     * @throws \yii\base\InvalidConfigException
     */
    public function afterPush(PushEvent $event)
    {
        $this->createQueueLog($event);
    }

    /**
     * @param ExecEvent $event
     */
    public function beforeExec(ExecEvent $event)
    {
        if (!$log = $this->getLog($event->job, $event->id)) {
            return;
        }
        $log->reserved_at = time();
        $log->attempt = $event->attempt;
        $log->status = QueueDbLogInterface::LOG_STATUS_EXEC;
        $log->pid = $event->sender->getWorkerPid();
        $log->save();
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        if (!$log = $this->getLog($event->job, $event->id)) {
            return;
        }

        $log->done_at = time();
        $log->status = QueueDbLogInterface::LOG_STATUS_DONE;
        $log->setData([
            'result' => $event->result
        ])->save();

        $this->restart($event->job, $event->sender);

    }

    /** Restart specific job
     * @param JobInterface|QueueManagerJobInterface $job
     * @param Queue $queue
     * @param null|Exception $error
     */
    protected function restart(JobInterface $job, Queue $queue, $error = null)
    {
        if ($job instanceof QueueManagerJobInterface) {

            if (method_exists($job, 'getRestartDelay')) {
                if (!$delay = $job->getRestartDelay($error)) {
                    return;
                }
                $queue->delay($delay);
            } else {
                return;
            }

            if (method_exists($job, 'getRestartPushPriority')) {
                $queue->priority($job->getRestartPushPriority());
            }

            $queue->push($job);
        }
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        if (!$log = $this->getLog($event->job, $event->id)) {
            return;
        }
        $log->status = $event->retry ? QueueDbLogInterface::LOG_STATUS_WAIT : QueueDbLogInterface::LOG_STATUS_ERROR;

        $fields = [
            'message' => $event->error->getMessage(),
            'type' => ($event->error instanceof Exception) ? $event->error->getName() : StringHelper::basename(get_class($event->error)),
            'attempt' => $event->attempt
        ];

        if ($event->error instanceof ExceptionInterface) {
            $fields = ArrayHelper::merge($fields, $event->error->resolveErrorFields());
        }

        $log->setData([
            'error' => [
                $event->attempt => $fields
            ]
        ])->save();

        if (isset($event->job)) {
            $this->restart($event->job, $event->sender, $event->error);
        }
    }


    /**
     * @var QueueLogModel[]
     */
    private static $_logList = [];

    /**
     * @param JobInterface $job
     * @param QueueLogModel $log
     */
    protected function addLogToList($job, $log)
    {
        self::$_logList[] = [
            'log' => $log,
            'job' => $job
        ];
    }

    /**
     * @param PushEvent $event
     * @return QueueLogModel
     * @throws \yii\base\InvalidConfigException
     */
    protected function createQueueLog(PushEvent $event)
    {
        $model = new QueueLogModel([
            'id' => $event->id,
            'name' => $this->getTaskName($event->job),
            'type' => $this->getTaskType($event->job),
            'ttr' => $event->ttr,
            'delay' => $event->delay,
            'priority' => $event->priority ?: 1024,
            'status' => QueueDbLogInterface::LOG_STATUS_WAIT,
            'job' => $event->sender->serializer->serialize($event->job),
            'channel' => $event->sender->channel,
            'pushed_at' => time(),
        ]);

        $model->save();

        $this->addLogToList($event->job, $model);

        return $model;

    }

    /**
     * @param QueueLogModel $model
     * @return bool|false|int
     * @throws UserException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function deleteJob(QueueLogModel $model)
    {

        if ($model->status === self::LOG_STATUS_EXEC) {
            throw  new UserException('Running tasks cannot be deleted');
        }

        if (in_array($model->status, [self::LOG_STATUS_DONE, self::LOG_STATUS_ERROR])) {
            return $model->delete();
        }

        if (!$message = (new Query())->from('{{%queue}}')
            ->select('job')->where(['[[id]]' => $model->id])->limit(1)->one()) {
            return $model->delete();
        }

        list($job) = $this->owner->unserializeMessage($message['job']);

        if ($job instanceof QueueManagerJobInterface && method_exists($job, 'beforeDelete')) {
            if (!$job->beforeDelete($this->owner, $model)) {
                return false;
            }
        }

        if (\Yii::$app->db->createCommand()->delete('{{%queue}}', ['[[id]] ' => $model->id])->execute() > 0) {
            return $model->delete();
        }

        return false;
    }


    /**
     * Name of job task
     * @param string|JobInterface $job
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getTaskName($job)
    {
        if (is_string($job)) {
            $job = \Yii::createObject($job);
        }

        if ($job instanceof QueueManagerJobInterface && method_exists($job, 'getJobName')) {
            return $job->getJobName();
        }

        return Inflector::camel2words((StringHelper::basename(get_class($job))));
    }

    /**
     * @param string[]|string|QueueManagerJobInterface $type
     * @return int
     */
    public function getTaskType($type)
    {
        if (is_object($type)) {
            $type = get_class($type);
        } elseif (is_array($type)) {
            $type = implode('_', $type);
        }

        return crc32($type);
    }

    /**
     * @param JobInterface $job
     * @param integer $id
     * @return QueueLogModel|false
     */
    private function getLog($job, $id = null)
    {
        foreach (self::$_logList as $item) {
            if ($item['job'] === $job) {
                return $item['log'];
            }
        }

        if (isset($id)) {
            if ($model = QueueLogModel::findOne($id)) {
                $this->addLogToList($job, $model);
                return $model;
            }
        }
        return false;
    }

    /**
     * При перезапуске сервиса бд сбрасывается автоинкремент
     * возвращаем значение из таблицы лога
     * @throws \yii\db\Exception
     */
    private function normalizeAutoIncrement()
    {
        $db = $this->owner->db;

        $raw = $db->createCommand("SHOW TABLE STATUS LIKE '" .
            $db->getTableSchema(QueueLogModel::tableName())->fullName . "'")->queryOne(\PDO::FETCH_ASSOC);
        $db->createCommand()->setSql("ALTER TABLE " . $this->owner->tableName .
            "  AUTO_INCREMENT = " . $raw['Auto_increment'])->execute();
    }


}