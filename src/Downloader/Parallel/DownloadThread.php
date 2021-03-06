<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\Parallel;


use DownloadBooster\Downloader\DownloadChunk\DownloadChunkInterface;

/**
 * Class DownloadThread
 *
 * An extension of Thread used to run a single chunk download
 *
 * @package DownloadBooster\Downloader\Parallel
 */
class DownloadThread extends \Thread
{
    /** @var DownloadChunkInterface */
    private $downloadChunk;

    /**
     * @param DownloadChunkInterface $downloadChunk
     */
    public function __construct(DownloadChunkInterface $downloadChunk)
    {
        $this->downloadChunk = $downloadChunk;
    }

    /**
     *  Performs the run method given by the DownloadChunk
     */
    public function run(): void
    {
        $this->downloadChunk->run();
    }

    /**
     * @return DownloadChunkInterface Pass back the DownloadChunk
     */
    public function getDownloadChunk(): DownloadChunkInterface
    {
        return $this->downloadChunk;
    }
}