<?php


return [
    'class' => \yii\console\Application::class,
    'id' => 'test_manager',
    'enableCoreCommands' => YII_ENV_DEV | YII_ENV_TEST,
    'bootstrap' => ['log', 'sqs', 'dbq'],
    'controllerNamespace' => 'unit\commands',
    'basePath' => dirname(__DIR__, 2),
    'aliases' => [
        'unit' => '@app/tests/unit'
    ],
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'username' => 'user',
            'password' => 'pass',
            'dsn' => 'mysql:host=127.0.0.1;dbname=db'
        ],

        'dbq' => [
            'class' => \yii\queue\db\Queue::class,
            'mutex' => \yii\mutex\MysqlMutex::class,
            'as log' => [
                'class' => \somov\qm\QueueDbLogBehavior::class
            ]
        ],

        'sqs' => [
            'class' => \yii\queue\sqs\Queue::class,
            'url' => 'https://sqs.eu-central-1.amazonaws.com/000000000000/test.fifo',
            'key' => 'key',
            'secret' => 'secret',
            'region' => 'eu-central-1',
            'as log' => [
                'class' => \somov\qm\QueueDbLogBehavior::class
            ]
        ]
    ]
];
