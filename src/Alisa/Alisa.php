<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use ReflectionException;
use function array_key_exists;
use function in_array;

/**
 * Class Alisa
 * @package Alisa
 */
class Alisa
{
    public const YANDEX_RESPONSE = 'response';
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

    protected $repeat;

    protected $watchers;


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
     * Путь до морфологического словаря
     * @param $path
     */
    public function setDictionaryPath($path): void
    {
        $GLOBALS['DICTS_PATH'] = $path;
    }

    /**
     * Путь сохранения сессий
     * @param $path
     */
    public function setSessionsPath($path): void
    {
        $GLOBALS['SES_PATH'] = $path;
    }


    /**
     * Устаналивает выбранный алгоритм распозования
     * @param RecognitionInterface $algorithm
     * @see MorphyRecognition по умолчанию
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
     * @return Trigger
     */
    public function getRecognizedCommand(): Trigger
    {
        return $this->recognizedCommand;
    }

    /**
     * @param Trigger ...$trigger
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

        $utterance = $this->request->getUtterance();
        if ($this->request->isNewSession()) {
            $this->storage->storeTrigger($this->recognizedCommand, 'new session');
        } elseif ($this->recognizedCommand) {
            $replaced = null;
            if ($this->directionType === DirectionType::BACKWARD) {
                $replaced = $this->storage->getPreviousTrigger()[SessionStorage::NAME];
                if ($this->triggers->getByName($replaced)->isDefault()) {
                    $replaced = null;
                }
            }
            if ($this->request->isButtonClick()) {
                $utterance = $this->request->getPayloadData()[Button::TITLE];

                if (isset($this->request->getPayloadData()[Button::ATTACH])) {
                    $replaced = $this->request->getPayloadData()[Button::ATTACH];
                }
                if (isset($this->request->getPayloadData()[Button::SERVICES][Paginator::KEEPDATA])) {
                    $utterance = $this->request->getPayloadData()[Button::SERVICES][Paginator::KEEPDATA];
                    $replaced = null;
                }
            }

            $this->storage->storeTrigger($this->recognizedCommand, $utterance, $replaced);
        }
        $this->storage->save();
    }

    protected function sendHelp(): void
    {
        $answer = new Response();
        $answer->addText('Приветствую! Бот запущен, но не настроены стандратные триггеры.');

        $button = new Button('Что такое стандартные триггеры?');
        $button->addLink('https://github.com/isamarin/alisa/tree/master#стандартные-триггеры');
        $button->setHide(true);

        $button2 = new Button('Посмотреть пример реализации');
        $button2->addLink('https://github.com/isamarin/alisa/tree/exmaple/');
        $button2->setHide(true);


        $answer->addButton($button, $button2);
        $response = $this->request->getServiceData();
        $response[self::YANDEX_RESPONSE] = $answer->send($this->recognizedCommand);
        die(json_encode($response));
    }

    /**
     * TODO понять как это редъюсить
     * мб разбить на методы?
     *
     * @return bool
     */
    protected function getCommand(): bool
    {
        /* Первое сообщение – приветствие */
        if ($this->request->getMessageID() === 0) {
            $this->recognizedCommand = $this->helloCommand;
            return true;
        }

        /* Проверяем клик ли это на кнопку */
        if ($this->request->isButtonClick()) {
            $button = $this->request->getPayloadData();
            if (array_key_exists(Button::NAME, $button)) {
                $this->recognizedCommand = $this->triggers
                    ->getByName($this->request->getPayloadData()[Button::NAME]);
            } else {
                $this->recognizedCommand = $this->triggers->getDefaultTrigger();
                $this->storage->setTriggerData($this->storage->getPreviousTrigger(), $this->request->getUtterance());
            }
            if (array_key_exists(Button::ASSIGN, $button)) {
                $this->storage->setTriggerData($button[Button::ASSIGN], $button[Button::TITLE]);
            }
            return true;
        }

        if ($this->request->isSubstitued()) {
            $this->recognizedCommand = $this->triggers->getByName($this->request->isSubstitued()[Request::SUBSTITUTED_TO]);
            return true;
        }


        /** @var array $arPreviousTrigger */
        $arPreviousTrigger = $this->storage->getPreviousTrigger();
        if ($arPreviousTrigger && isset($arPreviousTrigger[SessionStorage::NEXT])) {
            $this->recognizedCommand = $this->triggers->getByName($arPreviousTrigger[SessionStorage::NEXT]);
            return true;
        }

        $this->recognizeCommand();
        return true;
    }

    protected function recognizeCommand(): void
    {
        $this->recognizedCommand = $this->alghoritm->rateSimilarities($this->request, $this->triggers);
    }

    /**
     * Устаналивает направление складирования сессии.
     * По умолчанию – задом наперед.
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
     * Возвращает флаг, является ли данный запрос самоповтором одного из триггеров
     * @return bool
     */
    public function isRepeatedRequest(): bool
    {
        if ($this->recognizedCommand && isset($this->storage->getPreviousTrigger()[SessionStorage::NEXT])
            && $this->storage->getPreviousTrigger()[SessionStorage::NEXT] === $this->recognizedCommand->getName()
            && $this->recognizedCommand->getName() === $this->storage->getPreviousTrigger()[SessionStorage::NAME]) {
            return true;
        }
        if (isset($this->request->getPayloadData()[Button::SERVICES][Paginator::REPEAT]) && $this->request->getPayloadData()[Button::SERVICES][Paginator::REPEAT]) {
            return true;
        }
        return false;
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
            if ($this->watchers) {
                foreach ($this->watchers as $watcher) {
                    /** @var callable $watcher */
                    $modfiedAnswer = $watcher($answer);
                    if ($modfiedAnswer instanceof Response) {
                        $answer = $this->modifyResponse($answer, $modfiedAnswer);
                    }
                }
            }
            if ($this->repeat) {
                $this->recognizedCommand->nextDelegate($this->recognizedCommand);
                $this->storage->storeTrigger($this->recognizedCommand,
                    $this->storage->getTriggerData($this->recognizedCommand->getName()));
            }
            $response = $this->request->getServiceData();
            if ( ! $this->request->isNewSession()) {
                $answer->serviceActions($this->request->getPayloadData(), $this->recognizedCommand,
                    $this->storage->getTriggerData($this->recognizedCommand->getName()));
            }
            $response[self::YANDEX_RESPONSE] = $answer->send($this->recognizedCommand);
            $this->storage->save();
            die(json_encode($response));
        }
    }


    /**
     * Глобалный модифиактор для ответа. Позволяет обрабатывать условия
     * и дополнять кнопками ответ
     * Должен возвращать Alisa::Response если нужна замена
     * Может быть использован для установки каких либо значений в сессию или
     * перехвату нежелательных комманд с последующим делегированием
     * @param Response $old
     * @param Response $new
     * @return Response
     * @example Пользователь должен авторизоваться, прежде чем получить доступ к команде голосом,
     * проверяем авторизован ли? Если нет то делегируем на другой триггер, например на триггер атворизации
     *
     */
    protected function modifyResponse(Response $old, Response $new): Response
    {
        return $old->addButtonsArray($new->getButtons());
    }

    /**
     * Делегирует исполнение теущего распознаного трииггера другому триггеру.
     * Пользователю отправляется только последний делегированный триггер
     * @param $triggerName
     * @param bool $saveCurrentSession
     */
    public function substituteTriggerTo($triggerName, $saveCurrentSession = null): void
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
        $response[self::YANDEX_RESPONSE] = $answer->send($this->recognizedCommand);
        trigger_error('Невернная передача триггер - триггер');
        if ($saveCurrentSession) {
            $this->storage->save();
        }
        die(json_encode($response));
    }

    /**
     * Возвращает запрос, который был получен от Яндекс.Алиса
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     *
     * @param $triggerName
     * @param $data
     */
    public function appendDataToTrigger($triggerName, $data): void
    {
        if ($triggerName && $data && $this->triggers->getByName($triggerName) && ! $this->storage->getTriggerData($triggerName)) {
            $this->storage->setTriggerData($triggerName, $data);
        }
    }

    /**
     * Сохраняет какие-нибудь данные в рамках работы бота
     * @param $data
     * @param $key
     * @see Alisa::getCommonData()
     */
    public function storeCommonData($data, $key): void
    {
        $this->storage->setItem($key, $data);

    }

    /**
     * @param string $triggerName
     * @return mixed
     */
    public function getTriggerData(string $triggerName)
    {
        return $this->storage->getTriggerData($triggerName);
    }

    /**
     * @param callable $function
     */
    public function addResponseModifier(callable $function): void
    {
        $this->watchers[] = $function;
    }

    /**
     * Устаналивает флаг самоповтора – триггер после отправки данных укажет, что пользотвальские
     * данные нужно переправить обратно на этот же триггер
     * @param bool $bRepeatTrigger
     */
    public function setRepeat(bool $bRepeatTrigger): void
    {
        $this->repeat = $bRepeatTrigger;
    }

    /**
     * Возвращает данные, которые были установлены в боте вручную
     * @param $key
     * @return null|string
     * @see Alisa::storeCommonData()
     */
    public function getCommonData($key): ?string
    {
        return $this->storage->getItem($key);
    }

    /**
     * Возвращает флаг, если пользователь использовал сервисные копнки пагинатора
     * @return bool
     * @see \isamarin\Alisa\Response::setButtonsPaginator()
     */
    public function isPaginatorCall(): bool
    {
        return isset($this->request->getPayloadData()[Button::SERVICES][Paginator::TOPAGE]);
    }
}