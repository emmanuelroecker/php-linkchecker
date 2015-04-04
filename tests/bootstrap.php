<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Process\Process;

// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s',
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    WEB_SERVER_DOCROOT
);

$process = new Process($command);
$process->start();

echo sprintf(
        '%s - Web server started on %s:%d',
        date('r'),
        WEB_SERVER_HOST,
        WEB_SERVER_PORT
    ) . PHP_EOL;

//wait server start
sleep(1);

// Kill the web server when the process ends
register_shutdown_function(
    function () use ($process) {
        echo 'Web server shutdown' . PHP_EOL;
        $process->stop();
    }
);

// More bootstrap code