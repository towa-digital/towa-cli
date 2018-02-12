<?php

namespace Towa\Setup\Utilities;

use Towa\Setup\Command;

class Config
{
    /** @var string */
    private $path;

    protected static $config = [];

    public function __construct()
    {
        $this->path = getenv('HOME').'/.towa-config';

        $this->initialize();
    }

    public function initialize()
    {
        $this->createConfigFileIfItDoesntExist();

        self::$config = YamlParser::readFile($this->path);
        Command::log('Configurations successfully loaded!');
    }

    public function createConfigFileIfItDoesntExist()
    {
        if (file_exists($this->path)) {
            return;
        }

        Command::log('Hold up! Creating a new config file');

        copy(__DIR__.'/../../config/.towa-config', $this->path);

        $config = YamlParser::readFile($this->path);
        $config['path'] = getenv('HOME').'/vvv';
        $config['path_config'] = getenv('HOME').'/vvv/vvv-config.yml';

        YamlParser::writeFile($config, $this->path);
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
