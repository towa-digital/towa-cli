<?php


namespace Towa\Setup\Commands\Project;

use Dotenv\Dotenv;
use League\CLImate\CLImate;
use Towa\Setup\Command;
use Towa\Setup\Commands\Services\Database;
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

        // get devilbox-env-configuration for further use
        $devilbox_env = new Dotenv($this->devilbox_path);
        if (file_exists($this->devilbox_path . '/.env')) {
            $devilbox_env->load();
        } else {
            throw new \RuntimeException('Could not find .env-file for devilbox. Looked at: ' . $this->devilbox_path);
        }

        $projects = $this->find_projects();
        $databases = collect($this->find_databases());

        $data = collect($projects)
            ->map(function ($item) use ($databases) {
                return [
                    'project' => $item,
                    'has_database' => $databases->contains($item) ? 'yes' : 'no',
                ];
            })
            ->values()
            ->toArray();

        // show table with project, path and hasDatabase?
        $this->climate->table($data);
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
            scandir($this->devilbox_path . $this->devilbox_www_path, SCANDIR_SORT_ASCENDING), ['.', '..']
        );
    }

    private function find_databases()
    {
        $db = new Database($this->climate);
        $db->set_password('');
        $db->set_port(env('HOST_PORT_MYSQL'));

        return $db->all_databases();
    }
}