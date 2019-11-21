<?php

namespace isamarin\Alisa;

use function array_key_exists;

/**
 * Class SessionStorage
 * @package isamarin\Alisa
 */
class SessionStorage
{
    protected const TRIGGER = 'trigger';
    protected const SESSION = 'sessions';
    protected const REQUEST = 'request';
    protected const COMMON = 'common';
    protected const DATA = 'data';
    protected const ALLOWED = 'allowed_class';
    protected $dir;
    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var Response $response
     */

    /** @var array $data */
    protected $data;
    protected $file;

    /**
     * SessionStorage constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->dir = $_SERVER['DOCUMENT_ROOT'] . '/sessions/';
        if ($this->checkDirectory()) {
            $this->request = $request;
            $this->getData();
        } else {
            trigger_error('Ошибка при создании диррекории ' . $this->dir);
        }

    }

    protected function getData(): void
    {
        $this->file = $this->dir . DIRECTORY_SEPARATOR . $this->request->getSessionID() . '.json';
        if (file_exists($this->file)) {
            $this->data = json_decode(file_get_contents($this->file), true);
        }
    }

    /**
     * @param string $triggerName
     * @param $data
     */
    public function storeTrigger(string $triggerName, $data, $replaceTrigger = null): void
    {
        $this->data[self::SESSION][$this->request->getMessageID()][self::TRIGGER] = $triggerName;
        $this->data[self::SESSION][$this->request->getMessageID()][self::DATA] = $data;

        $this->data[self::TRIGGER][$triggerName] = $data;
        if ($replaceTrigger) {
            $this->data[self::TRIGGER][$replaceTrigger] = $data;
        }
    }

    /**
     * @param $trigger
     * @return |null
     */
    public function getTriggerData($trigger)
    {

        if (array_key_exists(self::TRIGGER, $this->data) && array_key_exists($trigger, $this->data[self::TRIGGER])) {
            return $this->data[self::TRIGGER][$trigger];
        }
        return null;
    }

    public function setTriggerData($trigger, $data)
    {
        $this->data[self::TRIGGER][$trigger] = $data;
        $this->save();
    }

    /**
     * @param $key
     * @param $item
     */
    public function setItem($key, $item): void
    {
        $this->data[self::COMMON][$key] = $item;
    }

    /**
     * @param $key
     * @return null
     */
    public function getItem($key)
    {
        if (isset($this->data[self::COMMON]) && array_key_exists($key, $this->data[self::COMMON])) {
            return $this->data[self::COMMON][$key];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getPreviousTrigger(): string
    {
        if ($this->request->getMessageID() !== 0) {
            return $this->data[self::SESSION][$this->request->getMessageID() - 1][self::TRIGGER];
        }
        return null;
    }

    /**
     * @param $messageID
     * @return |null |null |null
     */
    public function getTriggerByMessageID($messageID)
    {
        if (array_key_exists($messageID, $this->data[self::SESSION])) {
            return $this->data[self::SESSION][$messageID][self::TRIGGER];
        }
        return null;
    }

    /**
     * @return bool
     */
    protected function checkDirectory(): bool
    {
        if ( ! file_exists($this->dir)) {
            return ! ( ! mkdir($this->dir)
                && ! is_dir($this->dir));
        }
        return true;
    }

    public function save(): void
    {
        file_put_contents($this->file, json_encode($this->data));
    }

    /**
     * @return array
     */
    public function _asArray(): array
    {
        return (array)$this->data;
    }
}