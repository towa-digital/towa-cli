<?php

namespace Towa\Setup\Commands\Services;

use Towa\Setup\Command;

class Repository extends Command
{
    /**
     * @param string $repository url for the repository
     * @param string $target path to target-folder
     * @throws \RuntimeException if the git clone command failes
     */
    public function pull($repository, $target)
    {
        exec("git clone $repository $target", $output, $status);

        if (0 !== $status) {
            throw new \RuntimeException("Could not clone repository: $repository");
        }
    }
}