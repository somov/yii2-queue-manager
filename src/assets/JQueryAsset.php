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
class JQueryAsset extends AssetBundle
{
    use ColorTrait;

    /**
     * @var string
     */
    public $sourcePath = '@npm/yii2-queue-manager-client/dist';


    public $css = [
        'main' => 'css/queueMc-c.min.css'
    ];

    public $js = [
        'js/jquery.queueMc.min.js'
    ];

    public $depends = [
        \yii\web\JqueryAsset::class
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->applyColors();
    }



}