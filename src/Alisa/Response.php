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
    private $recognized;

    /**
     * Response constructor.
     * @param $recognized
     */
    public function __construct()
    {
    }

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
        $this->merge($buttons);
        return $this;
    }

    /**
     * @param $buttons
     */
    protected function merge($buttons)
    {
        if ($buttons) {
            $this->buttons = array_merge($this->buttons, $buttons);
        }
    }

    /**
     * @param array $buttons
     * @return $this
     */
    public function addButtonsArray(array $buttons)
    {
        $this->merge($buttons);
        return $this;
    }

    /**
     * @param int $length
     */
    public function setButtonsPaginator(int $length): void
    {
        $this->paginatorLength = $length;
    }

    /**
     *
     */
    public function resetButtons(){
        $this->buttons = [];
    }

    /**
     * Не использовать!
     * @internal
     */
    public function serviceActions($payload, $recognized, $keepPreviosData): void
    {
        if ($this->paginatorLength) {
            $pag = new Paginator($payload, $recognized, $keepPreviosData);
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
    public function send(Trigger $recognized): array
    {
        $rawButtons = [];
        foreach ($this->buttons as $button) {
            /** @var Button $button */
            $raw = $button->get();
            if (isset($raw['payload']['ATTACH']) && $raw['payload']['ATTACH'] === true) {
                $raw['payload']['ATTACH'] = $recognized->getName();
            }
            $rawButtons[] = $raw;
        }

        $text = $this->answers[array_rand($this->answers, 1)];
        if ($this->buttons) {
            $text['buttons'] = $rawButtons;
        }
        return $text;
    }

}