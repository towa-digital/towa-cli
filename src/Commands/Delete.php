<?php

namespace Towa\Setup\Commands;

use Towa\Setup\Command;
use Towa\Setup\Interfaces\CommandInterface;
use Towa\Setup\Utilities\YamlParser;

class Delete extends Command implements CommandInterface
{
    public $description = 'Delete some old shit';

    public function execute()
    {
        $siteName = $this->getSiteName();

        try {
            $this->deleteSiteFromConfig($siteName);
        } catch (\Exception $e) {
            $this->climate->error('failed to update vvv-config.yml');
            $this->climate->error($e->getMessage());
        }
    }

    private function deleteSiteFromConfig($siteName)
    {
        $config = YamlParser::readFile(get_config('path_config'));
        unset($config['sites'][$siteName]);
        YamlParser::writeFile($config, get_config('path_config'));
    }
}
