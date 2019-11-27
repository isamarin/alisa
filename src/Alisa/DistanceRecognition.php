<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use isamarin\Alisa\Traits\Morphy;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;

/**
 * Class DistanceRecognition
 * @package isamarin\Alisa
 */
class DistanceRecognition implements RecognitionInterface
{
    use Morphy;

    /**
     * @param Request $request
     * @param TriggerIterator $iterator
     * @return Trigger
     * @deprecated Пока не использовать
     * Возвращает триггер, имеющий лучшее значение дистанции Дамерау-Левенштайна
     * @see https://ru.wikipedia.org/wiki/Расстояние_Дамерау_—_Левенштейна
     */

    public function rateSimilarities(Request $request, TriggerIterator $iterator): Trigger
    {
        $res = [];
        foreach ($iterator as $trigger) {
            $requestWordsString = implode(' ', $this->convertToBaseForm($request->getWords()));
            $triggerWords = $trigger->getTokens();
            $out = [];
            foreach ($triggerWords[0] as $level) {
                $_level = implode(' ', $this->convertToBaseForm(explode(' ', $level)));
                $distance = new DamerauLevenshtein($requestWordsString, $_level);
                $out[] = $distance->getSimilarity();
            }
            sort($out);
            $res[$trigger->getName()] = array_shift($out);
        }
        sort($res);
        return $iterator->getByName(key(array_shift($res)));
    }

}