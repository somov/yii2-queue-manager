<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.10.21
 * Time: 15:26
 */

namespace somov\qm\assets;

/**
 * Trait ColorTrait
 * @package somov\qm\assets
 */
trait ColorTrait
{

    /**
     * @var string
     */
    public $colors = '';


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