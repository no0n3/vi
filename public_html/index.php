<?php

$config = [
    'params' => include('../config/params.php'),
    'components' => include('../config/components.php')
];

include '../Vi.php';

//defined('VI_ENV') or define('VI_ENV', 'dev');

\App::run($config);
