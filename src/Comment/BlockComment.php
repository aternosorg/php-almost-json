<?php

namespace Aternos\PhpAlmostJson\Comment;

use Aternos\PhpAlmostJson\Input;

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
    public static function read(Input $input): void
    {
        $input->read(2);
        while ($input->valid() && !$input->check(static::END)) {
            $input->read();
        }
        if ($input->check(static::END)) {
            $input->read(2);
        }
    }
}
