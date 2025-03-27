<?php

namespace Aternos\PhpAlmostJson\Comment;

use Aternos\PhpAlmostJson\Input;

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
    public static function read(Input $input): void
    {
        while ($input->valid() && !$input->check("\n")) {
            $input->read();
        }
    }
}
