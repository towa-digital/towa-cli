<?php

function get_config(string $key)
{
    return \Towa\Setup\Utilities\Config::get($key);
}

function get_sites()
{
    return \Towa\Setup\Utilities\YamlParser::readFile(get_config('path_config'))['sites'];
}
