<?php
/**
 * Created by PhpStorm.
 * User: william
 * Date: 7/21/18
 * Time: 1:33 PM
 */

use DownloadBooster\ChunkDownloader\MockChunkDownloader;
use DownloadBooster\DownloadBooster;
use PHPUnit\Framework\TestCase;

class DownloadBoosterTest extends TestCase
{
    public static $url = 'http://d5c24dfb.bwtest-aws.pravala.com/384MB.jar';

    public function test__construct()
    {
        $booster = new DownloadBooster(self::$url);
        $this->assertInstanceOf(DownloadBooster::class, $booster);

        $booster = new DownloadBooster(self::$url, 1, 100);
        $this->assertInstanceOf(DownloadBooster::class, $booster);

        $booster = new DownloadBooster('notawebsite');
        $this->expectException(InvalidArgumentException::class);

        $booster = new DownloadBooster(self::$url, -5);
        $this->expectException(InvalidArgumentException::class);
    }

    public function testDownload()
    {
        $booster = new DownloadBooster(self::$url, 4, 1, MockChunkDownloader::class);
        $result = $booster->download();

        $this->assertInternalType('bool', $result);

        $data = $booster->getData();

        // We used 4 chunks of size 1, allowing us to see ordering of the result
        $this->assertEquals('0123', $data);
    }
}
