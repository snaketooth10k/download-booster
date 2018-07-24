<?php declare(strict_types=1);


namespace DownloadBooster;


use DownloadBooster\Downloader\DownloaderFactory;

/**
 * Class DownloadBooster
 *
 * Offers a facade for downloading files in chunks
 *
 * @package DownloadBooster
 */
class DownloadBooster
{
    private $downloader;

    /**
     * DownloadBooster constructor.
     *
     * @param string $chunkDownloaderClass The name of the Downloader class to use
     * @param string $url
     * @param int $chunkCount The number of chunks to get
     * @param int $chunkSize The size, in bytes, of each chunk. Defaults to 1/4 of 4 MiB.
     */
    public function __construct(string $chunkDownloaderClass, string $url, int $chunkCount, int $chunkSize)
    {
        $download = new Download($url, $chunkCount, $chunkSize);
        $this->downloader = DownloaderFactory::getChunkDownloader($chunkDownloaderClass, $download);
    }

    public function getRemoteContent(): string
    {
        $this->downloader->run();
        $download = $this->downloader->getDownload();

        return $download->getData();
    }
}