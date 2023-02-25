<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Attribute;

use Attribute;

/**
 * Классы и функции, помеченные этим аттрибутом, не могут быть скомпилированы с помощью KPHP
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final class PhpOnly
{
}
