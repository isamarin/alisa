<?php

namespace isamarin\Alisa;

/**
 * Class Trigger
 * @package isamarin\Alisa
 */
class Trigger
{
    protected $words = [];
    protected $default = false;
    protected $mistake = false;
    protected $start = false;
    private $name;
    /** @var Trigger $next */
    private $next = false;
    private $storeData = true;

    /**
     * Trigger constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = strtoupper($name);
    }

    /**
     * Устаналивает группу слов синонимов
     * @param array $tokens
     * @return Trigger
     */
    public function linkTokens(...$tokens): Trigger
    {
        foreach ($tokens as $tokenGroup) {
            $out = [];
            foreach ($tokenGroup as $word) {
                $out[] = strtoupper($word);
            }
            $this->words[] = $out;
        }
        return $this;
    }

    /**
     * Возвращает коллекцию группу слов синонимов
     * @return array
     * @see Trigger::linkTokens()
     */
    public function getTokens(): array
    {
        return $this->words;
    }

    /**
     * Устанавливает триггер, который автоматически сработает после текущего
     *
     * Стоит использовать в случаях, когда нельзя распознать команду
     * тк запрос принимаемый от пользователя является какими-либо данными, например имя
     * или адрес
     * @param Trigger $next
     * @return Trigger
     */
    public function nextDelegate(Trigger $next): Trigger
    {
        if ($next->isValid()) {
            $this->next = $next;
        }
        return $this;
    }

    /**
     * Проверяет триггер на корректность
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->name ? true : false;
    }

    /**
     * @version с 1.5.0-beta по умолчанию все триггеры сохраняют данные
     * @deprecated
     */
    public function isStoreData(): bool
    {
        return $this->storeData;
    }

    /**
     * @param bool $shouldStore
     * @version с 1.5.0-beta по умолчанию все триггеры сохраняют данные
     * @deprecated
     */
    public function setStoreData(bool $shouldStore): void
    {
        $this->storeData = $shouldStore;
    }

    /**
     * Возвращает триггер, которому будет делегирована передача
     * @return Trigger
     * @see Trigger::nextDelegate()
     */
    public function getNextTrigger(): Trigger
    {
        return $this->next;
    }

    /**
     * Возвращает флаг, имеет ли триггер делегата
     * @return bool
     * @see Trigger::nextDelegate()
     */
    public function hasNextTrigger(): bool
    {
        return $this->next ? true : false;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает триггер, срабатывающий по умолчанию
     * @param bool $isDefault
     * @return Trigger
     */
    public function setAsDefault(bool $isDefault = true): Trigger
    {
        $this->default = $isDefault;
        return $this;

    }

    /**
     * Устанавливает данный триггер как стартовый
     * @param bool $isInit
     * @return Trigger
     * @see Request::isNewSession()
     */
    public function setAsInit(bool $isInit = true): Trigger
    {
        $this->start = $isInit;
        return $this;
    }

    /**
     * Задействовать данный триггер, в случае если команда не была распознана ботом
     * @param bool $isMistake
     * @return Trigger
     */
    public function setAsMistake(bool $isMistake = true): Trigger
    {
        $this->mistake = $isMistake;
        return $this;
    }

    /**
     * Возвращает флаг, является ли данный триггер по-умолчанию
     * @return bool
     * @see Trigger::setAsDefault()
     */
    public function isDefault(): bool
    {
        return $this->default ? true : false;
    }

    /**
     * Возвращает флаг, является ли данный обработчиком ошибки распознования
     * @return bool
     * @see Trigger::setAsMistake()
     */
    public function isMistake(): bool
    {
        return $this->mistake ? true : false;
    }

    /**
     * Возвращает флаг, является ли данный триггер приветсвующим
     * @return bool
     * @see Trigger::setAsInit()
     */
    public function isInit(): bool
    {
        return $this->start;
    }


}