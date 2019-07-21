<?php

namespace isamarin\Alisa;

use Iterator;

class TriggerIterator implements Iterator
{
    protected $position;
    protected $array;

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

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

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