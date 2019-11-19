<?php

namespace isamarin\Alisa;

/**
 * Class Response
 * @package isamarin\Alisa
 */
class Response
{
    private $answers;
    private $buttons = [];

    /**
     * @param string $text
     * @param string|null $tts
     * @return $this
     */

    public function addText(string $text, string $tts = null): self
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

    /**
     * @param Button ...$buttons
     * @return $this
     */
    public function addButton(Button ... $buttons): self
    {
        if ($buttons) {
            $this->buttons = array_merge($this->buttons, $buttons);
        }
        return $this;
    }


    public function serviceActions($payload,$recognized){

        $pag = new Paginator($payload, $recognized);
        foreach ($this->buttons as $button){
            $pag->append($button);
        }


        $this->buttons = $pag->getPaginated();
    }

    /**
     * @return array
     */
    public function send(): array
    {

        $rawButtons = [];
        foreach ($this->buttons as $button) {
          $rawButtons[] = $button->get();
        }

        $text = $this->answers[array_rand($this->answers, 1)];
        if ($this->buttons) {
            $text['buttons'] = $rawButtons;
        }
        return $text;
    }

}