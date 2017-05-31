<?php

namespace Towa\Setup;

use FilesystemIterator;
use League\CLImate\CLImate;
use Towa\Setup\Utilities\Config;

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

        (new Config());

        $this->decideWhatToExecute();

        // start provisioning
        self::$climate->info('Done!');
    }

    private function drawTowa()
    {
        self::$climate->towa()->addArt(__DIR__.'/../art');
        self::$climate->animation($this->getArt())->enterFrom($this->getAnimationDirection());
    }

    protected function decideWhatToExecute()
    {
        $input = self::$climate->radio('Whatcha wanna do?', [
            'Towa\Setup\Commands\Create' => 'Add new site',
            'Towa\Setup\Commands\Delete' => 'Delete existing sites',
        ]);

        $class = $input->prompt();

        // TODO: different way to reprompt?
        if (empty($class)) {
            $this->decideWhatToExecute();
        }

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

        return 'towa'.rand(1, $count);
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
}
