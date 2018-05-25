<?php

function get_config(string $key)
{
    return \Towa\Setup\Utilities\Config::get($key);
}
