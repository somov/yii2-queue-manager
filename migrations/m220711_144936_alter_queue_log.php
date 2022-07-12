<?php

use yii\db\Migration;

require_once 'm200317_144936_crate_queue_log.php';

/**
 * Class m200317_144936_crate_queue_log
 */
class m220711_144936_alter_queue_log extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = m200317_144936_crate_queue_log::TABLE_NAME;
        $this->addColumn($table, 'queue_id', $this->string(200)->after('id'));
        $this->createIndex('uq_queue_log_queue_id', $table, ['queue_id'], true);

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m110722_144936_alter_queue_log cannot be reverted.\n";

        return false;
    }

}
