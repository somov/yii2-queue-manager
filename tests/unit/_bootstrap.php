<?php

defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once __DIR__ .  '/../../../app/vendor/yiisoft/yii2/Yii.php';
require __DIR__ .'/../../../app/vendor/autoload.php';

$dir = dirname(__DIR__);

Yii::setAlias('@mtest', $dir  . '/unit/modelfiles');
Yii::setAlias('@ext', $dir );
