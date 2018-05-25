<?php

namespace Towa\Setup\Utilities;

class Config
{
    /** @var string */
    private $path;

    protected static $config = [];

    public function __construct()
    {
        $this->path = \dirname(__FILE__, 3) . '/config/.towa-config';

        $this->initialize();
    }

    public function initialize()
    {
        self::$config = YamlParser::readFile($this->path);
    }

    public static function set(string $key, $value)
    {
        self::$config[$key] = $value;
    }

    public static function get(string $key = null)
    {
        return $key ? self::$config[$key] : self::$config;
    }
}
