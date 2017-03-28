<?php

function get_config(string $key) {
    return (new \Towa\Setup\Utilities\ConfigParser)->get($key);
}
