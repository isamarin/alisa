<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use isamarin\Alisa\Traits\Morphy;

class MorphyRecognition implements RecognitionInterface
{
    use Morphy;

    /**
     * Возвращает 1 если найдено точное совпадение по группам ключевых слов
     * выбранного триггера, или 0, если хотя бы одна из групп не совпала
     * @param Request $request
     * @param Trigger $trigger
     * @return int
     */
    public function rateSimilarities(Request $request, TriggerIterator $iterator): Trigger
    {

        foreach ($iterator as $trigger) {
            $requestWords = $this->convertToBaseForm($request->getWords());
            $triggerWords = $trigger->getTokens();
            if (count($triggerWords)) {
                foreach ($triggerWords as $level) {
                    $levelWords = $this->convertToBaseForm($level);
                    if ( ! $this->compare($requestWords, $levelWords)) {
                        break 2;
                    }
                }
                return $trigger;
            }
        }
        return $iterator->getMistakeTrigger();
    }

    /**
     * @param $one
     * @param $two
     * @return bool
     */
    protected function compare($one, $two): bool
    {
        foreach ($one as $w) {
            if (in_array($w, $two, true)) {
                return true;
            }
        }
        return false;
    }

}