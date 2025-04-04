<?php

namespace Aternos\AlmostJson\Node;

use Aternos\AlmostJson\Input;
use Aternos\AlmostJson\AlmostJsonParser;

class NullNode extends AlmostJsonNode
{
    protected const NULL = "null";

    /**
     * @inheritDoc
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool
    {
        return $input->checkInsensitive(static::NULL);
    }

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @inheritDoc
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void
    {
        $input->assertInsensitive(static::NULL);
        $input->skip(4);
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): null
    {
        return null;
    }
}
