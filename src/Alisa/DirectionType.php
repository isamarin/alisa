<?php

namespace isamarin\Alisa;

use ReflectionClass;
use ReflectionException;

/**
 * Class DirectionType
 * @package isamarin\Alisa
 */
abstract class DirectionType
{
    public const BACKWARD = 0;
    public const FORWARD = 1;

    /**
     * @return array
     * @throws ReflectionException
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}