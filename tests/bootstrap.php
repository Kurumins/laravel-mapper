<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
/**
 * @param $key
 * @throws \Exception
 */
function config($key)
{
    if ($key !== 'mapper.fk_field_pattern') {
        throw new \Exception('The test setup does not have value of "' . $key . '" confg');
    }
    return '/^fk_(.*)/';
}

function base_path($path)
{
    return realpath(__DIR__ . '/../') . $path;
}