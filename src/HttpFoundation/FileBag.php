<?php

namespace Kaa\HttpFoundation;

/**
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
