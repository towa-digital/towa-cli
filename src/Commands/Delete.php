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
        $availableSites = array_keys(get_sites());

        if (empty($availableSites)) {
            return self::$climate->comment('There is no site set in your config file!');
        }

        $list = self::$climate->confirm('Choose sites from list?');

        if ($list->confirmed()) {
            $input = self::$climate->checkboxes('Select all sites you wish to delete', $availableSites);

            $sites = $input->prompt();
        } else {
            $sites = [$this->getSiteName()];
        }

        try {
            $this->deleteSiteFromConfig($sites);
        } catch (\Exception $e) {
            self::$climate->error('failed to update vvv-config.yml');
            self::$climate->error($e->getMessage());
        }
    }

    private function deleteSiteFromConfig($sites)
    {
        $config = YamlParser::readFile(get_config('path_config'));

        foreach ($sites as $site) {
            unset($config['sites'][$site]);
        }

        YamlParser::writeFile($config, get_config('path_config'));
    }
}
