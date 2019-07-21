<?php

namespace isamarin\Alisa;

/**
 * Class Alisa
 * @package Alisa
 */
class Alisa
{
    /** @var TriggerIterator $triggers */
    protected $triggers;
    /** @var Trigger $recornizedCommand */
    protected $recognizedCommand;
    /** @var SessionStorage $storage */
    protected $storage;
    /** @var Trigger $defaultCommand */
    protected $defaultCommand;
    /** @var Trigger $helloCommand */
    protected $helloCommand;
    /** @var Trigger $mistakeTrigger */
    protected $mistakeTrigger;

    protected $request;

    protected $skillName;

    protected $recognizedType;


    /**
     * Alisa constructor.
     * @param string $skillName
     * @param null $data
     */
    public function __construct(string $skillName, $data = null)
    {
        $this->request = new Request($data);
        $this->skillName = $skillName;
        $this->triggers = new TriggerIterator();
        $this->storage = new SessionStorage($this->request);
        $this->recognizedType = RecognizedType::MORPHY_STRICT;

    }

    /**
     * @param int $RecognizedType
     * @see RecognizedType
     */
    public function setRecognizedAlgorithm(int $RecognizedType)
    {
        if (in_array($RecognizedType, RecognizedType::getConstants(), true)) {
            $this->recognizedType = $RecognizedType;
        }
    }


    /**
     * Устанавливает триггер по-умолчанию
     * @param Trigger $trigger
     */
    public function setDefaultCommand(Trigger $trigger)
    {
        if ($trigger->isValid()) {
            $this->defaultCommand = $trigger;
        }
    }

    /**
     * @param Trigger $trigger
     */
    public function setHelloCommand(Trigger $trigger)
    {
        if ($trigger->isValid()) {
            $this->helloCommand = $trigger;
        }
    }

    /**
     * @param Trigger $trigger
     */
    public function setMistakeCommand(Trigger $trigger)
    {
        if ($trigger->isValid()) {
            $this->mistakeTrigger = $trigger;
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return bool
     * TODO
     * reduce returns
     */
    public function getCommand(): bool
    {
        /* Первое сообщение – приветствие */
        if ($this->request->getMessageID() === 0) {
            $this->recognizedCommand = $this->helloCommand;
            $this->storage->storeTrigger($this->recognizedCommand);
            return true;
        }
        /* Проверяем клик ли это на кнопку */
        if ($this->request->isButtonClick()) {
            $this->recognizedCommand = $this->triggers
                ->getByName($this->request->getPayloadData()['NAME']);
            return true;
        }

        /* Мб нам несут данные? */
        if ($prev = $this->storage->getPreviousRequest()['NAME']) {
            $_previousCommand = $this->triggers->getByName($prev);
            if ($_previousCommand->isStoreData() && $_previousCommand->hasNextTrigger()) {
                $this->recognizedCommand = $_previousCommand->getNextTrigger();
                $this->storage->setItem($this->request->getUtterance(), $this->recognizedCommand->getName());
                $this->storage->storeTrigger($this->recognizedCommand);
                return true;
            }
            /* мб далее дефолт? */
            if ($_previousCommand->hasNextTrigger()
                && $_previousCommand->getNextTrigger() === $this->defaultCommand) {
                $this->recognizedCommand = $this->defaultCommand;
                $this->storage->storeTrigger($this->recognizedCommand);
                return true;
            }
        }

        if ($command = $this->recognizeCommand()) {
            $this->recognizedCommand = $command;
            $this->storage->storeTrigger($this->recognizedCommand);
            return true;
        }


        /* Не удалось распознать */
        if ( ! $this->recognizedCommand) {
            $this->recognizedCommand = $this->mistakeTrigger;
            $this->storage->storeTrigger($this->recognizedCommand);
            return true;
        }
        return false;

    }

    protected function recognizeCommand(): bool
    {
        /**
         * @var DistanceRecognition|MorphyRecognition $recognizer
         */
        $recognizer = null;
        if ($this->recognizedType === RecognizedType::MORPHY_STRICT) {
            $recognizer = new MorphyRecognition();
        } else {
            $recognizer = new DistanceRecognition();
        }
        $results = [];
        /**
         * @var Trigger $trigger
         */
        foreach ($this->triggers as $trigger) {
            $results[$trigger->getName()] = $recognizer->rateSimilarities($this->request, $trigger);
            if ($this->recognizedType === RecognizedType::MORPHY_STRICT && $results[$trigger->getName()] === 1) {
                $this->recognizedCommand = $trigger;
                return true;
            }
        }
        if ($this->recognizedType === RecognizedType::DAMERAU_LEVENSHTEIN_DISTANCE) {
            sort($results);
            $this->recognizedCommand = $this->triggers->getByName(key($results));
            return true;
        }

        return false;
    }


    /**
     * @param Trigger $command
     * @return mixed
     */
    public function getStoredCommandData(Trigger $command)
    {
        return $this->storage->getItem($command->getName());
    }

    /**
     * @return Trigger
     */
    public function getRecognizedCommand(): Trigger
    {
        return $this->recognizedCommand;
    }

    /**
     * @param Trigger ...$trigger
     */
    public function addCommand(Trigger ... $trigger)
    {
        foreach ($trigger as $tr) {
            if ($tr->isValid()) {
                if ($this->defaultCommand && ! $tr->hasNextTrigger()) {
                    $tr->setNext($this->defaultCommand);
                }
                $this->triggers->append($tr);
            }
        }
    }

    public function sendResponse(Trigger $command, callable $func)
    {
        if ($command === $this->recognizedCommand) {
            /** @var Response $answer */
            $answer = $func();
            $response = $this->request->getServiceData();
            $response['response'] = $answer->send();
            print json_encode($response);
            $this->storage->clear();
        }
    }

    public function storeCommonData($data, $key)
    {
        $this->storage->setItem($key, $data);

    }

    public function getCommonData($key)
    {
        return $this->storage->getItem($key);
    }
}