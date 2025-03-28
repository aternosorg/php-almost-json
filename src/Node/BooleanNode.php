<?php

namespace Aternos\AlmostJson\Node;

use Aternos\AlmostJson\Input;
use Aternos\AlmostJson\AlmostJsonParser;

class BooleanNode extends AlmostJsonNode
{
    protected const TRUE = "true";
    protected const FALSE = "false";

    protected bool $value;

    /**
     * @inheritDoc
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool
    {
        return $input->checkInsensitive(static::TRUE, static::FALSE);
    }

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @inheritDoc
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void
    {
        $input->assertInsensitive(static::TRUE, static::FALSE);
        $this->value = $input->checkInsensitive(static::TRUE);
        if ($this->value) {
            $input->skip(4);
        } else {
            $input->skip(5);
        }
    }

    /**
     * @return bool
     */
    public function getValue(): bool
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): bool
    {
        return $this->value;
    }
}
