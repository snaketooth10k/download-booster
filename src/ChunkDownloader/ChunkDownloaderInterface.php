<?php


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
     * Due to a change in typehint handling in php7.1, TYPE $var = null now resolves to ?TYPE $var = null in the
     * interpreter, which results in a mismatch of Thread::start and interface. An issue will be opened against pthreads
     * for a fix to this issue. This method must still be implemented, but due to the experimental nature of these
     * libraries, a proper interface cannot currently be provided.
     *
     * @param int $options
     * @return bool
     */
//    public function start(int $options = null);

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