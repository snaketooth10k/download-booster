<?php declare(strict_types=1);


use DownloadBooster\ChunkDownloader\ChunkDownloaderParallel;

class ChunkDownloaderParallelTest extends \PHPUnit\Framework\TestCase
{
    public function testChunkDownloaderParallel(): void
    {
        $url = 'http://d5c24dfb.bwtest-aws.pravala.com/384MB.jar';
        $downloader = new ChunkDownloaderParallel($url, 0, 1023);

        $this->assertInstanceOf(ChunkDownloaderParallel::class, $downloader);

        $downloader->start();
        $this->assertTrue($downloader->isStarted());

        $downloader->join();
        $this->assertTrue($downloader->isJoined());

        $chunk = $downloader->getData();
        $this->assertInternalType('string', $chunk);
    }
}
