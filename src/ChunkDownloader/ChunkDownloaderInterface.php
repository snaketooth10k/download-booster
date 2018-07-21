<?php declare(strict_types=1);


namespace DownloadBooster\ChunkDownloader;


/**
 * Interface ChunkDownloaderInterface
 * 
 * A ChunkDownloader represents a strategy for downloading a chunk of a file. DownloadBooster requires that the
 * ChunkDownloader used implements this interface. This prevents the need for modifying the DownloadBooster itself.
 *
 * @package DownloadBooster\ChunkDownloader
 */
interface ChunkDownloaderInterface
{
    public function __construct(string $url, int $chunkStart, int $chunkSize);

    /**
     * Carry out the chunk download
     *
     * Since PHP doesn't support parallelism out-of-the-box, this interface guarantees that a non-parallel
     * ChunkDownloaderParallel can be swapped in if necessary.
     *
     * @param int $options
     * @return bool
     */
    public function start($options = 0);

    /**
     * Join the subthread back to the caller
     *
     * This method must return true if the ChunkDownloader does not process in parallel
     *
     * @return bool
     */
    public function join();

    /**
     * Get the data downloaded from the server
     *
     * @return string
     */
    public function getData(): string;
}