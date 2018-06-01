<?php

function determine_os_user_home_dir()
{
    $os = [
        'mac' => '/Users',
        'linux' => '/home',
        'winnt' => '/Users',
    ];

    return $os[strtolower(PHP_OS)] ?? '';
}