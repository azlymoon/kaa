<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpKernel;

use Exception;
use Kaa\HttpFoundation\Response;
use Kaa\HttpFoundation\Request;

/**
 * HttpKernelInterface handles a Request to convert it to a Response.
 */
interface HttpKernelInterface
{
    public const MAIN_REQUEST = 1;
    public const SUB_REQUEST = 2;

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     *
     * @throws Exception When an Exception occurs during processing
     */
    public function handle(Request $request): Response;
}
