<?php

//$start = microtime(true);

require_once __DIR__ . '/vendor/autoload.php'; 

$app = new \Share\App(['config.ini.php']);
$app->run();

//echo "Elapsed: ".(microtime(true) - $start);