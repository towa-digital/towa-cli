<?php

namespace Towa\Setup\Commands;

use League\CLImate\CLImate;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Towa\Setup\Command;
use Towa\Setup\Interfaces\CommandInterface;
use Towa\Setup\Utilities\YamlParser;

class Create extends Command implements CommandInterface
{
    private $devilboxSrc;
    private $publicFolder;
    private $projectFolder;

    public function __construct(CLImate $climate)
    {
        parent::__construct($climate);
        $this->publicFolder = '/data/www/';
    }

    public function softCheckDevilbox()
    {
        return ($a = file_exists($this->devilboxSrc . '/docker-compose.yml'));
    }

    public function execute()
    {

        $this->devilboxSrc = $this->getDevilboxPath();

        if (!$this->softCheckDevilbox()) {
            echo "Installier doch zersch mol die Devilbox, Junge -> git clone https://github.com/cytopia/devilbox";

        } else {
            $siteName = $this->getSiteName();
            $this->projectFolder = $this->createProjectPath($siteName);
            $repoConfig = $this->getRepoConfiguration();

            exec("cd " . $this->devilboxSrc . $this->publicFolder . " && git clone " . $repoConfig['repo'] . ' ' . $siteName . '/htdocs' , $output, $status);

            if ( 0 !== $status )
            {
                echo 'failed cloning repository';
                die();
            }
            else {
                // datenbank erstellen
                $this->createDatabase($siteName);
            }

            $this->createProjectEnvFile();
            $this->installComposerDependencies();
            $this->installWordPress();
            /*$this->addHostsEntry();
            $this->showSiteInfo();*/

            // $this->notifyOnSuccess($siteName);

            return true;
        }

    }

    private function saveSiteToConfig($siteName, $site)
    {
        $config = YamlParser::readFile(get_config('path_config'));
        $config['sites'][$siteName] = $site;
        YamlParser::writeFile($config, get_config('path_config'));
    }

    private function getRepoConfiguration()
    {
        return [
            'repo' => $this->getRepoUrl(),
            'branch' => $this->getBranch(),
        ];
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

    private function notifyOnSuccess($siteName)
    {
        self::$climate->info("Site: {$siteName}.test");
        self::$climate->info('User: towa_admin');
        self::$climate->info('Password: dev');
    }

    private function getDevilboxPath()
    {
        return $this->question('<cyan>Devilbox Installation Dir?</cyan>', true);
    }

    private function createProjectPath($siteName)
    {
        return $this->devilboxSrc . $this->publicFolder . $siteName;
    }

    private function createDatabase($siteName)
    {
        $command = 'echo "create database ' . $siteName . '" | mysql -u root -h 127.0.0.1';
        exec($command, $output, $status);

        if ( 0 !== $status ) {
            echo 'failed creating database';
            die();
        }
    }

    private function createProjectEnvFile()
    {
        $command = "cd " . $this->projectFolder . "/htdocs && cp .env.example .env ";

        exec($command , $output, $status);

        if ( 0 !== $status )
        {
            echo 'failed creating .env file';

        } else {
            // TODO: set necessary env-variables automatically.
            // $info = explode( PHP_EOL, file_get_contents( $this->projectFolder . '/htdocs/.env' ) );
            if ( true === $this->setEnvVariables())
            {
                echo '.env-file created';
            } else {
                exit();
            }
        }
    }

    private function installComposerDependencies()
    {
        exec("cd " . $this->projectFolder . "/htdocs && composer install" , $output, $status);

        if ( 0 !== $status )
        {
            echo 'failed composer install';
            die();
        } else {
            echo 'composer dependencies installed';
        }
    }

    private function installWordPress()
    {
        //--url=a13.test --title=Test --admin_user=towa_admin --admin_password=dev --admin_email=ranko.spegar@towa.com
        $command = 'echo "wp core install"';
        exec($command, $output, $status);
    }

    private function setEnvVariables()
    {
        $path = $this->projectFolder . '/htdocs/.env';
        $info = explode( PHP_EOL, file_get_contents( $path ) );
        $env = [];
        $orgEnv = [];

        //collect($info)->map()

        foreach ( $info as $row ){
            if ( empty($row) or false !== strpos( $row, '#')){
                continue;
            }

            $kv = explode( '=', $row );
            $orgEnv[$kv[0]] = $kv[1];
        }

        $env['DB_NAME'] = 'a20';
        $env['DB_USER'] = 'root';
        $env['DB_PASSWORD'] = '';
        $env['WP_ENV'] = 'development';
        $env['DB_HOME'] = 'a20.loc';

        $newEnv = array_merge( $orgEnv, $env );

        $result = file_put_contents($path, $newEnv);

        if ( false !== $result )
        {

            return true;
        }
    }
}
