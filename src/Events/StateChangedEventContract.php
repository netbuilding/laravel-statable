<?php
/**
 * Created by PhpStorm.
 * User: isaacliu
 * Date: 2019-03-25
 * Time: 19:47
 */

namespace Iben\Statable\Events;


interface StateChangedEventContract
{
    /**
     * StateChangedEvent constructor.
     * @param $model
     * @param $from
     * @param $to
     * @param $transition
     */
    public function __construct($model, $from, $to, $transition = null);

    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @return mixed
     */
    public function getFrom();

    /**
     * @return mixed
     */
    public function getTo();

    /**
     * @return mixed
     */
    public function getTransition();


}
