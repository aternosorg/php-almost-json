<?php

namespace Tests;

use Aternos\AlmostJson\Exception\UnexpectedEndOfInputException;
use Aternos\AlmostJson\Exception\UnexpectedInputException;
use Aternos\AlmostJson\Input;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class InputTest extends AlmostJsonTestCase
{
    protected const WHITESPACE = [
        "\t", "\n", "\v",
        "\f", "\r", " ",
        "\xA0", "\u{2028}",
        "\u{2029}", "\u{FEFF}",

        // Zs Unicode category
        "\u{0020}", "\u{00A0}",
        "\u{1680}", "\u{2000}",
        "\u{2001}", "\u{2002}",
        "\u{2003}", "\u{2004}",
        "\u{2005}", "\u{2006}",
        "\u{2007}", "\u{2008}",
        "\u{2009}", "\u{200A}",
        "\u{202F}", "\u{205F}",
        "\u{3000}"
    ];

    public static function provideWhitespace(): Generator
    {
        foreach (self::WHITESPACE as $whitespace) {
            yield [$whitespace];
        }
    }

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

    #[DataProvider("provideWhitespace")]
    public function testSkipWhitespaceCharacter(string $char): void
    {
        $input = new Input($char . "test" . $char);
        $input->skipWhitespace();
        $this->assertEquals(1, $input->tell());
        $input->skip(4);
        $input->skipWhitespace();
        $this->assertEquals(6, $input->tell());
    }

    public function testGetEncoding(): void
    {
        $input = new Input("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
        $this->assertEquals("UTF-8", $input->getEncoding());
        $input = new Input("Lorem ipsum dolor sit amet, consectetur adipiscing elit.", "ISO-8859-1");
        $this->assertEquals("ISO-8859-1", $input->getEncoding());
    }

    public function testRewind(): void
    {
        $this->input->skip(6);
        $this->assertEquals(6, $this->input->tell());
        $this->input->rewind();
        $this->assertEquals(0, $this->input->tell());
    }

    public function testSeek(): void
    {
        $this->input->skip(6);
        $this->assertEquals(6, $this->input->tell());
        $this->input->seek(0);
        $this->assertEquals(0, $this->input->tell());
        $this->input->seek(6);
        $this->assertEquals(6, $this->input->tell());
    }
}
