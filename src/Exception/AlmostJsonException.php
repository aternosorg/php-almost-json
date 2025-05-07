<?php

namespace Aternos\AlmostJson\Exception;

use Exception;
use Throwable;

class AlmostJsonException extends Exception
{
    /**
     * @param string $message
     * @param int $offset
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        protected int $offset,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message . " at offset " . $this->offset, previous: $previous);
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
