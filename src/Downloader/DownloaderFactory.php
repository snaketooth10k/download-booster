<?php declare(strict_types=1);


namespace DownloadBooster\Downloader;


use DownloadBooster\Downloader\Serial\MockDownloader;
use DownloadBooster\Downloader\Serial\SerialDownloader;
use DownloadBooster\Download;

/**
 * Class DownloaderFactory
 * @package DownloadBooster\Downloader
 */
class DownloaderFactory
{
    /**
     * @param string $class The class to use to instantiate a Downloader object
     * @param Download $download
     * @return DownloaderInterface
     */
    public static function getChunkDownloader(string $class, Download $download): DownloaderInterface
    {
        $class = strtolower($class);

        if ($class == 'paralleldownloader') {
            return new ParallelDownloader($download);
        }
        if ($class == 'mockdownloader') {
            return new MockDownloader($download);
        }
        if ($class == 'serialdownloader') {
            return new SerialDownloader($download);
        }

        throw new \InvalidArgumentException("${class} is not an expected Downloader class.");
    }
}