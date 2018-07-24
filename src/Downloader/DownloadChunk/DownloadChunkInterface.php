<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\DownloadChunk;


/**
 * Interface DownloadChunkInterface
 *
 * Represent a method for downloading a chunk from some remote (or local) resource
 *
 * @package DownloadBooster\Downloader\DownloadChunk
 */
interface DownloadChunkInterface
{
    /**
     * @param string $url
     * @param int $lowByteOffset
     * @param int $highByteOffset
     */
    public function __construct(string $url, int $lowByteOffset, int $highByteOffset);

    /**
     * Perform the chunk download task.
     *
     * This is designed to match into the run method of Threaded. This way, Threaded::extend could be used to add
     * parallel processing functionality to the class at runtime if desired.
     */
    public function run(): void;

    /**
     * @return string The data chunk downloaded from the remote server
     */
    public function getChunkData(): string;
}