<?php

namespace isamarin\Alisa;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public static function loadJson($name)
    {
        return json_decode(file_get_contents(__DIR__ . '/json/' . $name . '.json'), true);
    }

    public function testInit()
    {
        $request = new Request(self::loadJson('0'));
        $this->assertEquals('42', $request->getUserID());
        $this->assertEquals(true, $request->isNewSession());
        $this->assertEquals('fake_session', $request->getSessionID());
        $this->assertEquals(0, $request->getMessageID());
        $this->assertEquals(['Hello', 'World'], $request->getWords());
        $this->assertEquals('Hello World', $request->getUtterance());
        $this->assertEquals('fake-client-42', $request->getClientID());
        $this->assertEquals(true, $request->isVoiceRequest());
        $this->assertEquals(false, $request->isButtonClick());
        $this->assertEquals(true, $request->isNewSession());
        $this->assertEquals(null, $request->getPayloadData());
        $this->assertEquals(false, $request->isBadLanguage());
        $this->assertEquals([
            'session' => [
                'message_id' => 0,
                'session_id' => 'fake_session',
                'user_id' => 42,
            ],
            'version' => '1.0',
        ], $request->getServiceData());


    }

    public function testTypes()
    {
        $request = new Request(self::loadJson('1'));
        $this->assertEquals(true, $request->isButtonClick());
        $this->assertEquals(false, $request->isVoiceRequest());
        $this->assertEquals(false, $request->isNewSession());
        $this->assertEquals(1, $request->getMessageID());
        $this->assertEquals(null, $request->getPayloadData());
        $this->assertEquals(true, $request->isBadLanguage());
    }

    public function testPayload()
    {
        $request = new Request(self::loadJson('2'));
        $this->assertEquals(2, $request->getMessageID());
        $this->assertEquals(true, $request->isButtonClick());
        $this->assertEquals(['NAME' => 'TEST', 'DATA' => 'CHUNGA-CHANGA']
            , $request->getPayloadData());

    }


}