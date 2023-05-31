<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * FileBag пока не трогаем, тут проблема с получением загреженных файлов. См. File.php
 *
 * FileBag is a container for uploaded files.
 */
class FileBag extends ParameterBag
{
    /**
     * @param string[] $parameters An array of HTTP files
     */
    public function __construct($parameters = [])
    {
        parent::__construct($parameters);
        $this->replace($parameters);
    }

    /** @param string[] $files */
    final public function replace($files = []): void
    {
        # Just called the parent methods with empty parameters,these parameters in the FileBag class we name differently
        parent::replace();

        $this->add($files);
    }

    /** @param string[] $files */
    final public function add($files = []): void
    {
        # Just called the parent methods with empty parameters,these parameters in the FileBag class we name differently
        parent::add();

        foreach ($files as $key => $file) {
            // $key is always string
            $key = (string)$key;
            $this->set($key, $file);
        }
    }
}
