<?php

namespace isamarin\Alisa\Interfaces;

/**
 * Interface RequestInterface
 * @package isamarin\Alisa\Interfaces
 */
interface RequestInterface
{
    /**
     * @return string
     */
    public function getSessionID(): string;

    /**
     * @return string
     */
    public function getUserID(): string;

    /**
     * @return string
     */
    public function getClientID(): string;

    /**
     * @return int
     */
    public function getMessageID(): int;

    /**
     * @return bool
     */
    public function isButtonClick(): bool;

    /**
     * @return bool
     */
    public function isVoiceRequest(): bool;

    /**
     * @return bool
     */
    public function isNewSession(): bool;

    public function getPayloadData();

    /**
     * @return array
     */
    public function getWords(): array;

    /**
     * @return bool
     */
    public function isBadLanguage(): bool;

    /**
     * @return string
     */
    public function getUtterance(): string;
}