<?php

namespace Aternos\AlmostJson\Node;

use Aternos\AlmostJson\Input;
use Aternos\AlmostJson\AlmostJsonParser;

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
        $input->skip();
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
            $input->skip();
        }
        $input->skipWhitespace();
        $input->assert(static::CLOSE);
        $input->skip();
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
