<?php

namespace Aternos\PhpAlmostJson\Node;

abstract class AlmostJsonNode implements AlmostJsonNodeInterface
{
    protected const NUMBERS = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    protected const HEX = ["a", "b", "c", "d", "e", "f", "A", "B", "C", "D", "E", "F", ...self::NUMBERS];
    protected const COMMA = ",";
}
