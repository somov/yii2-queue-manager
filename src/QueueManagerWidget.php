<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.05.20
 * Time: 17:04
 */

namespace somov\qm;

use somov\common\traits\ScriptWidgetRegisterTrait;
use somov\qm\assets\JQueryEsAsset;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class QueueManagerWidget
 * @package app\widgets\base
 */
class QueueManagerWidget extends Widget
{
    use ScriptWidgetRegisterTrait;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    public $wrapperSelector = 'qmc-queue-manager';

    /**
     * @var bool
     */
    public $renderPluginScript = false;

    /**
     * @var array
     */
    public $route;
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $assetsClass = JQueryEsAsset::class;

    /**
     * @var array
     */
    public $defaultClientOptions = [];


    /**
     * @inheritdoc
     */
    public function init()
    {

        if (empty($this->url)) {
            if (isset($this->route)) {
                $this->url = Url::toRoute($this->route);
            } else {
                throw new InvalidConfigException('Url or route required');
            }
        }

        $request = \Yii::$app->request;

        if (\Yii::$app->controller->enableCsrfValidation) {
            ArrayHelper::setValue($this->clientOptions, 'resolver', [
                'url' => $this->url,
                'params' => [
                    $request->csrfParam => $request->getCsrfToken()
                ]
            ]);
        }

        $this->clientOptions = ArrayHelper::merge($this->defaultClientOptions, $this->clientOptions);

    }

    /**
     * @return string
     */
    public function run()
    {
        if ($this->renderPluginScript) {
            $this->scriptRegister($this->assetsClass, false, 'queueManager');
        } else {
            $this->registerScriptPluginViaDataAttr('manager', $this->assetsClass, $this->wrapperSelector);
        }

        echo Html::tag('div', false, $this->options);
    }


}