<?php

namespace isamarin\Alisa;

use ReflectionClass;

abstract class RecognizedType
{
    const MORPHY_STRICT = 0;
    const DAMERAU_LEVENSHTEIN_DISTANCE = 1;

    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}