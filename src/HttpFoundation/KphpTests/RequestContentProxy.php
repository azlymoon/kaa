<?php

/*
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * This file was rewritten from the Symfony package
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation\KphpTests;

use Kaa\HttpFoundation\Request;

class RequestContentProxy extends Request
{
    public function getContent(bool $asResource = false): string
    {
        return http_build_query(['_method' => 'PUT', 'content' => 'mycontent'], '', '&');
    }
}