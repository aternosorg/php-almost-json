<?php

namespace Tests;

use Aternos\PhpAlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\PhpAlmostJson\Exception\UnexpectedInputException;
use Aternos\PhpAlmostJson\Input;

class InputTest extends AlmostJsonTestCase
{
    protected Input $input;

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Input("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
    }

    public function testRead(): void
    {
        $this->assertEquals("Lorem ", $this->input->read(6));
        $this->assertEquals("ipsum ", $this->input->read(6));

        $this->assertEquals(44, strlen($this->input->read(100)));
    }

    public function testSkip(): void
    {
        $this->input->skip(6);
        $this->assertEquals(6, $this->input->tell());
        $this->assertEquals("ipsum ", $this->input->read(6));
    }

    public function testReadAll(): void
    {
        $this->assertEquals("Lorem ipsum ", $this->input->readAll("Lorem", "ipsum", " "));
    }

    public function testSkipAll(): void
    {
        $this->input->skipAll("Lorem", "ipsum", " ");
        $this->assertEquals(12, $this->input->tell());
        $this->assertEquals("dolor ", $this->input->read(6));
    }

    public function testPeek(): void
    {
        $this->assertEquals("Lorem ", $this->input->peek(6));
        $this->assertEquals(0, $this->input->tell());
        $this->input->skip(6);
        $this->assertEquals("ipsum ", $this->input->peek(6));
    }

    public function testTell(): void
    {
        $this->assertEquals(0, $this->input->tell());
        $this->input->skip(6);
        $this->assertEquals(6, $this->input->tell());
    }

    public function testValid(): void
    {
        $this->assertTrue($this->input->valid());
        $this->input->skip(6);
        $this->assertTrue($this->input->valid());
        $this->input->skip(56);
        $this->assertFalse($this->input->valid());
    }

    public function testAssertValid(): void
    {
        $this->input->assertValid();
        $this->input->skip(6);
        $this->input->assertValid();
        $this->input->skip(56);
        $this->expectException(UnexpectedEndOfInputException::class);
        $this->input->assertValid();
    }

    public function testCheck(): void
    {
        $this->assertTrue($this->input->check("Lorem"));
        $this->assertFalse($this->input->check("lorem"));
        $this->assertFalse($this->input->check("ipsum"));
        $this->input->skip(6);
        $this->assertTrue($this->input->check("ipsum"));
        $this->assertFalse($this->input->check("Lorem"));
    }

    public function testCheckInsensitive(): void
    {
        $this->assertTrue($this->input->checkInsensitive("Lorem"));
        $this->assertTrue($this->input->checkInsensitive("lorem"));
        $this->assertFalse($this->input->checkInsensitive("ipsum"));
        $this->input->skip(6);
        $this->assertTrue($this->input->checkInsensitive("ipSuM"));
        $this->assertFalse($this->input->checkInsensitive("Lorem"));
    }

    public function testAssert(): void
    {
        $this->input->assert("Lorem");
        $this->input->skip(6);
        $this->input->assert("ipsum");
        $this->expectException(UnexpectedInputException::class);
        $this->input->assert("dolor");
    }

    public function testAssertInsensitive(): void
    {
        $this->input->assertInsensitive("Lorem");
        $this->input->assertInsensitive("lorem");
        $this->input->skip(6);
        $this->input->assertInsensitive("IPSUM");
        $this->expectException(UnexpectedInputException::class);
        $this->input->assertInsensitive("dolor");
    }

    public function testSkipWhitespace(): void
    {
        $input = new Input(" test   test /* test */   test //test");
        $input->skipWhitespace();
        $this->assertEquals(1, $input->tell());
        $input->skip(4);
        $input->skipWhitespace();
        $this->assertEquals(8, $input->tell());
        $input->skip(4);
        $input->skipWhitespace();
        $this->assertEquals(26, $input->tell());
        $input->skip(4);
        $input->skipWhitespace();
        $this->assertEquals(37, $input->tell());
    }

    public function testGetEncoding(): void
    {
        $input = new Input("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
        $this->assertEquals("UTF-8", $input->getEncoding());
        $input = new Input("Lorem ipsum dolor sit amet, consectetur adipiscing elit.", "ISO-8859-1");
        $this->assertEquals("ISO-8859-1", $input->getEncoding());
    }
}
