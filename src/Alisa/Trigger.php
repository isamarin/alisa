<?php

namespace isamarin\Alisa;

class Trigger
{
    private $name;
    /** @var Trigger $next */
    private $next = false;
    protected $words = [];
    private $storeData = false;
    protected $default = false;
    protected $mistake = false;
    protected $start = false;

    public function __construct($name)
    {
        $this->name = strtoupper($name);
    }

    /**
     * Устаналивает группу слов синонимов
     * @param array ...$tokens
     * @return Trigger
     */
    public function addTokens(array ...$tokens): Trigger
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

    public function getWords(): array
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
    public function setNextTrigger(Trigger $next): Trigger
    {
        if ($next->isValid()) {
            $this->next = $next;
        }
        return $this;
    }

    /**
     * @param bool $shouldStore
     * @deprecated
     */
    public function setStoreData(bool $shouldStore): void
    {
        $this->storeData = $shouldStore;
    }

    /**
     * @deprecated
     */
    public function isStoreData(): bool
    {
        return $this->storeData;
    }

    public function getNextTrigger(): Trigger
    {
        return $this->next;
    }

    public function hasNextTrigger(): bool
    {
        return $this->next ? true : false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливает триггер, срабатывающий по умолчанию
     * @param bool $default
     * @return Trigger
     */
    public function setAsDefault(bool $default = true): Trigger
    {
        $this->default = $default;
        return $this;

    }

    /**
     * Устанавливает данный триггер как стартовый
     * @param bool $start
     * @return Trigger
     * @see Request::isNewSession()
     */
    public function setAsInit(bool $start = true): Trigger
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Задействовать данный триггер, в случае если команда не была распознана ботом
     * @param bool $mistake
     * @return Trigger
     */
    public function setAsMistake(bool $mistake = true): Trigger
    {
        $this->mistake = $mistake;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default ? true : false;
    }

    public function isMistake(): bool
    {
        return $this->mistake ? true : false;
    }

    public function isInit(): bool
    {
        return $this->start;
    }

    /**
     * Проверяет триггер на корректность
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->name ? true : false;
    }


}