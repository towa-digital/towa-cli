<?php

namespace Towa\Setup\Commands\Project;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Towa\Setup\Command;
use Towa\Setup\Interfaces\CommandInterface;
use Towa\Setup\Utilities\YamlParser;

class Delete extends Command implements CommandInterface
{
    public $description = 'Delete project';
    public $sites = [];

    public function execute()
    {
        $availableSites = array_keys(get_sites());

        if (empty($availableSites)) {
            return self::$climate->comment('There is no site set in your config file!');
        }

        $list = self::$climate->confirm('Choose sites from list?');

        if ($list->confirmed()) {
            $input = self::$climate->checkboxes('Select all sites you wish to delete', $availableSites);

            $this->sites = $input->prompt();
        } else {
            $this->sites = [$this->getSiteName()];
        }

        try {
            $this->deleteSites();
        } catch (\Exception $e) {
            self::$climate->error('failed to update vvv-config.yml');
            self::$climate->error($e->getMessage());
        }

        return true;
    }

    private function deleteSites()
    {
        $this->deleteSiteDb()
             ->deleteSiteFromConfig()
             ->deleteSiteNginxConfig()
             ->deleteSiteFiles();
    }

    private function deleteSiteFromConfig()
    {
        $config = YamlParser::readFile(get_config('path_config'));

        foreach ($this->sites as $site) {
            unset($config['sites'][$site]);
        }

        YamlParser::writeFile($config, get_config('path_config'));

        return $this;
    }

    private function deleteSiteNginxConfig()
    {
        $deleteNginxConfig = new Process($this->buildDeleteNginxCommand($this->sites));

        try {
            self::$climate->info('remove nginx configs...');

            $deleteNginxConfig->setTimeout(0)->run(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to delete dbs');
            self::$climate->error($e->getMessage());
        }

        return $this;
    }

    private function deleteSiteDb()
    {
        $deleteDb = new Process($this->buildSql($this->sites));

        try {
            self::$climate->info('clear dbs...');

            $deleteDb->setTimeout(0)->run(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to delete dbs');
            self::$climate->error($e->getMessage());
        }

        return $this;
    }

    private function deleteSiteFiles()
    {
        $vvv = get_config('path');

        foreach ($this->sites as $siteName) {
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
            self::$climate->info("delete {$path}...");

            $removeProcess->setTimeout(0)->run(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to delete the files');
            self::$climate->error($e->getMessage());
        }
    }

    private function buildDeleteNginxCommand($sites)
    {
        $vvv = get_config('path');
        $command = "cd {$vvv} && vagrant ssh --command \"cd /etc/nginx/custom-sites && sudo rm -rf";

        foreach ($sites as $siteName) {
            $command .= " *{$siteName}*";
        }

        $command .= '"';

        return $command;
    }
}
