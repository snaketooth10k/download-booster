<?php

use DownloadBooster\ChunkDownloader\ChunkDownloaderSerial;
use PHPUnit\Framework\TestCase;

class ChunkDownloaderSerialTest extends TestCase
{
    public function testChunkDownloaderSerial(): void
    {
        $url = 'http://d5c24dfb.bwtest-aws.pravala.com/384MB.jar';
        $downloader = new ChunkDownloaderSerial($url, 0, 1023);

        $this->assertInstanceOf(ChunkDownloaderSerial::class, $downloader);

        $this->assertTrue($downloader->start());

        $this->assertTrue($downloader->join());

        $chunk = $downloader->getData();
        $this->assertInternalType('string', $chunk);
    }
}
