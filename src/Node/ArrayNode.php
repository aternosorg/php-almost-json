<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\AlmostJsonParser;

class ArrayNode extends AlmostJsonNode
{
    protected const OPEN = "[";
    protected const CLOSE = "]";

    /**
     * @var AlmostJsonNode[]
     */
    protected array $children = [];

    /**
     * @inheritDoc
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool
    {
        return $input->check(static::OPEN);
    }

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @inheritDoc
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void
    {
        $input->assert(static::OPEN);
        $input->read();
        $input->skipWhitespace();

        $depth++;
        $children = [];
        while (true) {
            $input->skipWhitespace();
            if ($input->check(static::CLOSE)) {
                break;
            }
            $children[] = $parser->parseNext($input, $depth);
            $input->skipWhitespace();
            if (!$input->check(static::COMMA)) {
                break;
            }
            $input->read();
        }
        $input->skipWhitespace();
        $input->assert(static::CLOSE);
        $input->read();
        $this->children = $children;
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): array
    {
        $result = [];
        foreach ($this->children as $child) {
            $result[] = $child->toNative($assoc);
        }
        return $result;
    }

    /**
     * @return AlmostJsonNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
