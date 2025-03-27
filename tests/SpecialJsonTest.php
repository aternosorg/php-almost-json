<?php

namespace Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

class SpecialJsonTest extends AlmostJsonTestCase
{
    public static function jsonProvider(): Generator
    {
        foreach (scandir(__DIR__ . "/data/special") as $file) {
            if (str_ends_with($file, ".ajson")) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $content = file_get_contents(__DIR__ . "/data/special/" . $file);
                if (!preg_match("#-----EXPECTED-----\n([\s\S]*)\n-----EXPECTED-----#", $content, $matches)) {
                    throw new RuntimeException("Invalid test file format: " . $file);
                }
                $expected = $matches[1];

                yield $filename => [
                    "json" => $content,
                    "expected" => $expected
                ];
            }
        }
    }

    #[DataProvider("jsonProvider")]
    public function testRegularJson(string $json, string $expected): void
    {
        $this->assertEquals(
            json_encode(json_decode($expected)),
            $this->encode($this->parser->parseString($json))
        );

        $this->assertEquals(
            json_encode(json_decode($expected)),
            $this->encode($this->parser->parseString($json, true))
        );
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function encode(mixed $value): string
    {
        return json_encode($this->replaceNanAndInf($value));
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    protected function replaceNanAndInf(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map($this->replaceNanAndInf(...), $input);
        }
        if (is_object($input)) {
            foreach ($input as $key => $value) {
                $input->{$key} = $this->replaceNanAndInf($value);
            }
            return $input;
        }
        if (is_float($input)) {
            if (is_nan($input)) {
                return "NaN";
            }
            if (is_infinite($input)) {
                return "Infinity";
            }
        }
        return $input;
    }
}
