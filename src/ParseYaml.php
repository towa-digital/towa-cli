<?php

namespace Towa\Setup;

use Symfony\Component\Yaml\Yaml;

class ParseYaml
{
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
