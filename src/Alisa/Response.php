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
    private $paginatorLength;

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

    /**
     * @param int $length
     */
    public function setButtonsPaginator(int $length)
    {
        $this->paginatorLength = $length;
    }


    /**
     * Не использовать!
     * @internal
     */
    public function serviceActions($payload, $recognized):void
    {
        if ($this->paginatorLength) {
            $pag = new Paginator($payload, $recognized);
            $pag->setLimit($this->paginatorLength);
            foreach ($this->buttons as $button) {
                $pag->append($button);
            }
            $this->buttons = $pag->getPaginated();
        }
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