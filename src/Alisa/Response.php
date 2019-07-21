<?php

namespace isamarin\Alisa;

class Response
{
    private $answers;
    private $buttons = [];

    public function addText(string $text, string $tts = null)
    {
        $answer = [];
        if ($text) {
            $answer['text'] = $text;
            if ($tts) {
                $answer['tts'] = $tts;
            } else {
                $answer['tts'] = $text;
            }
            $this->answers[] = $answer;
        }
        return $this;
    }

    public function addButton(Button ... $buttons)
    {
        if ($buttons) {
            $res = [];
            foreach ($buttons as $button) {
                $res[] = $button->get();
            }
            $this->buttons = array_merge($this->buttons, $res);
        }
        return $this;
    }

    public function send(): array
    {
        $text = $this->answers[array_rand($this->answers, 1)];
        if ($this->buttons) {
            $text['buttons'] = $this->buttons;
        }
        return $text;
    }
}