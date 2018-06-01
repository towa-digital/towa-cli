<?php

namespace Towa\Setup;

use FilesystemIterator;
use League\CLImate\CLImate;
use Towa\Setup\Commands\Project\Create;
use Towa\Setup\Commands\Project\Show;

class Command
{
    /* @var CLImate */
    public $climate;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function run()
    {
        $this->drawTowa();
        $this->showAvailableCommands();
    }

    private function drawTowa()
    {
        $this->climate->towa()->addArt(__DIR__ . '/../art');
        $this->climate->animation($this->getArt())->enterFrom($this->getAnimationDirection());
    }

    private function showAvailableCommands()
    {
        $Command = $this->decideWhatToExecute();

        if ('exit' === $Command){
            $this->climate->info('Bye!');
            exit();
        }

        (new $Command($this->climate))->execute();

        $this->showAvailableCommands();
    }

    protected function decideWhatToExecute()
    {
        $input = $this->climate->radio('What shall I do?', [
            Create::class => 'Add new devilbox-project',
            Show::class => 'Show current projects',
            'exit' => 'Bye!',
        ]);

        $class = $input->prompt();

        if (null === $class) {
            $this->decideWhatToExecute();
        }

        return $class;
    }

    protected function question($question, $required = false, $default = '')
    {
        $input = $this->climate->cyan()->input($question);

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
            new FilesystemIterator(__DIR__ . '/../art', FilesystemIterator::SKIP_DOTS)
        );

        return 'towa' . random_int(1, $count);
    }

    private function getAnimationDirection()
    {
        $directions = ['bottom', 'top'];

        return $directions[array_rand($directions, 1)];
    }

    public function log(string $message)
    {
        return $this->climate->comment($message);
    }
}
