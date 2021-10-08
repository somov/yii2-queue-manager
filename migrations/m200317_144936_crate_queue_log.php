<?php

use yii\db\Migration;

/**
 * Class m200317_144936_crate_queue_log
 */
class m200317_144936_crate_queue_log extends Migration
{
    const TABLE_NAME = '{{%queue_log}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'channel' => $this->string()->notNull(),
            'type' => $this->bigInteger()->notNull(),
            'name' => $this->string(255),
            'job' => $this->binary()->notNull(),
            'data' => $this->text(),
            'pushed_at' => $this->integer()->notNull(),
            'ttr' => $this->integer()->notNull(),
            'delay' => $this->integer()->notNull(),
            'priority' => $this->integer()->unsigned()->notNull()->defaultValue(1024),
            'reserved_at' => $this->integer(),
            'processed_at' => $this->timestamp(),
            'attempt' => $this->integer(),
            'done_at' => $this->integer(),
            'status' => $this->integer(),
            'pid' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('i_queue_log_channel', self::TABLE_NAME, 'channel');
        $this->createIndex('i_queue_log_status', self::TABLE_NAME, 'status');
        $this->createIndex('i_queue_log_pushed_at', self::TABLE_NAME, 'pushed_at');
        $this->createIndex('i_queue_log_type', self::TABLE_NAME, 'type');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200317_144936_crate_queue_log cannot be reverted.\n";

        return false;
    }

}
