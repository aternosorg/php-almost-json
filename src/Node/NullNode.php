<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\AlmostJsonParser;

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
        $input->read(4);
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): null
    {
        return null;
    }
}
