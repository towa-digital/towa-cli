<?php
namespace Towa\Setup\Utilities;

use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    public static function get(string $file, string $key)
    {
        $data = Yaml::parse(file_get_contents($file));

        return array_key_exists($key, $data)
            ? $data[$key]
            : null;
    }

    public static function readFile($file)
    {
        return Yaml::parse(file_get_contents($file));
    }

    public static function writeFile($data, $file)
    {
        // inline is number of depth to parse yaml
        return file_put_contents($file, Yaml::dump($data, 5));
    }
}
