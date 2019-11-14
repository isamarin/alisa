<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use isamarin\Alisa\Traits\Morphy;

class MorphyRecognition implements RecognitionInterface
{
    use Morphy;

    /**
     * @param Request $request
     * @param Trigger $trigger
     * @return int
     */
    public function rateSimilarities(Request $request, TriggerIterator $iterator): Trigger
    {
        foreach ($iterator as $trigger) {
            /** @var Trigger $trigger */
            if ($trigger->isInit() || $trigger->isDefault() || $trigger->isMistake()) {
                continue;
            }
            $requestWords = $this->convertToBaseForm($request->getWords());
            $triggerWords = $trigger->getTokens();
            $count = count($triggerWords);
            $suggested = 0;
            if ($count) {
                foreach ($triggerWords as $level) {
                    $levelWords = $this->convertToBaseForm($level);
                    if ($this->compare($requestWords, $levelWords)) {
                        $suggested++;
                    }
                }
            } else {
                continue;
            }
            if ($count===$suggested){
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