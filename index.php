<?php

//$start = microtime(true);
require_once __DIR__ . '/vendor/autoload.php';
\Dynart\Micro\App::run(new \Share\App(['config.ini.php']));
//echo "Elapsed: ".(microtime(true) - $start);