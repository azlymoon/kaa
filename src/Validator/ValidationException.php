<?php

declare(strict_types=1);

namespace Kaa\Validator;

use Exception;

class ValidationException extends Exception
{
    /**
     * @var \Kaa\Validator\Violation[]
     */
    private array $violationList;

    /**
     * @param \Kaa\Validator\Violation[] $violationList
     */
    public function __construct(array $violationList)
    {
        parent::__construct();
        $this->violationList = $violationList;
    }

    /**
     * @return \Kaa\Validator\Violation[]
     */
    public function getViolationList(): array
    {
        return $this->violationList;
    }
}
