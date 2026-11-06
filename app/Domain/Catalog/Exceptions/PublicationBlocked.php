<?php

namespace App\Domain\Catalog\Exceptions;

use RuntimeException;

class PublicationBlocked extends RuntimeException
{
    public function __construct(public readonly array $blockers)
    {
        parent::__construct('Publication blocked: '.implode(', ', $blockers));
    }
}
