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
class JQueryEsAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@npm/yii2-queue-manager-client/dist';

    /**
     * @var string
     */
    public $colors = '';

    public $css = [
        'main' => 'css/queueMc-c.min.css'
    ];

    public $js = [
        'js/jquery.queueMc_es.min.js'
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


    /**
     * Apply color settings
     */
    protected function applyColors()
    {
        $replace = '';
        if (empty($this->colors) === false) {
            $replace = '-colors-' . $this->colors;
        }

        $this->css['main'] = str_replace('-c', $replace, $this->css['main']);
    }


}