<?php

namespace isamarin\Alisa;

use isamarin\Alisa\Interfaces\RecognitionInterface;
use isamarin\Alisa\Traits\Morphy;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;

class DistanceRecognition implements RecognitionInterface
{
    use Morphy;

    /**
     * Возвращает лучшее значение дистанции Дамерау-Левенштайна для выбранного триггера
     * @param Request $request
     * @param Trigger $trigger
     * @return int
     * @see https://ru.wikipedia.org/wiki/Расстояние_Дамерау_—_Левенштейна
     */


    public function rateSimilarities(Request $request, Trigger $trigger): int
    {
        $requestWordsString = implode(' ', $this->convertToBaseForm($request->getWords()));
        $triggerWords = $trigger->getWords();
        $out = [];
        foreach ($triggerWords[0] as $level) {
            $_level = implode(' ', $this->convertToBaseForm(explode(' ', $level)));
            $distance = new DamerauLevenshtein($requestWordsString, $_level);
            $out[] = $distance->getSimilarity();
        }
        sort($out);

        return array_shift($out);
    }

}