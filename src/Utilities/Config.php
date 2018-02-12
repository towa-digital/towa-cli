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
        $this->path = getenv('HOME').'/vvv';
        $this->configPath = $this->path.'/vvv-config.yml';

        $this->initialize();
    }

    public function initialize()
    {
        $this->createConfigFileIfItDoesntExist();

        self::$config = YamlParser::readFile($this->configPath);
        Command::log('Configurations successfully loaded!');
    }

    public function createConfigFileIfItDoesntExist()
    {
        if (file_exists($this->configPath)) {
            return;
        }

        Command::log('Hold up! Creating a new config file');

        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        copy(__DIR__.'/../../config/vvv-config.yml', $this->configPath);

        $config = YamlParser::readFile($this->configPath);
        $config['path'] = getenv('HOME').'/vvv';
        $config['path_config'] = getenv('HOME').'/vvv/vvv-config.yml';

        YamlParser::writeFile($config, $this->configPath);
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
