<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.05.20
 * Time: 17:07
 */

namespace somov\qm\assets;


use yii\web\AssetBundle;

/**
 * Class QueueManagerWidgetAsset
 * @package app\assets\backend
 */
class JQueryEsAssetTest extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@npm/yii2-queue-manager-client/build';


    public $css = [
        'bundle.css'
    ];

    public $js = [
        'jquery.bundle_es.js'
    ];

    public $depends = [
        \yii\web\JqueryAsset::class
    ];


}