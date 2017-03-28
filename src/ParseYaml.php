<?php

namespace Towa;

use Symfony\Component\Yaml\Yaml;

class ParseYaml
{
    public static function edit($siteName, $site)
    {
        $config = self::readFile('/Users/dseidl/Code/vvv/vvv-config.yml');
        $config['sites'][$siteName] = $site;
        self::writeFile($config, '/Users/dseidl/Code/vvv/vvv-config-temp.yml');
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
