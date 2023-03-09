<?php

declare(strict_types=1);

namespace Kaa\Validator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Strategy\AssertGeneratorInterface;
use Kaa\Validator\Strategy\BlankGenerator;
use Kaa\Validator\Strategy\EmailGenerator;
use Kaa\Validator\Strategy\GreaterThanGenerator;
use Kaa\Validator\Strategy\GreaterThanOrEqualGenerator;
use Kaa\Validator\Strategy\IsFalseGenerator;
use Kaa\Validator\Strategy\IsTrueGenerator;
use Kaa\Validator\Strategy\LessThanGenerator;
use Kaa\Validator\Strategy\LessThanOrEqualGenerator;
use Kaa\Validator\Strategy\MaxGenerator;
use Kaa\Validator\Strategy\MinGenerator;
use Kaa\Validator\Strategy\NegativeGenerator;
use Kaa\Validator\Strategy\NegativeOrZeroGenerator;
use Kaa\Validator\Strategy\NotBlankGenerator;
use Kaa\Validator\Strategy\NotNullGenerator;
use Kaa\Validator\Strategy\PositiveGenerator;
use Kaa\Validator\Strategy\PositiveOrZeroGenerator;
use Kaa\Validator\Strategy\TypeGenerator;

#[PhpOnly]
class GeneratorContext
{
    /** @var AssertGeneratorInterface[] */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new MinGenerator(),
            new MaxGenerator(),
            new NotBlankGenerator(),
            new BlankGenerator(),
            new NotNullGenerator(),
            new IsTrueGenerator(),
            new IsFalseGenerator(),
            new TypeGenerator(),
            new GreaterThanGenerator(),
            new GreaterThanOrEqualGenerator(),
            new LessThanGenerator(),
            new LessThanOrEqualGenerator(),
            new PositiveGenerator(),
            new PositiveOrZeroGenerator(),
            new NegativeGenerator(),
            new NegativeOrZeroGenerator(),
            new EmailGenerator(),
        ];
    }

    public function getStrategy(Assert $assert): ?AssertGeneratorInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($assert)) {
                return $strategy;
            }
        }

        return null;
    }
}
