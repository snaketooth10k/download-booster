#!/usr/bin/env php
<?php declare(strict_types=1);

use DownloadBooster\ChunkDownloader\ChunkDownloaderParallel;
use DownloadBooster\ChunkDownloader\ChunkDownloaderSerial;
use DownloadBooster\DownloadBooster;
use GetOpt\GetOpt;
use GetOpt\Option;

require __DIR__ . '/vendor/autoload.php';


// Define command line options
$optionURL = new Option('u', 'url', GetOpt::REQUIRED_ARGUMENT);
$optionURL->setDescription(
    'The URL of the file to download, like http://ipv4.download.thinkbroadband.com/100MB.zip'
);

// Outfile name
$optionOutputFile = new Option('o', 'output-file', GetOpt::OPTIONAL_ARGUMENT);
$optionOutputFile->setDescription('The name of the file to write to disk, like file.part.jar');

// Parallel processing flag
$optionParallel = new Option('p', 'parallel', GetOpt::NO_ARGUMENT);
$optionParallel->setDescription('Download file chunks in parallel')
    ->setValidation(function () {
            return extension_loaded('pthreads');
        }, PHP_EOL . 'You must have pthreads loaded to perform parallel downloads from the cli.' . PHP_EOL
            . 'See https://stackoverflow.com/questions/34969325/how-to-install-php7-zts-pthreads-on-ubuntu-14-04'
            . PHP_EOL . 'for a good guide for compiling php with ZTS and pthreads.'
            . PHP_EOL . 'The library was tested with PHP 7.2.8 and pthreads master branch on Ubuntu18.04 Server.'
            . PHP_EOL
    );

// Chunk Count
$optionChunkCount = new Option('c', 'chunk-count', GetOpt::OPTIONAL_ARGUMENT);
$optionChunkCount
    ->setDescription('The number of chunks to use for the download. Default = 4, Min = 1, Max = 12')
    ->setDefaultValue('4')
    ->setValidation(function ($value) {
            return is_numeric($value) && $value <= 12 && $value > 0;
        }, 'Chunk count must be numeric, at least 1, and at most 12.'
    );

// Chunk Size
$optionChunkSize = new Option('s', 'chunk-size', GetOpt::OPTIONAL_ARGUMENT);
$optionChunkSize->setDescription('The size of each download chunk in bytes. Default = 1048576')
    ->setDefaultValue('1048576')
    ->setValidation(function ($value) {
            return is_numeric($value) && (int) $value > 0;
        }, 'Chunk size must be a number greater than 0'
    );

// Help
$optionHelp = new Option('h', 'help', GetOpt::NO_ARGUMENT);
$optionHelp->setDescription(
    'Show the help text'
);

$getOpt = new GetOpt([
    $optionURL,
    $optionOutputFile,
    $optionParallel,
    $optionChunkCount,
    $optionChunkSize,
    $optionHelp,
]);


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
$fileName = $getOpt->getOption('output-file');
$url = $getOpt->getOption('url');
$parallel = $getOpt->getOption('parallel');
$chunkCount = (int)$getOpt->getOption('chunk-count');
$chunkSize = (int)$getOpt->getOption('chunk-size');


// Download and store file
$downloadStrategy = $parallel ? ChunkDownloaderParallel::class : ChunkDownloaderSerial::class;
$downloader = new DownloadBooster((string) $url, $downloadStrategy, $chunkCount, $chunkSize);
$downloader->download();

if (empty($fileName)) {
    $urlParts = explode('/', $url);
    $fileName = array_pop($urlParts);
}


// Guard against overwriting files, more semantically pleasing than mode x
if (file_exists($fileName)) {
    echo "The file ${fileName} already exists.";
    exit;
}
if ($file = fopen($fileName, 'w')) {
    fwrite($file, $downloader->getData());
    fclose($file);
    echo "File ${fileName} written" . PHP_EOL;
    exit;
}

echo "Couldn't open file ${fileName} for writing.";
exit;
