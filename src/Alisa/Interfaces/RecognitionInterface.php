<?php

namespace isamarin\Alisa\Interfaces;

use isamarin\Alisa\Request;
use isamarin\Alisa\Trigger;
use isamarin\Alisa\TriggerIterator;

interface RecognitionInterface
{
    /**
     * @param Request $request
     * @param TriggerIterator $trigger
     * @return int
     */
    public function rateSimilarities(Request $request, TriggerIterator $trigger): Trigger;
}