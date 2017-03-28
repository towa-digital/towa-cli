<?php

namespace Towa\Setup\Utilities;

class ConfigParser
{
    /** @var string */
    private $path;

    public function __construct()
    {
        $this->path = getenv('HOME').'/.towa-config';

        $this->createConfigFileIfItDoesntExist();
    }

    public function createConfigFileIfItDoesntExist()
    {
        if (! is_file($this->path)) {
            copy(__DIR__.'/../../config/.towa-config', $this->path);
        }

        return;
    }

    public function get(string $key)
    {
        return YamlParser::get($this->path, $key);
    }
}