<?php

namespace isamarin\Alisa\Interfaces;

use isamarin\Alisa\Request;
use isamarin\Alisa\Trigger;
use isamarin\Alisa\TriggerIterator;

/**
 * Interface RecognitionInterface
 * @package isamarin\Alisa\Interfaces
 */
interface RecognitionInterface
{
    /**
     * @param Request $request
     * @param TriggerIterator $trigger
     * @return Trigger
     */
    public function rateSimilarities(Request $request, TriggerIterator $trigger): Trigger;
}