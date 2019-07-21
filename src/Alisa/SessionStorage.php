<?php

namespace isamarin\Alisa;

class SessionStorage
{
    const TRIGGER = 'trigger';
    const SESSION = 'sessions';
    const REQUEST = 'request';
    const COMMON = 'common';
    const ALLOWED = 'allowed_class';
    protected $dir;
    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var Response $response
     */

    protected $data;
    protected $file;

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

    protected function getData()
    {
        $this->file = $this->dir . DIRECTORY_SEPARATOR . $this->request->getSessionID() . '.json';
        if (file_exists($this->file)) {
            $this->data = json_decode(file_get_contents($this->file), true);
        } else {
            $this->data[self::SESSION][$this->request->getMessageID()][self::REQUEST] = serialize($this->request);
            $this->save();
        }
    }

    public function storeTrigger(Trigger $trigger)
    {
        $this->data[self::SESSION][$this->request->getMessageID()][self::TRIGGER] = serialize($trigger);
    }

    public function setItem($key, $item)
    {
        $this->data['common'][$key] = $item;
    }

    public function getItem($key)
    {
        if (array_key_exists($key, $this->data[self::COMMON])) {
            return $this->data[self::COMMON][$key];
        }
        return null;
    }

    public function getPreviousRequest()
    {
        if ($this->request->getMessageID() !== 0) {
            return unserialize($this->data[self::SESSION][$this->request->getMessageID() - 1][self::REQUEST],
                [self::ALLOWED => [get_class(Request::class)]]);
        }
        return $this->request;
    }

    public function getPreviousTrigger()
    {
        if ($this->request->getMessageID() !== 0) {
            return unserialize($this->data[self::SESSION][$this->request->getMessageID() - 1][self::TRIGGER],
                [self::ALLOWED => [get_class(Trigger::class)]]);
        }
        return null;
    }

    public function getTriggerByMessageID($messageID)
    {
        if (array_key_exists($messageID, $this->data[self::SESSION])) {
            return unserialize($this->data[self::SESSION][$messageID][self::TRIGGER],
                [self::ALLOWED => [get_class(Trigger::class)]]);
        }
        return null;
    }

    protected function checkDirectory(): bool
    {
        if ( ! file_exists($this->dir)) {
            return ! ( ! mkdir($this->dir)
                && ! is_dir($this->dir));
        }
        return true;
    }

    public function save()
    {
        file_put_contents($this->file, json_encode($this->data));
    }
}