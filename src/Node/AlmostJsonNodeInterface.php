<?php

namespace Aternos\PhpAlmostJson\Node;

use Aternos\PhpAlmostJson\Exception\AlmostJsonException;
use Aternos\PhpAlmostJson\Input;
use Aternos\PhpAlmostJson\AlmostJsonParser;
use stdClass;

interface AlmostJsonNodeInterface
{
    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @return bool
     */
    public static function detect(Input $input, AlmostJsonParser $parser): bool;

    /**
     * @param Input $input
     * @param AlmostJsonParser $parser
     * @param int $depth
     * @return void
     * @throws AlmostJsonException
     */
    public function read(Input $input, AlmostJsonParser $parser, int $depth = 0): void;

    /**
     * @param bool $assoc
     * @return stdClass|array|string|int|float|bool|null
     */
    public function toNative(bool $assoc = false): stdClass|array|string|int|float|bool|null;
}
