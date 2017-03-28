<?php
namespace Towa\Setup;

use FilesystemIterator;
use League\CLImate\CLImate;
use Towa\Setup\Commands\Delete;
use Towa\Setup\Commands\NewProject;

class Command
{
    /* @var CLImate */
    public $climate;

    /**
     * Command constructor.
     *
     * @param $climate CLImate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function run()
    {
        $this->drawTowa();

        $this->decideWhatToExecute();

        // start provisioning
        $this->climate->info('Done!');
    }

    private function drawTowa()
    {
        $this->climate->towa()->addArt(__DIR__ . '/../art');
        $this->climate->animation($this->getArt())->enterFrom($this->getAnimationDirection());
    }

    protected function decideWhatToExecute()
    {
        $input = $this->climate->radio('Was soll gemacht werden?', [
            'NewProject' => 'Seite erstellen',
            'Delete' => 'Seite löschen',
        ]);

        switch ($input->prompt()) {
            case 'NewProject':
                (new NewProject($this->climate))->execute();
                break;
            case 'Delete':
                (new Delete($this->climate))->execute();
                break;
        }
    }

    protected function question($question, $required = false, $default = '')
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
}
