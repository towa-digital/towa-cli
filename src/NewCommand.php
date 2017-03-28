<?php
namespace Towa;

use FilesystemIterator;
use League\CLImate\CLImate;

/**
 * Created by TOWA.
 * User: dseidl
 * Date: 28/03/17
 */
class NewCommand
{
    /* @var CLImate */
    private $climate;

    /**
     * NewCommand constructor.
     *
     * @param $climate CLImate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function run()
    {
        $this->climate->style->addColor('towa', 136);
        $this->drawTowa();
        $siteName = $this->getSiteName();
        $site = $this->buildSite($siteName);

        try {
            ParseYaml::edit($siteName, $site);
        } catch (\Exception $e) {
            $this->climate->error('failed to update vvv-config.yml');
            $this->climate->error($e->getMessage());
        }

        $this->climate->info('Done!');
    }

    private function drawTowa()
    {
        $this->climate->towa()->addArt(__DIR__ . '/../art');
        $this->climate->animation($this->getArt())->enterFrom($this->getAnimationDirection());
//        $this->climate->draw('towa');
    }

    private function getSiteName()
    {
        return $this->question('<cyan>Seiten/Projekt Name?</cyan>', true);
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

    private function question($question, $required = false, $default = '')
    {
        $input = $this->climate->input($question);

        if ($required) {
            $input->accept(function ($response) {
                return !empty($response);
            });
        }

        if ($default) {
            $input->defaultTo($default);
        }

        return $input->prompt();
    }

    private function getArt()
    {
        $count = iterator_count(new FilesystemIterator(__DIR__ . '/../art', FilesystemIterator::SKIP_DOTS));
        dump($count);

        return 'towa' . rand(1, $count);
    }

    private function getAnimationDirection()
    {
        $directions = ['left', 'bottom', 'top'];
        return $directions[array_rand($directions, 1)];
    }
}
