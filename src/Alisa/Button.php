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
    protected $assign;
    protected $attach = false;

    public const PAYLOAD = 'payload';
    public const TITLE = 'TITLE';
    public const ASSIGN = 'ASSIGN';
    public const ATTACH = 'ATTACH';
    public const NAME = 'NAME';

    public const YANDEX_HIDE = 'hide';
    public const YANDEX_TITLE = 'title';
    public const YANDEX_LINK = 'link';

    public const SERVICES = 'services';


    /**
     * Button constructor.
     * @param string $title
     * @param bool $hide
     */
    public function __construct(string $title = 'untitled', $hide = null)
    {
        if ($title) {
            $this->title = $title;
        }
        $this->hide = $hide ?? true;
    }

    /**
     * Устанавливает URL к кнопке
     * @param string $link
     * @return Button
     */
    public function addLink(string $link): Button
    {
        if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
            $this->link = $link;
        }
        return $this;
    }

    /**
     * Устанавливает заголовок для кнопки. Может быть передан через конструктор.
     * @param string $title
     * @return Button
     */
    public function setTitle(string $title): Button
    {
        if ($title) {
            $this->title = $title;
        }
        return $this;
    }

    /**
     * Устанавливает тип кнопки. Внизу как кнопка, или в сообщении как ссылка.
     * Если true – значит кнопка. false – ссылка
     * Может быть установлено через конструктор
     * @param bool $hide
     * @return Button
     */
    public function setHide(bool $hide): Button
    {
        $this->hide = $hide;
        return $this;
    }

    /**
     * Связывает триггер, который сработает при нажатии на кнопку
     * @param Trigger $trigger
     * @return Button
     */
    public function linkTrigger(Trigger $trigger): Button
    {
        if ($trigger->isValid()) {
            $this->trigger[self::NAME] = $trigger->getName();
        }
        return $this;
    }

    /**
     * Переопределяет принадлежность данных, полученных с кнопки другому тригеру
     * @param Trigger $trigger
     * @return Button
     * @see Button::linkTrigger()
     */
    public function assignDataTo(Trigger $trigger): Button
    {
        $this->assign = $trigger->getName();
        return $this;
    }

    /**
     * @param bool $toCurrent
     * @return Button
     */
    public function attach(bool $toCurrent): Button
    {
        $this->attach = $toCurrent;
        return $this;
    }

    /**
     * @param $payload
     * @return Button
     */
    public function addPayload($payload): Button
    {
        $this->trigger[self::SERVICES] = $payload;
        return $this;
    }

    /**
     * @return array
     * @internal Используется при отправки кнопки Яндексу
     */
    public function get(): array
    {
        $res = [];
        $res[self::YANDEX_TITLE] = $this->title;
        if ($this->trigger) {
            $res[self::PAYLOAD] = $this->trigger;
        }
        $res[self::PAYLOAD][self::TITLE] = $this->title;
        if ($this->link) {
            $res[self::YANDEX_LINK] = $this->link;
        }
        if ($this->attach) {
            $res[self::PAYLOAD][self::ATTACH] = $this->attach;
        }
        if ($this->assign) {
            $res[self::PAYLOAD][self::ASSIGN] = $this->assign;
        }
        $res[self::YANDEX_HIDE] = $this->hide;

        return $res;
    }

    /**
     * Возврашает string представление результата get
     * @return string
     * @see Button::get()
     */
    public function __toString()
    {
        return http_build_query($this->get());
    }

}