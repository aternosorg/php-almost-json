<?php

namespace Aternos\AlmostJson\Comment;

use Aternos\AlmostJson\Input;

class BlockComment implements AlmostJsonCommentInterface
{
    protected const START = "/*";
    protected const END = "*/";

    /**
     * @inheritDoc
     */
    public static function detect(Input $input): bool
    {
        return $input->check(static::START);
    }

    /**
     * @inheritDoc
     */
    public static function skip(Input $input): void
    {
        $input->skip(2);
        while ($input->valid() && !$input->check(static::END)) {
            $input->skip();
        }
        if ($input->check(static::END)) {
            $input->skip(2);
        }
    }
}
