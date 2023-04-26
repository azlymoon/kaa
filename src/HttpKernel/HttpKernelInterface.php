<?php


namespace Kaa\HttpKernel;

use Exception;
use Kaa\HttpFoundation\Request;
use Kaa\HttpFoundation\Response;

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
     * @param int  $type  The type of the request
     *                    (one of HttpKernelInterface::MAIN_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool $catch Whether to catch exceptions or not
     *
     * @throws Exception When an Exception occurs during processing
     */
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response;
}
