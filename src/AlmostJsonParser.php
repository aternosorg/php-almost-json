<?php

namespace Aternos\AlmostJson;

use Aternos\AlmostJson\Exception\AlmostJsonException;
use Aternos\AlmostJson\Exception\MaxDepthException;
use Aternos\AlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\AlmostJson\Exception\UnexpectedInputException;
use Aternos\AlmostJson\Node\AlmostJsonNode;
use Aternos\AlmostJson\Node\ArrayNode;
use Aternos\AlmostJson\Node\BooleanNode;
use Aternos\AlmostJson\Node\NullNode;
use Aternos\AlmostJson\Node\NumberNode;
use Aternos\AlmostJson\Node\ObjectNode;
use Aternos\AlmostJson\Node\StringNode;
use stdClass;

class AlmostJsonParser
{
    /**
     * @var class-string<AlmostJsonNode>[]
     */
    protected const NODES = [
        NullNode::class,
        BooleanNode::class,
        ArrayNode::class,
        ObjectNode::class,
        NumberNode::class,
        StringNode::class
    ];

    protected bool $zeroPrefixOctal = false;
    protected int $maxDepth = 512;
    protected bool $topLevelUnquotedStringAllowed = false;

    /**
     * @return bool
     */
    public function isZeroPrefixOctal(): bool
    {
        return $this->zeroPrefixOctal;
    }

    /**
     * @param bool $zeroPrefixOctal
     * @return $this
     */
    public function setZeroPrefixOctal(bool $zeroPrefixOctal): static
    {
        $this->zeroPrefixOctal = $zeroPrefixOctal;
        return $this;
    }

    /**
     * @param string $input
     * @param bool $assoc
     * @return stdClass|array|string|int|float|bool|null
     * @throws AlmostJsonException
     * @throws MaxDepthException
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    public function parseString(string $input, bool $assoc = false): stdClass|array|string|int|float|bool|null
    {
        return $this->parse(new Input($input), $assoc);
    }

    /**
     * @param Input $input
     * @param bool $assoc
     * @return stdClass|array|string|int|float|bool|null
     * @throws AlmostJsonException
     * @throws MaxDepthException
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     */
    public function parse(Input $input, bool $assoc = false): stdClass|array|string|int|float|bool|null
    {
        return $this->parseNext($input)->toNative($assoc);
    }

    /**
     * @param Input $input
     * @param int $depth
     * @return AlmostJsonNode
     * @throws AlmostJsonException
     * @throws UnexpectedEndOfInputException
     * @throws UnexpectedInputException
     * @throws MaxDepthException
     * @internal
     */
    public function parseNext(Input $input, int $depth = 0): AlmostJsonNode
    {
        if ($depth > $this->maxDepth) {
            throw new MaxDepthException("Maximum depth of " . $this->maxDepth .
                " exceeded", $input->tell());
        }

        $input->skipWhitespace();
        $input->assertValid();

        foreach (static::NODES as $nodeClass) {
            if ($nodeClass::detect($input, $this)) {
                $node = new $nodeClass();
                $node->read($input, $this, $depth);
                return $node;
            }
        }

        throw new UnexpectedInputException("Expected JSON node" .
            ", got " . $input->peek(16), $input->tell());
    }

    /**
     * @return int
     */
    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    /**
     * @param int $maxDepth
     * @return $this
     */
    public function setMaxDepth(int $maxDepth): static
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTopLevelUnquotedStringAllowed(): bool
    {
        return $this->topLevelUnquotedStringAllowed;
    }

    /**
     * @param bool $topLevelUnquotedStringAllowed
     * @return $this
     */
    public function setTopLevelUnquotedStringAllowed(bool $topLevelUnquotedStringAllowed): static
    {
        $this->topLevelUnquotedStringAllowed = $topLevelUnquotedStringAllowed;
        return $this;
    }

}
