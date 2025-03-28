<?php

namespace Aternos\AlmostJson\Comment;

use Aternos\AlmostJson\Input;

interface AlmostJsonCommentInterface
{
    /**
     * @param Input $input
     * @return bool
     */
    public static function detect(Input $input): bool;

    /**
     * @param Input $input
     * @return void
     */
    public static function skip(Input $input): void;
}
