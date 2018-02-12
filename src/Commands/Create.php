<?php

namespace Towa\Setup\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Towa\Setup\Command;
use Towa\Setup\Interfaces\CommandInterface;
use Towa\Setup\Utilities\YamlParser;

class Create extends Command implements CommandInterface
{
    public function execute()
    {
        $siteName = $this->getSiteName();
        $site = $this->buildSite($siteName);

        try {
            $this->saveSiteToConfig($siteName, $site);
        } catch (\Exception $e) {
            self::$climate->error('Meh... failed to update vvv-config.yml');
            self::$climate->error($e->getMessage());
        }

        try {
            $this->provisionSite($siteName);
        } catch (ProcessFailedException $e) {
            self::$climate->error('Meh... failed to provision site');
            self::$climate->error($e->getMessage());
        }

        $this->notifyOnSuccess($siteName);

        return true;
    }

    private function saveSiteToConfig($siteName, $site)
    {
        $config = YamlParser::readFile(get_config('path_config'));
        $config['sites'][$siteName] = $site;
        YamlParser::writeFile($config, get_config('path_config'));
    }

    private function getRepoUrl()
    {
        return $this->question('Repo Url (ssh)? [<yellow>Boilerplate</yellow>]', true, get_config('boilerplate'));
    }

    private function getBranch()
    {
        $default = get_config('branch');

        return $this->question("Branch? [<yellow>{$default}</yellow>]", true, $default);
    }

    private function getPhpVersion()
    {
        $default = get_config('phpVersion');

        return $this->question("PHP Version? [<yellow>{$default}</yellow>]", true, $default);
    }

    private function buildSite($siteName)
    {
        return [
            'repo'           => $this->getRepoUrl(),
            'branch'         => $this->getBranch(),
            'nginx_upstream' => $this->getPhpVersion(),
            'hosts'          => [
                $siteName.'.test',
            ],
        ];
    }

    private function provisionSite($siteName)
    {
        $vvv = get_config('path');
        $provision = new Process("cd {$vvv} && vagrant provision --provision-with site-{$siteName}");

        $provision->setTimeout(0)->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }

    private function notifyOnSuccess($siteName)
    {
        self::$climate->info("Site: {$siteName}.dev");
        self::$climate->info('User: towa_admin');
        self::$climate->info('Password: dev');
    }
}
