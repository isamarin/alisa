<?php

namespace isamarin\Alisa;

use ReflectionClass;

abstract class DirectionType
{
    public const BACKWARD = 0;
    public const FORWARD = 1;

    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}