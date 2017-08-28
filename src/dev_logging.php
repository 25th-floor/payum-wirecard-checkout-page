<?php
declare(strict_types=1);

function dolog($message)
{
    file_put_contents('/var/www/test/test.log', print_r($message, true) . PHP_EOL, FILE_APPEND);
}