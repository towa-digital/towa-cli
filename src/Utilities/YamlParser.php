<?php

namespace Towa\Setup\Utilities;

use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    public static function edit($siteName, $site)
    {
        $config = self::readFile('/Users/dseidl/Code/vvv/vvv-config.yml');
        $config['sites'][$siteName] = $site;
        self::writeFile($config, '/Users/dseidl/Code/vvv/vvv-config-temp.yml');
    }

    public static function get(string $file, string $key) {
        $data = Yaml::parse(file_get_contents($file));

        return array_key_exists($key, $data)
            ? (count($data[$key]) > 1 ? $data[$key] : $data[$key][0])
            : null;
    }

    private function readFile($file)
    {
        return Yaml::parse(file_get_contents($file));
    }

    private function writeFile($data, $file)
    {
        // inline is number of depth to parse yaml
        return file_put_contents($file, Yaml::dump($data, 5));
    }
}
