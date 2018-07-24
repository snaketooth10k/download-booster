<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\Serial;


use DownloadBooster\ChunkAdjuster;
use DownloadBooster\Download;
use DownloadBooster\Downloader\DownloadChunk\CurlDownloadChunk;
use DownloadBooster\Downloader\DownloadChunk\DownloadChunkInterface;
use DownloadBooster\Downloader\DownloaderInterface;

/**
 * Class SerialDownloader
 *
 * Downloads file chunks serially.
 *
 * @package DownloadBooster\Downloader\Serial
 */
class SerialDownloader implements DownloaderInterface
{
    /**
     * @var Download
     */
    private $download;

    /**
     * @var DownloadChunkInterface[]
     */
    private $downloadChunks;

    /**
     * SerialDownloader constructor
     *
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
     * Generate download chunks using chunk count and size
     */
    private function createChunks(): void
    {
        $ranges = ChunkAdjuster::createChunkRanges($this->download);

        foreach ($ranges as $lowOffset => $highOffset) {
            $chunks[] = new CurlDownloadChunk($this->download->getURL(), $lowOffset, $highOffset);
        }

        $this->downloadChunks = $chunks;
    }

    /**
     * Run each chunk's download
     */
    private function executeChunkDownloads(): void
    {
        foreach ($this->downloadChunks as &$chunk) {
            $chunk->run();
        }
    }

    /**
     * Assemble the data from each chunk and store it back to the download object
     */
    private function assembleDataChunks(): void
    {
        foreach ($this->downloadChunks as &$chunk) {
            $data[] = $chunk->getChunkData();
        }

        $this->download->setData(implode('', $data));
    }
}