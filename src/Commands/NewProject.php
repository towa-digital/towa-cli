<?php
namespace Towa\Setup\Commands;

use Towa\Command;
use Towa\ParseYaml;

/**
 * Created by TOWA.
 * User: dseidl
 * Date: 28/03/17
 */
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
        $config = ParseYaml::readFile('/Users/dseidl/Code/vvv/vvv-config.yml');
        $config['sites'][$siteName] = $site;
        ParseYaml::writeFile($config, '/Users/dseidl/Code/vvv/vvv-config-temp.yml');
    }

    private function getRepoUrl()
    {
        return $this->question('Repo Url (ssh)? [<yellow>Boilerplate</yellow>]', true, 'git@bitbucket.org:towa_gmbh/towa-workflow-boilerplate.git');
    }

    private function getBranch()
    {
        return $this->question('Branch? [<yellow>master</yellow>]', true, 'master');
    }

    private function getPhpVersion()
    {
        return $this->question('PHP Version? [<yellow>php71</yellow>]', true, 'php71');
    }

    private function buildSite($siteName)
    {
        $repo = $this->getRepoUrl();
        $branch = $this->getBranch();
        $phpVersion = $this->getPhpVersion();
        $host = [
            $siteName . '.dev'
        ];

        return [
            'repo' => $repo,
            'branch' => $branch,
            'nginx_upstream' => $phpVersion,
            'hosts' => $host,
        ];
    }
}
