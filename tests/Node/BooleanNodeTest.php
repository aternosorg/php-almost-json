<?php

namespace Tests\Node;

use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\Node\BooleanNode;
use Tests\Node\NodeTestCase;

class BooleanNodeTest extends NodeTestCase
{
    public function testParseBoolean(): void
    {
        $input = new Input("true");
        $node = new BooleanNode();
        $node->read($input, $this->parser);
        $this->assertTrue($node->getValue());
        $this->assertEquals(true, $node->toNative(true));

        $input = new Input("false");
        $node = new BooleanNode();
        $node->read($input, $this->parser);
        $this->assertFalse($node->getValue());
        $this->assertEquals(false, $node->toNative(true));
    }

    public function testCaseInsensitive(): void
    {
        $input = new Input("TRUE");
        $node = new BooleanNode();
        $node->read($input, $this->parser);
        $this->assertTrue($node->getValue());
        $this->assertEquals(true, $node->toNative(true));

        $input = new Input("FALSE");
        $node = new BooleanNode();
        $node->read($input, $this->parser);
        $this->assertFalse($node->getValue());
        $this->assertEquals(false, $node->toNative(true));
    }

    public function testInvalidValue(): void
    {
        $input = new Input("notabool");
        $node = new BooleanNode();
        $this->expectException(\Aternos\PhpAlmostJson\Exception\UnexpectedInputException::class);
        $node->read($input, $this->parser);
    }

    public function testDetect(): void
    {
        $input = new Input("true");
        $this->assertTrue(BooleanNode::detect($input, $this->parser));

        $input = new Input("false");
        $this->assertTrue(BooleanNode::detect($input, $this->parser));

        $input = new Input("notabool");
        $this->assertFalse(BooleanNode::detect($input, $this->parser));
    }
}
