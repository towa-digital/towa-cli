<?php

namespace Towa\Setup\Commands\Services;

use Towa\Setup\Command;

class Database extends Command
{
    private $user = 'root';
    private $password;
    private $host = '127.0.0.1';
    private $port;

    /**
     * @param string $database_name name of the database. will be normalized
     * @throws \RuntimeException if the database-name is missing or could not create database
     */
    public function create($database_name)
    {
        if (null === $database_name) {
            throw new \RuntimeException('Missing database-name');
        }

        $database_name = $this->normalize_database_name($database_name);
        $command = $this->create_mysql_command($database_name);

        exec($command, $output, $status);

        if (0 !== $status) {
            throw new \RuntimeException('Could not create database. Details: ' . $output);
        }
    }

    public function set_password($password): void
    {
        $this->password = $password;
    }

    public function set_host($host): void
    {
        $this->host = $host;
    }

    public function set_port($port): void
    {
        $this->port = $port;
    }

    private function create_mysql_command($database_name)
    {
        $sql_query = "CREATE DATABASE $database_name;";

        return sprintf(
            'echo "%1$s" | mysql -u %2$s -h %3$s -P %4$s',
            $sql_query,
            $this->user,
            $this->host,
            $this->port
        );
    }

    private function normalize_database_name($database_name)
    {
        $database_name = str_replace(['-', ' '], '_', $database_name);
        $database_name = strtolower($database_name);

        return $database_name;
    }
}