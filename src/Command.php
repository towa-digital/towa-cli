<?php
namespace Towa\Setup;

use FilesystemIterator;
use League\CLImate\CLImate;
use Symfony\Component\Finder\Finder;
use Towa\Setup\Commands\Delete;
use Towa\Setup\Commands\NewProject;
use Towa\Setup\Utilities\ConfigParser;
use Towa\Setup\Utilities\YamlParser;

class Command
{
    /* @var CLImate */
    public static $climate;

    /**
     * Command constructor.
     *
     * @param $climate CLImate
     */
    public function __construct(CLImate $climate)
    {
        self::$climate = $climate;
    }

    public function run()
    {
        $this->drawTowa();

        (new ConfigParser());

        $this->decideWhatToExecute();

        // start provisioning
        self::$climate->info('Done!');
    }

    private function drawTowa()
    {
        self::$climate->towa()->addArt(__DIR__ . '/../art');
        self::$climate->animation($this->getArt())->enterFrom($this->getAnimationDirection());
    }

    protected function decideWhatToExecute()
    {
        $input = self::$climate->radio('Whatcha wanna do?', $this->getOptions());
        $class = $input->prompt();

        return (new $class(self::$climate))->execute();
    }

    protected function question($question, $required = false, $default = '')
    {
        $input = self::$climate->input($question);

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

    protected function getSiteName()
    {
        return $this->question('<cyan>Seiten/Projekt Name?</cyan>', true);
    }

    private function getArt()
    {
        $count = iterator_count(
            new FilesystemIterator(__DIR__.'/../art', FilesystemIterator::SKIP_DOTS)
        );

        return 'towa' . rand(1, $count);
    }

    private function getAnimationDirection()
    {
        $directions = ['bottom', 'top'];
        return $directions[array_rand($directions, 1)];
    }

    public static function log(string $message)
    {
        return self::$climate->comment($message);
    }

    private function getOptions()
    {
        $options = [];

        $finder = new Finder;
        $finder->files()->name('*.php')->in(__DIR__.'/Commands');

        foreach ($finder as $file) {
            $ns = 'Towa\Setup\Commands';
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }
            $class = $ns.'\\'.$file->getBasename('.php');

            $r = new \ReflectionClass($class);

            if ($r->isSubclassOf('Towa\\Setup\\Command')) {
                $options[$r->name] = ($r->newInstanceWithoutConstructor())->description;
            }
        }

        return $options;
    }
}
