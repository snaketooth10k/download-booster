<?php


namespace DownloadBooster\Downloader;


use DownloadBooster\Download;

/**
 * Interface DownloaderInterface
 * 
 * A Downloader represents a strategy for downloading a remote file. DownloadBooster requires that the
 * Downloader used implements this interface.
 *
 * @package DownloadBooster\Downloader
 */
interface DownloaderInterface
{
    /**
     * @param Download $download
     */
    public function __construct(Download $download);

    /**
     * Download the remote file
     */
    public function run(): void;

    /**
     * @return Download
     */
    public function getDownload(): Download;
}