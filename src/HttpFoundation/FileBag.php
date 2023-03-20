<?php

namespace Kaa\HttpFoundation;

use Kaa\HttpFoundation\File\UploadedFile;

/**
 * FileBag пока не трогаем, тут проблема с получением загреженных файлов. См. File.php
 *
 * FileBag is a container for uploaded files.
 */
class FileBag extends ParameterBag
{
    private const FILE_KEYS = ['error', 'name', 'size', 'tmp_name', 'type'];

    /**
     * @param mixed $parameters An array of HTTP files
     */
    public function __construct($parameters = [])
    {
        $this->replace($parameters);
    }

    /** @param mixed $files */
    public function replace($files = [])
    {
        $this->parameters = [];
        $this->add($files);
    }

    public function set(string $key, $value)
    {
//        if (!\is_array($value) && !$value instanceof UploadedFile) {
//            throw new \InvalidArgumentException('An uploaded file must be an array or an instance of UploadedFile.');
//        }

//        parent::set($key, $this->convertFileInformation($value));
    }

    /** @param mixed $files */
    public function add($files = [])
    {
        foreach ($files as $key => $file) {
            // $key is always string
            $key = (string)$key;
            $this->set($key, $file);
        }
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param UploadedFile[]|UploadedFile $file
     *
     * @return UploadedFile[]|UploadedFile|null
     */
    protected function convertFileInformation($file)
    {
//        if ($file instanceof UploadedFile) {
//            return $file;
//        }
//
//        $file = $this->fixPhpFilesArray($file);
//        $keys = array_keys($file);
//        sort($keys);
//
//        if (self::FILE_KEYS == $keys) {
//            if (\UPLOAD_ERR_NO_FILE == $file['error']) {
//                $file = null;
//            } else {
//                $file = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error'], false);
//            }
//        } else {
//            $file = array_map(function ($v) { return $v instanceof UploadedFile || \is_array($v) ? $this->convertFileInformation($v) : $v; }, $file);
//            if (array_keys($keys) === $keys) {
//                $file = array_filter($file);
//            }
//        }
//
        return $file;
    }
}
