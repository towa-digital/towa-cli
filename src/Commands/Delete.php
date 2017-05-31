<?php

namespace Towa\Setup\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
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
            $this->deleteSites($sites);
        } catch (\Exception $e) {
            self::$climate->error('failed to update vvv-config.yml');
            self::$climate->error($e->getMessage());
        }
    }

    private function deleteSites($sites)
    {
        $this->deleteSiteDb($sites)
             ->deleteSiteFromConfig($sites)
             ->deleteSiteFiles($sites);
    }

    private function deleteSiteFromConfig($sites)
    {
        $config = YamlParser::readFile(get_config('path_config'));

        foreach ($sites as $site) {
            unset($config['sites'][$site]);
        }

        YamlParser::writeFile($config, get_config('path_config'));

        return $this;
    }

    private function deleteSiteDb($sites)
    {
        $deleteDb = new Process($this->buildSql($sites));

        try {
            self::$climate->info('clear dbs');

            $deleteDb->setTimeout(0)->run(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to delete dbs');
            self::$climate->error($e->getMessage());
        }

        return $this;
    }

    private function deleteSiteFiles($sites)
    {
        $vvv = get_config('path');

        foreach ($sites as $siteName) {
            $this->removeDirectory($vvv.'/www/'.$siteName);
        }

        return $this;
    }

    private function buildSql($sites)
    {
        $vvv = get_config('path');
        $sql = "cd {$vvv} && vagrant ssh --command \"mysql -u root -e '";

        foreach ($sites as $siteName) {
            $sql .= "DROP DATABASE IF EXISTS {$siteName}; ";
        }

        $sql .= "'\"";

        return $sql;
    }

    private function removeDirectory($path)
    {
        $removeProcess = new Process("rm -rf {$path}");

        try {
            self::$climate->info("delete {$path}");

            $removeProcess->setTimeout(0)->run(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to delete the files');
            self::$climate->error($e->getMessage());
        }
    }
}
