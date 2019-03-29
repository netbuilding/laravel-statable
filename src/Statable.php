<?php

namespace Iben\Statable;

use Iben\Statable\Events\StateChangedEvent;
use Iben\Statable\Events\StateChangedEventContract;
use Illuminate\Support\Str;
use SM\StateMachine\StateMachine;
use Iben\Statable\Models\StateHistory;

trait Statable
{
    /**
     * @var StateMachine
     */
    protected $SM;

    protected $lastTransition;

    public static function bootStatable()
    {
        static::saved(function ($model) {
            $property = $model->getStateProperty();
            if ($model->wasChanged($property)) {
                $from = $model->getOriginal($property);
                $to = $model->getAttribute($property);

                $model->stateHistory()->create([
                    'actor_id' => $model->getActorId(),
                    'transition' => $model->lastTransition,
                    'from' => $from,
                    'to' => $to,
                ]);

                $model->fireStateChangedEvent($from, $to, $model->lastTransition);
            }
        });
    }

    /**
     * @param array $transitionData
     */
    public function addHistoryLine(array $transitionData)
    {
        if ($this->getKey()) {
            $transitionData['actor_id'] = $this->getActorId();
            $this->stateHistory()->create($transitionData);
        }
    }

    /**
     * @return int|null
     */
    public function getActorId()
    {
        return auth()->id();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function stateHistory()
    {
        return $this->morphMany(StateHistory::class, 'statable');
    }

    public function currentStateIs($state): bool
    {
        return $this->stateIs() === $state;
    }

    /**
     * @return mixed|string
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function stateIs()
    {
        return $this->stateMachine()->getState();
    }

    /**
     * @return mixed|\SM\StateMachine\StateMachine
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function stateMachine()
    {
        if (!$this->SM) {
            $this->SM = app('sm.factory')->get($this, $this->getGraph());
        }

        return $this->SM;
    }

    /**
     * @return string
     */
    protected function getGraph()
    {
        return Str::camel(class_basename(new static));
    }

    /**
     * @param $transition
     * @return bool
     * @throws \SM\SMException|\Illuminate\Container\EntryNotFoundException
     */
    public function apply($transition)
    {
        if ($this->getKey() === null && $this->saveBeforeTransition()) {
            $this->save();
        }

        $apply = $this->stateMachine()->apply($transition);
        $this->lastTransition = $transition;
        return $apply;
    }

    /**
     * @return bool
     */
    protected function saveBeforeTransition()
    {
        return false;
    }

    public function canApplyList()
    {
        $transitions = array_keys(array_get($this->getConfig(), 'transitions', []));
        return collect($transitions)->mapWithKeys(function ($item, $key) {
            return [
                $item => $this->canApply($item)
            ];
        });
    }

    public function getConfig()
    {
        return config("state-machine.{$this->getGraph()}");
    }

    /**
     * @param $transition
     * @return bool
     * @throws \SM\SMException|\Illuminate\Container\EntryNotFoundException
     */
    public function canApply($transition)
    {
        return $this->stateMachine()->can($transition);
    }

    public function scopeOfState($query, $state)
    {
        return $query->where($this->getStateProperty(), $state);
    }

    public function getStateProperty()
    {
        return array_get($this->getConfig(), 'property_path', 'state');
    }

    public function scopeWithoutState($query, $state)
    {
        return $query->where($this->getStateProperty(), '<>', $state);
    }

    public function fireStateChangedEvent($from, $to, $transition = null)
    {
        $stateChangedEvent = $this->getStateChangedEvent();

        if (
            $stateChangedEvent &&
            class_exists($stateChangedEvent) &&
            \is_subclass_of($stateChangedEvent, StateChangedEventContract::class)
        ) {
            event(new $stateChangedEvent($this, $from, $to, $transition));
        }

        event(new StateChangedEvent($this, $from, $to, $transition));
    }

    public function getStateChangedEvent(): string
    {
        return '';
    }
}
