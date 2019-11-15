<?php

namespace isamarin\Alisa;

use Iterator;

/**
 * Class TriggerIterator
 * @package isamarin\Alisa
 */
class TriggerIterator implements Iterator
{
    protected $position;
    protected $array;

    /**
     * @param Trigger $command
     */
    public function append(Trigger $command): void
    {
        $this->array[] = $command;
    }

    public function __construct()
    {
        $this->position = 0;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->array[$this->position];
    }

    /**
     * @return int|mixed
     */
    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

    /**
     * @param $name
     * @return bool|Trigger
     */
    public function getByName($name)
    {
        foreach ($this->array as $trigger) {
            /** @var Trigger $trigger */
            if ($trigger->getName() === strtoupper($name)) {
                return $trigger;
            }
        }
        return false;
    }

    /**
     * @return bool|Trigger
     */
    public function getDefaultTrigger()
    {
        foreach ($this->array as $trigger) {
            /** @var Trigger $trigger */
            if ($trigger->isDefault()) {
                return $trigger;
            }
        }
        return false;
    }

    /**
     * @return bool|Trigger
     */
    public function getInitTrigger()
    {
        foreach ($this->array as $trigger) {
            /** @var Trigger $trigger */
            if ($trigger->isInit()) {
                return $trigger;
            }
        }
        return false;

    }

    /**
     * @return bool|Trigger
     */
    public function getMistakeTrigger()
    {
        foreach ($this->array as $trigger) {
            /** @var Trigger $trigger */
            if ($trigger->isMistake()) {
                return $trigger;
            }
        }
        return false;
    }

}