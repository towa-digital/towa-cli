<?php

namespace Towa\Setup;

use FilesystemIterator;
use League\CLImate\CLImate;
use Towa\Setup\Utilities\YamlParser;

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
            YamlParser::edit($siteName, $site);
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
    }

    private function getSiteName()
    {
        return $this->question('<cyan>Seiten/Projekt Name?</cyan>', true);
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
        $count = iterator_count(
            new FilesystemIterator(__DIR__.'/../art', FilesystemIterator::SKIP_DOTS)
        );

        return 'towa'.rand(1, $count);
    }

    private function getAnimationDirection()
    {
        $directions = ['left', 'bottom', 'top'];
        return $directions[array_rand($directions, 1)];
    }
}
