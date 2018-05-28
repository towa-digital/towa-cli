<?php

namespace Towa\Setup\Commands\Project;

use Dotenv\Dotenv;
use League\CLImate\CLImate;
use Towa\Setup\Command;
use Towa\Setup\Commands\Services\Database;
use Towa\Setup\Commands\Services\Repository;
use Towa\Setup\Interfaces\CommandInterface;

class Create extends Command implements CommandInterface
{
    private $devilbox_path;
    private $devilbox_www_path;
    private $devilbox_project_folder;
    private $boilerplate_url;

    public function __construct(CLImate $climate)
    {
        parent::__construct($climate);
        $this->devilbox_www_path = 'data/www/';
        $this->boilerplate_url = 'git@bitbucket.org:towa_gmbh/towa-workflow-boilerplate.git';
    }

    public function isDevilboxPresent()
    {
        return file_exists($this->devilbox_path . '/docker-compose.yml');
    }

    /**
     * @throws \RuntimeException if defined tasks can't be processed
     * @return bool
     */
    public function execute(): bool
    {
        $this->devilbox_path = $this->getDevilboxPath();

        if (!$this->isDevilboxPresent()) {
            throw new \RuntimeException('Devilbox is not installed. Please install first: https://github.com/cytopia/devilbox');
        }

        // get devilbox-env-configuration for further use
        $devilbox_env = new Dotenv($this->devilbox_path);
        if (file_exists($this->devilbox_path . '/.env')) {
            $devilbox_env->load();
        } else {
            throw new \RuntimeException('Could not find .env-file for devilbox. Looked at: ' . $this->devilbox_path);
        }

        $site_name = $this->getSiteName();
        $this->set_project_path($site_name);

        $repository = $this->getRepoUrl();
        $projectPath = $this->devilbox_project_folder . '/htdocs';
        (new Repository($this->climate))->pull($repository, $projectPath);

        if ($this->create_database()) {
            $this->climate->info('Using devilbox-configuration for database setup.');
            $db = new Database($this->climate);
            $db->set_port(env('HOST_PORT_MYSQL'));
            $db->create($site_name);
        }

        $this->show_project_summary();

        return true;
    }

    private function getDevilboxPath()
    {
        $user = get_current_user();
        $os_user_home = $this->determine_os_user_home_dir();

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

    private function getSiteName()
    {
        return $this->question('Project Name?', true);
    }

    private function getRepoUrl()
    {
        return $this->question(
            'Repository? [<yellow>Boilerplate</yellow>]',
            true,
            $this->boilerplate_url
        );
    }

    private function set_project_path($site_name)
    {
        $this->devilbox_project_folder = $this->devilbox_path . $this->devilbox_www_path . $site_name;
    }

    private function determine_os_user_home_dir()
    {
        $os = [
            'mac' => '/Users',
            'linux' => '/home',
            'winnt' => '/Users',
        ];

        return $os[strtolower(PHP_OS)] ?? '';
    }

    private function create_database()
    {
        return $this->climate->confirm('Create Database-Schema?')->confirmed();
    }

    private function show_project_summary()
    {
        $info = [
            'Project Url' => 'test.local',
            'Project Path' => $this->devilbox_project_folder,
            'Database Name' => false,
        ];

        $this->climate->br();

        $padding = $this->climate->padding('15', '.');
        foreach ($info as $label => $value)
        {
            if ($value){
                $this->climate->tab();
                $padding->label($label)->result($value);
            }
        }

        $this->climate->br();
    }
}
