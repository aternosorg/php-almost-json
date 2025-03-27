<?php

namespace Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

class VanillaJsonTest extends AlmostJsonTestCase
{
    public static function jsonProvider(): Generator
    {
        foreach (scandir(__DIR__ . "/data/json") as $file) {
            if (str_ends_with($file, ".json")) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                yield $filename => ["json" => file_get_contents(__DIR__ . "/data/json/" . $file)];
            }
        }
    }

    #[DataProvider("jsonProvider")]
    public function testRegularJson(string $json): void
    {
        $this->assertEquals(
            json_encode(json_decode($json)),
            json_encode($this->parser->parseString($json))
        );

        $this->assertEquals(
            json_encode(json_decode($json, true)),
            json_encode($this->parser->parseString($json, true))
        );
    }
}
