<?php

namespace isamarin\Alisa\Interfaces;

use isamarin\Alisa\Request;
use isamarin\Alisa\Trigger;

interface RecognitionInterface
{
    /**
     * @param Request $request
     * @param Trigger $trigger
     * @return int
     */
    public function rateSimilarities(Request $request, Trigger $trigger): int;
}