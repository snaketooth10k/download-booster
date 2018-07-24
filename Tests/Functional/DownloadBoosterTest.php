<?php declare(strict_types=1);

use DownloadBooster\DownloadBooster;
use PHPUnit\Framework\TestCase;

class DownloadBoosterTest extends TestCase
{
    function testDownload(): void
    {
        $downloadBooster = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            1,
            1
        );

        $output = $downloadBooster->getRemoteContent();
        $this->assertInternalType('string', $output);
    }

    public function testChunking(): void
    {
        $downloadBooster = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            3,
            7
        );
        $reversedChunks = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            7,
            3
        );

        $this->assertEquals(
            $downloadBooster->getRemoteContent(),
            $reversedChunks->getRemoteContent(),
            'Chunk size and count are not reversible. Chunking is not being handled properly.'
        );
    }

    public function testChunkCalculation(): void
    {
        $downloadBooster = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            2,
            2
        );

        $halfChunks = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            1,
            2
        );

        $halfChunkSize = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            2,
            1
        );

        $defaultLength = strlen($downloadBooster->getRemoteContent());
        $halfChunkLength = strlen($halfChunks->getRemoteContent());
        $halfChunkSizeLength = strlen($halfChunkSize->getRemoteContent());

        $this->assertEquals(
            $defaultLength,
            $halfChunkLength * 2,
            'Halving the chunk count did not half the returned data.'
        );

        $this->assertEquals(
            $defaultLength,
            $halfChunkSizeLength * 2,
            'Halving the chunk size did not half the returned data.'
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /(\-1)+/
     */
    public function testInvalidChunkCount(): void
    {
        $downloadBooster = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            -1,
            1
        );

        $downloadBooster->getRemoteContent();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /(\-1)+/
     */
    public function testInvalidChunkSize(): void
    {
        $downloadBooster = new DownloadBooster(
            'SerialDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            1,
            -1
        );

        $downloadBooster->getRemoteContent();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /(RandomDownloader)+/i
     */
    public function testInvalidDownloaderClass(): void
    {
        $downloadBooster = new DownloadBooster(
            'RandomDownloader',
            'https://www.w3.org/TR/PNG/iso_8859-1.txt',
            1,
            1
        );

        $downloadBooster->getRemoteContent();
    }
}
