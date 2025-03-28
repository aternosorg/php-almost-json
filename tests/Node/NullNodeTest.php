<?php

namespace Tests\Node;

use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\Node\NullNode;

class NullNodeTest extends NodeTestCase
{
    public function testParseNull(): void
    {
        $input = new Input("null");
        $node = new NullNode();
        $node->read($input, $this->parser);
        $this->assertEquals(null, $node->toNative());
    }

    public function testCaseInsensitive(): void
    {
        $input = new Input("NULL");
        $node = new NullNode();
        $node->read($input, $this->parser);
        $this->assertEquals(null, $node->toNative());
    }

    public function testInvalidValue(): void
    {
        $input = new Input("notanull");
        $node = new NullNode();
        $this->expectException(UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testDetect(): void
    {
        $input = new Input("null");
        $this->assertTrue(NullNode::detect($input, $this->parser));

        $input = new Input("NULL");
        $this->assertTrue(NullNode::detect($input, $this->parser));

        $input = new Input("notanull");
        $this->assertFalse(NullNode::detect($input, $this->parser));
    }
}
