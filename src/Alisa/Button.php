<?php

namespace isamarin\Alisa;

use Symfony\Component\Yaml\Tests\B;

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

    public function __construct(string $title = 'untitled', $hide = true)
    {
        if ($title) {
            $this->title = $title;
        }
        $this->hide = $hide;
    }

    /**
     * Устанавливает URL к кнопке
     * @param string $link
     * @return Button
     */
    public function addLink(string $link)
    {
        if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
            $this->link = $link;
        }
        return $this;
    }

    /**
     * @param string $title
     * @return Button
     */
    public function setTitle(string $title)
    {
        if ($title) {
            $this->title = $title;
        }
        return $this;
    }

    /**
     * @param bool $hide
     * @return Button
     */
    public function setHide(bool $hide)
    {
        $this->hide = $hide;
        return $this;
    }

    /**
     * Связывает триггер, который сработает при нажатии на кнопку
     * @param Trigger $trigger
     * @param string $data
     */
    public function linkTrigger(Trigger $trigger, $data = null): Button
    {
        if ($trigger->isValid()) {
            $this->trigger['NAME'] = $trigger->getName();
            $this->trigger['DATA'] = $data;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $res = [];
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