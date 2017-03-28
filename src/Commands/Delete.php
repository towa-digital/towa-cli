<?php
/**
 * Created by TOWA.
 * User: dseidl
 * Date: 28/03/17
 */
namespace Towa\Setup\Commands;

use Towa\Command;
use Towa\ParseYaml;

class Delete extends Command
{
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
        $config = ParseYaml::readFile('/Users/dseidl/Code/vvv/vvv-config.yml');
        unset($config['sites'][$siteName]);
        ParseYaml::writeFile($config, '/Users/dseidl/Code/vvv/vvv-config-temp.yml');
    }
}
