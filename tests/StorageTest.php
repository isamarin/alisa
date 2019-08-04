<?php

namespace isamarin\Alisa;

use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{

    public static function loadJson($name)
    {
        return json_decode(file_get_contents(__DIR__ . '/json/' . $name . '.json'), true);
    }


    public function testNormal()
    {
       $request = new Request(self::loadJson('0'));
       $storage = new SessionStorage($request);

       $trigger = new Trigger('TEST');

       $storage->storeTrigger($trigger);

    }
}