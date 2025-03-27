<?php

namespace Aternos\PhpAlmostJson\Comment;

use Aternos\PhpAlmostJson\Input;

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
    public static function read(Input $input): void;
}
