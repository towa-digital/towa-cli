<?php
namespace Towa\Setup\Commands;

use Towa\Setup\Command;
use Towa\Setup\Utilities\YamlParser;

class NewProject extends Command
{
    public function execute()
    {
        $siteName = $this->getSiteName();
        $site = $this->buildSite($siteName);

        try {
            $this->saveSiteToConfig($siteName, $site);
        } catch (\Exception $e) {
            $this->climate->error('failed to update vvv-config.yml');
            $this->climate->error($e->getMessage());
        }
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
        $repo = $this->getRepoUrl();
        $branch = $this->getBranch();
        $phpVersion = $this->getPhpVersion();
        $host = [$siteName.'.dev'];

        return [
            'repo' => $repo,
            'branch' => $branch,
            'nginx_upstream' => $phpVersion,
            'hosts' => $host,
        ];
    }
}
