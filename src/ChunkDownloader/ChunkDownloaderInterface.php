<?php declare(strict_types=1);


namespace DownloadBooster\ChunkDownloader;


interface ChunkDownloaderInterface
{
    /**
     * Carry out the chunk download
     *
     * Since PHP doesn't support parallelism out-of-the-box, this interface guarantees that a non-parallel
     * ChunkDownloaderParallel can be swapped in if necessary.
     *
     * @param int $options
     */
    public function start($options = 0);

    /**
     * Get the data downloaded from the server
     *
     * @return string
     */
    public function getData(): string;
}