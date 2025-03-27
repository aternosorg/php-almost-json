<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\AlmostJsonParser;
use Aternos\PhpAlmostJson\Input;
use stdClass;

class ObjectNode extends AlmostJsonNode
{
    protected const OPEN = "{";
    protected const CLOSE = "}";
    protected const COLON = ":";

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

            $key = new StringNode();
            $key->read($input, $parser);
            $input->skipWhitespace();

            $input->assert(static::COLON);
            $input->read();
            $input->skipWhitespace();

            $value = $parser->parseNext($input, $depth);
            $children[$key->getValue()] = $value;
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
     * @return AlmostJsonNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function toNative(bool $assoc = false): stdClass|array
    {
        if ($assoc) {
            $result = [];
            foreach ($this->children as $key => $child) {
                $result[$key] = $child->toNative($assoc);
            }
            return $result;
        }

        $result = new stdClass();
        foreach ($this->children as $key => $child) {
            $result->{$key} = $child->toNative($assoc);
        }
        return $result;
    }
}
