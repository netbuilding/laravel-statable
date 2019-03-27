<?php
/**
 * Created by PhpStorm.
 * User: isaacliu
 * Date: 2019-03-25
 * Time: 19:47
 */

namespace Iben\Statable\Events;


class StateChangedEvent
{
    private $model;
    private $from;
    private $to;
    private $transition;

    /**
     * StateChangedEvent constructor.
     * @param $model
     * @param $from
     * @param $to
     * @param $transition
     */
    public function __construct($model, $from, $to, $transition = null)
    {
        $this->model = $model;
        $this->from = $from;
        $this->to = $to;
        $this->transition = $transition;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getTransition()
    {
        return $this->transition;
    }


}
