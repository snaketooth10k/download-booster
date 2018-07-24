<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\Parallel;


use DownloadBooster\Downloader\DownloadChunk\DownloadChunkInterface;

class DownloadThread extends \Thread
{
    private $downloadChunk;

    public function __construct(DownloadChunkInterface $downloadChunk)
    {
        $this->downloadChunk = $downloadChunk;
    }

    public function run(): void
    {
        $this->downloadChunk->run();
    }

    public function getDownloadChunk(): DownloadChunkInterface
    {
        return $this->downloadChunk;
    }
}