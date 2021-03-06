#!/usr/bin/env php
<?php declare(strict_types=1);


use DownloadBooster\DownloadBooster;
use DownloadBoosterCLI\GetOptUtility;


require __DIR__ . '/../vendor/autoload.php';


$getOpt = GetOptUtility::createGetOpt();

// Parse arguments passed from CLI
try {
    $getOpt->process($argv);
} catch (\GetOpt\ArgumentException\Invalid $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit;
} catch (\GetOpt\ArgumentException\Missing $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit;
} catch (\GetOpt\ArgumentException\Unexpected $exception) {
    echo 'If you tried to pass a negative number, that is not allowed see --help' . PHP_EOL;
    exit;
}

// Show help if required url is not passed, or if help arg is passed
if ($getOpt->getOption('help') || !$getOpt->getOption('url')) {
    echo $getOpt->getHelpText();
    exit;
}


// Extract arguments
$fileName = (string)$getOpt->getOption('output-file');
$url = (string)$getOpt->getOption('url');
$parallel = (bool)$getOpt->getOption('parallel');
$chunkCount = (int)$getOpt->getOption('chunk-count');
$chunkSize = (int)$getOpt->getOption('chunk-size');


// Use the last part of the URL as file name if not provided
if (empty($fileName)) {
    $urlParts = explode('/', $url);
    $fileName = array_pop($urlParts);
}


// Guard against overwriting files, more semantically pleasing than mode x
if (file_exists($fileName)) {
    echo "The file ${fileName} already exists." . PHP_EOL;
    exit;
}

// Download and store file
$downloadStrategy = $parallel ? 'ParallelDownloader' : 'SerialDownloader';
$downloader = new DownloadBooster($downloadStrategy, $url, $chunkCount, $chunkSize);

try {
    $data = $downloader->getRemoteContent();
} catch (InvalidArgumentException $exception) {
    echo $exception->getMessage() . PHP_EOL;
    echo $getOpt->getHelpText();
    exit;
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit;
}

if ($file = fopen($fileName, 'w')) {
    fwrite($file, $data);
    fclose($file);
    echo "File ${fileName} written" . PHP_EOL;
    exit;
}

echo "Couldn't open file ${fileName} for writing." . PHP_EOL;
exit;
