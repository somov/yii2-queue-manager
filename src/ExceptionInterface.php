<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 16.04.20
 * Time: 15:48
 */

namespace somov\qm;

/**
 * Interface AppQueueExceptionInterface
 * @package app\components\queue
 */
interface ExceptionInterface
{
    /**
     * @return array
     */
    public function resolveErrorFields();
}