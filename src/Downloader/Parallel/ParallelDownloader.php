<?php declare(strict_types=1);


namespace DownloadBooster\Downloader;


use DownloadBooster\ChunkAdjuster;
use DownloadBooster\Download;
use DownloadBooster\Downloader\DownloadChunk\CurlDownloadChunk;
use DownloadBooster\Downloader\Parallel\DownloadThread;

/**
 * Class ParallelDownloader
 *
 * This class leverages the pthreads extension to download multiple file chunks in parallel. In order to use it, PHP
 * must be compiled with ZTS and pthreads.
 *
 * @package DownloadBooster
 */
class ParallelDownloader extends \Thread implements DownloaderInterface
{
    /** @var Download */
    private $download;

    /** @var DownloadThread[] */
    private $downloadThreads;

    /**
     * @param Download $download
     */
    public function __construct(Download $download)
    {
        $this->download = $download;
    }

    /**
     * Download the remote file
     */
    public function run(): void
    {
        $this->createChunks();
        $this->executeChunkDownloads();
        $this->assembleDataChunks();
    }

    /**
     * @return Download
     */
    public function getDownload(): Download
    {
        return $this->download;
    }

    /**
     * Generate download chunks
     */
    private function createChunks(): void
    {
        $ranges = ChunkAdjuster::createChunkRanges($this->download);

        // This causes CurlDownloadChunk to extend Volatile at runtime
        \Threaded::extend(CurlDownloadChunk::class);

        foreach ($ranges as $lowOffset => $highOffset) {
            $task = new CurlDownloadChunk($this->download->getURL(), $lowOffset, $highOffset);
            $threads[] = new DownloadThread($task);
        }

        $this->downloadThreads = $threads;
    }

    /**
     * Download each chunk
     */
    private function executeChunkDownloads(): void
    {
        // Iteration by reference is not allowed on volatile objects
        foreach ($this->downloadThreads as $thread) {
            $thread->start();
        }

        foreach ($this->downloadThreads as $thread) {
            $thread->join();
        }
    }

    /**
     * Assemble the data from each chunk and store it back to the download object
     */
    private function assembleDataChunks(): void
    {
        foreach ($this->downloadThreads as $thread) {
            $chunk = $thread->getDownloadChunk();
            $data[] = $chunk->getChunkData();
        }

        /*
         * Something strange happens when running the chunks in parallel. The download object ($this->download) seems
         * to become immutable. It throws no exception, nor error, but it will not be modified. This is resolved by
         * using the fluent setter to replace the existing object. This warrants investigation, but is likely a
         * side-effect of the threadsafe classes in use.
         */
        $this->download = $this->download->setData(implode('', $data));
    }
}