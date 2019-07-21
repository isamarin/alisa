<?php

namespace isamarin\Alisa;

/**
 * Class Button
 * @package Alisa
 */
class Button
{
    /** @var array $trigger */
    protected $trigger;
    /** @var string $title */
    protected $title;
    /** @var bool $hide */
    protected $hide;
    /** @var string $command */
    protected $link;

    public function __construct(string $title = null, $hide = false)
    {
        if ($title) {
            $this->title = $title;
        }
        $this->hide = $hide;
    }

    /**
     * Устанавливает URL к кнопке
     * @param string $link
     */
    public function addLink(string $link)
    {
        if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
            $this->link = $link;
        }
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        if ($title) {
            $this->title = $title;
        }
    }

    /**
     * @param bool $hide
     */
    public function setHide(bool $hide)
    {
        $this->hide = $hide;
    }

    /**
     * Связывает триггер, который сработает при нажатии на кнопку
     * @param Trigger $trigger
     */
    public function linkTrigger(Trigger $trigger, $data = null)
    {
        if ($trigger->isValid()) {
            $this->trigger['NAME'] = $trigger->getName();
            $this->trigger['DATA'] = $data;
        }
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $res = [];
        if ( ! $this->title) {
            $this->title = 'untitled';
        }
        $res['title'] = $this->title;
        if ($this->trigger) {
            $res['payload'] = $this->trigger;
        }
        if ($this->link) {
            $res['link'] = $this->link;
        }
        $res['hide'] = $this->hide;

        return $res;
    }
}