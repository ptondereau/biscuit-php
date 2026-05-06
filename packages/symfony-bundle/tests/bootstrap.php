<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!extension_loaded('biscuit_php')) {
    require dirname(__DIR__) . '/stubs/biscuit-php.stubs.php';
}
