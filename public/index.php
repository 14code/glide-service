<?php
declare(strict_types=1);

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\ServerFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;

$appRoot = realpath(__DIR__ . '/..');
set_include_path($appRoot);

require 'vendor/autoload.php';

$configFile = $appRoot . '/data/config.json';
$config = json_decode(file_get_contents($configFile));

$glideDir = $appRoot . '/' . $config->glideDir;
$glideFilesystem = new Filesystem(new Local($glideDir));

$server = ServerFactory::create([
    'source' => $glideFilesystem,
    'cache' => $glideFilesystem,
    'source_path_prefix' => $config->source,
    'cache_path_prefix' => $config->cache,
    'driver' => 'imagick'
]);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$params = $_GET;

try {
    $image = $server->makeImage($path, $params);

    $responseFactory = new PsrResponseFactory(new Response(), function ($stream) {
        return new Stream($stream);
    });
    $response = $responseFactory->create($server->getCache(), $image);
}
catch (FileNotFoundException $exception) {
    $response = (new Response(404))->withBody(Utils::streamFor('File not found'));
}
catch (Throwable $throwable) {
    $response = (new Response(500))->withBody(Utils::streamFor('Something went wrong'));
}

(new SapiEmitter())->emit($response);
