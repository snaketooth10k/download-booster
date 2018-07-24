<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\Serial;


use DownloadBooster\Downloader\DownloaderInterface;
use DownloadBooster\Download;

class MockDownloader implements DownloaderInterface
{
    private $download;

    public function __construct(Download $download)
    {
        $this->download = $download;
    }

    /**
     * Download the remote file
     */
    public function run(): void
    {
        return;
    }

    /**
     * Return the download, typically after storing the downloaded data
     *
     * @return Download
     */
    public function getDownload(): Download
    {
        return $this->download;
    }
}