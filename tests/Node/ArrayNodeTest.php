<?php

namespace Tests\Node;

use Aternos\PhpAlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\Node\ArrayNode;

class ArrayNodeTest extends NodeTestCase
{
    public function testParseArray(): void
    {
        $input = new Input("[1, 2, 3]");
        $node = new ArrayNode();
        $node->read($input, $this->parser);
        $this->assertCount(3, $node->getChildren());

        $this->assertEquals([1, 2, 3], $node->toNative(true));
    }

    public function testMissingOpen(): void
    {
        $input = new Input("1, 2, 3]");
        $node = new ArrayNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testEmpty(): void
    {
        $input = new Input("[]");
        $node = new ArrayNode();
        $node->read($input, $this->parser);
        $this->assertCount(0, $node->getChildren());
    }

    public function testTrailingComma(): void
    {
        $input = new Input("[1, 2, 3,]");
        $node = new ArrayNode();
        $node->read($input, $this->parser);
        $this->assertCount(3, $node->getChildren());
    }

    public function testMissingClose(): void
    {
        $input = new Input("[1, 2, 3");
        $node = new ArrayNode();
        $this->expectException(UnexpectedEndOfInputException::class);
        $node->read($input, $this->parser);
    }

    public function testDetect(): void
    {
        $input = new Input("[1, 2, 3]");
        $this->assertTrue(ArrayNode::detect($input, $this->parser));

        $input = new Input("{1, 2, 3}");
        $this->assertFalse(ArrayNode::detect($input, $this->parser));
    }
}
