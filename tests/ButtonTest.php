<?php

namespace isamarin\Alisa;

use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    public function testLinkSimple()
    {
        $button = new Button('Look At This');
        $button->addLink('https://github.com/isamarin/alisa');
        $this->assertEquals([
            'title' => 'Look At This',
            'hide' => false,
            'link' => 'https://github.com/isamarin/alisa',
        ], $button->get());
        $this->assertCount(3, $button->get());

        $button->setTitle('OMG');
        $this->assertEquals([
            'title' => 'OMG',
            'hide' => false,
            'link' => 'https://github.com/isamarin/alisa',
        ], $button->get());
        $this->assertCount(3, $button->get());

        $button->setHide(true);
        $this->assertEquals([
            'title' => 'OMG',
            'hide' => true,
            'link' => 'https://github.com/isamarin/alisa',
        ], $button->get());
        $this->assertCount(3, $button->get());

        unset($button);

        $button = new Button();
        $button->setHide(true);
        $this->assertEquals([
            'title' => 'untitled',
            'hide' => true,
        ], $button->get());
        $this->assertCount(2, $button->get());
    }


}