<?php

declare(strict_types=1);

namespace Kaa\CodeGen;

use JsonException;

class EnvReader
{
    /**
     * @return mixed
     * @throws JsonException
     */
    public static function readEnv(string $kernelDir): mixed
    {
        #ifndef KPHP
        $content = file_get_contents($kernelDir . DIRECTORY_SEPARATOR . 'env.json');
        if ($content === false) {
            return [];
        }

        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        #endif

        /** @noinspection PhpUnreachableStatementInspection */
        /** @phpstan-ignore-next-line */
        return kphp_get_runtime_config();
    }
}
