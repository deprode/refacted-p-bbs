<?php

spl_autoload_register(
    function ($classname) {
        $filepath = __DIR__ . '/app/classes/' . strtolower($classname) . '.php';
        if (is_readable($filepath)) {
            require $filepath;
        }
    }
);