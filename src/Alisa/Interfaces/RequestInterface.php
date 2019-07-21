<?php

namespace isamarin\Alisa\Interfaces;

interface RequestInterface
{
    public function getSessionID(): string;

    public function getUserID(): string;

    public function getClientID(): string;

    public function getMessageID(): int;

    public function isButtonClick(): bool;

    public function isVoiceRequest(): bool;

    public function isNewSession(): bool;

    public function getPayloadData();

    public function getWords(): array;

    public function isBadLanguage(): bool;

    public function getUtterance(): string;
}