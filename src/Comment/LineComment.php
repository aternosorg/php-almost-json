<?php

namespace Aternos\AlmostJson\Comment;

use Aternos\AlmostJson\Input;

class LineComment implements AlmostJsonCommentInterface
{
    /**
     * @inheritDoc
     */
    public static function detect(Input $input): bool
    {
        return $input->check("//") || $input->check("#");
    }

    /**
     * @inheritDoc
     */
    public static function skip(Input $input): void
    {
        while ($input->valid() && !$input->check("\n")) {
            $input->skip();
        }
    }
}
