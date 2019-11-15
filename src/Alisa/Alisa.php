<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use ReflectionException;
use function in_array;

/**
 * Class Alisa
 * @package Alisa
 */
class Alisa
{
    /** @var TriggerIterator $triggers */
    protected $triggers = [];
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

    protected $substuted = false;

    protected $request;

    protected $skillName;

    protected $directionType;

    protected $alghoritm;


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
        $this->directionType = DirectionType::BACKWARD;
        $this->alghoritm = new MorphyRecognition();
    }


    /**
     * @param RecognitionInterface $algorithm
     */
    public function setAlgorithm(RecognitionInterface $algorithm): void
    {
        $this->alghoritm = $algorithm;
    }


    /**
     * Устанавливает триггер по-умолчанию
     * @param Trigger $trigger
     * @see Trigger::setAsDefault()
     * @deprecated
     */
    public function setDefaultTrigger(Trigger $trigger): void
    {
        if ($trigger->isValid()) {
            $this->defaultCommand = $trigger;
        } else {
            trigger_error('Некорректный триггер');
        }
    }

    /**
     * @param Trigger $trigger
     * @see Trigger::setAsInit()
     * @deprecated
     */
    public function setHelloTrigger(Trigger $trigger): void
    {
        if ($trigger->isValid()) {
            $this->helloCommand = $trigger;
        } else {
            trigger_error('Некорректный триггер');
        }
    }

    /**
     * @param Trigger $trigger
     * @see Trigger::setAsMistake()
     * @deprecated
     */
    public function setMistakeTrigger(Trigger $trigger): void
    {
        if ($trigger->isValid()) {
            $this->mistakeTrigger = $trigger;
        } else {
            trigger_error('Некорректный триггер');
        }
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return bool
     * TODO
     * reduce returns
     */
    protected function getCommand(): bool
    {
        if ($this->request->isSubstitued()) {
            $this->recognizedCommand = $this->triggers->getByName($this->request->isSubstitued()['to']);
            return true;
        }
        /* Первое сообщение – приветствие */
        if ($this->request->getMessageID() === 0) {
            $this->recognizedCommand = $this->helloCommand;
            $this->storage->storeTrigger($this->recognizedCommand->getName(), $this->request->getUtterance());
            return true;
        }
        /* Проверяем клик ли это на кнопку */
        if ($this->request->isButtonClick()) {
            $this->recognizedCommand = $this->triggers
                ->getByName($this->request->getPayloadData()['NAME']);

            $this->storage->storeTrigger($this->recognizedCommand->getName(), $this->request->getUtterance());
            return true;
        }

        $previousTriggerName = $this->storage->getPreviousTrigger();
        if ($previousTriggerName) {
            $_previousCommand = $this->triggers->getByName($previousTriggerName);
            if ($_previousCommand->hasNextTrigger()) {
                $this->recognizedCommand = $_previousCommand->getNextTrigger();
                $this->storage->setItem($this->request->getUtterance(), $this->recognizedCommand->getName());
                $this->storage->storeTrigger($this->recognizedCommand->getName(), $this->request->getUtterance());
                return true;
            }
        }

        $this->recognizeCommand();
        $this->storage->storeTrigger($this->recognizedCommand->getName(), $this->request->getUtterance());
        return true;
    }


    protected function recognizeCommand(): void
    {
        $this->recognizedCommand = $this->alghoritm->rateSimilarities($this->request, $this->triggers);
    }


    /**
     * @param string $triggerName
     * @return mixed
     */
    public function getTriggerData(string $triggerName)
    {
        return $this->storage->getItem($triggerName);
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
     * TODO
     * исправить работу итератора при пропуске дефолтных триггеров
     */
    public function addTrigger(Trigger ... $trigger): void
    {
        foreach ($trigger as $tr) {
            if ($tr->isValid()) {
                if ($tr->isMistake()) {
                    $this->mistakeTrigger = $tr;
                    $this->triggers->append($tr);
                    continue;
                }
                if ($tr->isDefault()) {
                    $this->defaultCommand = $tr;
                    $this->triggers->append($tr);
                    continue;
                }
                if ($tr->isInit()) {
                    $this->helloCommand = $tr;
                    $this->triggers->append($tr);
                    continue;
                }
                $this->triggers->append($tr);
            }
        }

        $this->init();
    }

    protected function init(): void
    {
        if ( ! isset($this->helloCommand, $this->defaultCommand, $this->mistakeTrigger)) {
            $this->sendHelp();
            die();
        }
        if ( ! $this->recognizedCommand) {
            $this->getCommand();
        }
        /**
         * Это что то связано с очередностью триггеров, была какая то проблема,
         * связанная с делегированием, триггер должен знать КОМУ передовать или от КОГО принимать
         * но как это сейчас работает черт его знает. Пусть лучше FORWARD и остается
         * TODO
         * убрать или проверить в чем преимущества
         * @see Alisa::setTriggerDataDirection()
         */

        $utterance = $this->request->getUtterance();
        if ( ! $this->request->isNewSession()) {
            if ($this->directionType === DirectionType::FORWARD) {
                $this->storage->setItem($this->recognizedCommand->getName(), $utterance);
            } else {
                $this->storage->setItem($this->storage->getPreviousTrigger(), $utterance);
            }
            $this->storage->save();
        }
    }

    /**
     *
     * @param $direction
     * @throws ReflectionException
     */
    public function setTriggerDataDirection($direction): void
    {
        if (in_array($direction, DirectionType::getConstants(), true)) {
            $this->directionType = $direction;
        }
    }

    /**
     * @param Trigger $trigger
     * @param callable $func
     */
    public function sendResponse(Trigger $trigger, callable $func): void
    {

        if ($trigger === $this->recognizedCommand) {
            /** @var Response $answer */
            $answer = $func();
            $response = $this->request->getServiceData();
            $response['response'] = $answer->send();
            $this->storage->save();
            die(json_encode($response));
        }
    }

    /**
     * @param $triggerName
     * @param bool $saveCurrentSession
     */
    public function substituteTriggerTo($triggerName, $saveCurrentSession = false): void
    {
        if ($this->recognizedCommand) {
            $subsTo = $this->triggers->getByName($triggerName);
            if ($subsTo) {
                $this->getRequest()->makeSubstitued($this->recognizedCommand->getName(), $subsTo->getName());
            }
        }
        $answer = new Response();
        $answer->addText('Внутренняя ошибка');
        $response = $this->request->getServiceData();
        $response['response'] = $answer->send();
        if ($saveCurrentSession) {
            $this->storage->save();
        }
        die(json_encode($response));
    }


    protected function sendHelp(): void
    {
        $answer = new Response();
        $answer->addText('Приветствую! Бот запущен, но не настроены стандратные триггеры.');

        $button = new Button('Что такое стандартные триггеры?');
        $button->addLink('https://github.com/isamarin/alisa/tree/master#стандартные-триггеры');
        $button->setHide(true);

        $button2 = new Button('Посмотреть пример реализации');
        $button2->addLink('');
        $button2->setHide(true);


        $answer->addButton($button, $button2);
        $response = $this->request->getServiceData();
        $response['response'] = $answer->send();
        die(json_encode($response));
    }


    /**
     * @param $data
     * @param $key
     */
    public function storeCommonData($data, $key): void
    {
        $this->storage->setItem($key, $data);

    }

    /**
     * @param $key
     * @return |null |null
     */
    public function getCommonData($key)
    {
        return $this->storage->getItem($key);
    }
}