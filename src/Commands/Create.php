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
            $projectFolder = $this->createProjectPath($siteName);
            $repoConfig = $this->getRepoConfiguration();

//            if (mkdir($projectFolder)){
//
//                // boilerplate pullen via git
//                //
//            }

            $result = shell_exec("cd " . $this->devilboxSrc . $this->publicFolder . " && git clone " . $repoConfig['repo'] . ' ' . $siteName . '/htdocs' );

            if ( null === $result )
            {
                echo 'oha';
            }
            else {
                // datenbank erstellen
                // host file eintrag --> wichtig document-root einstellen auf /web
                    // version 1, pwd eingabe weil sudo rechte notwendig
                    // version 2, globale localhost-umleitung
                // .env für projekt erstellen
                // wp downloaden
                // wp installieren
                // info für user mit login-url
            }



            try {
                $this->saveSiteToConfig($siteName, $site);
            } catch (\Exception $e) {
                self::$climate->error('Meh... failed to update vvv-config.yml');
                self::$climate->error($e->getMessage());
            }
            $this->notifyOnSuccess($siteName);

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
}
