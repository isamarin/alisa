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
     */
    public function addTokens(array ...$tokens): void
    {
        foreach ($tokens as $tokenGroup) {
            $out = [];
            foreach ($tokenGroup as $word) {
                $out[] = strtoupper($word);
            }
            $this->words[] = $out;
        }
    }

    public function getWords()
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
     */
    public function setNextTrigger(Trigger $next): void
    {
        if ($next->isValid()) {
            $this->next = $next;
        }
    }

    public function setStoreData(bool $shouldStore): void
    {
        $this->storeData = $shouldStore;
    }

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
     */
//    public function setAsDefault(bool $default = true)
//    {
//        $this->default = $default;
//
//    }

    /**
     * Устанавливает данный триггер как стартовый
     * @param bool $start
     * @see Request::isNewSession()
     */
//    public function setAsInit(bool $start = true)
//    {
//
//        $this->start = $start;
//
//    }

    /**
     * Задействовать данный триггер, в случае если команда не была распознана ботом
     * @return bool
     */
//    public function setAsMistake(bool $mistake = true)
//    {
//        $this->mistake = $mistake;
//    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function isMistake(): bool
    {
        return $this->mistake;
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