<?php


namespace Towa\Setup\Commands\Project;

use League\CLImate\CLImate;
use Towa\Setup\Command;
use Towa\Setup\Interfaces\CommandInterface;

class Show extends Command implements CommandInterface
{
    private $devilbox_path;
    private $devilbox_www_path;

    public function __construct(CLImate $climate)
    {
        parent::__construct($climate);
        $this->devilbox_www_path = 'data/www/';
    }

    public function execute()
    {
        $this->devilbox_path = $this->getDevilboxPath();

        if (!$this->isDevilboxPresent()) {
            throw new \RuntimeException('Devilbox is not installed. Please install first: https://github.com/cytopia/devilbox');
        }

        $projects = $this->find_projects();

        // show table with project, path and hasDatabase?
        foreach ($projects as $project )
        {
            $this->climate->info($project);
        }
    }

    private function isDevilboxPresent()
    {
        return file_exists($this->devilbox_path . '/docker-compose.yml');
    }

    private function getDevilboxPath()
    {
        $user = get_current_user();
        $os_user_home = determine_os_user_home_dir();

        $devilbox_default_path = sprintf(
            '%1$s/%2$s/Devilbox/',
            $os_user_home,
            $user
        );

        $path = $this->question(
            "Devilbox Installation Directory? [<yellow>$devilbox_default_path</yellow>]",
            false,
            $devilbox_default_path
        );

        return rtrim($path, '/') . '/';
    }

    private function find_projects()
    {
        return array_diff(
            scandir( $this->devilbox_path . $this->devilbox_www_path, SCANDIR_SORT_ASCENDING ), [ '.', '..' ]
        );
    }
}