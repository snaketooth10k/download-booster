<?php declare(strict_types=1);


namespace DownloadBooster\ChunkDownloader;


use Requests;
use Requests_Response;

/**
 * Class ChunkDownloaderParallel
 *
 * This class represents the task of downloading an assigned chunk to memory. It extends the Thread class so it can act
 * in parallel with other ChunkDownloaders if implemented. Note that the attribute "data" is used instead of "chunk"
 * because we don't wished to confuse this with the \Threaded::chunk() method.
 *
 * @package DownloadBooster
 */
class ChunkDownloaderParallel extends \Thread implements ChunkDownloaderInterface
{
    /** @var int The expected HTTP status code returned for partial content */
    const PARTIAL_CONTENT_STATUS_CODE = 206;

    /** @var string */
    private $url;

    /** @var int */
    private $chunkSize;

    /** @var int */
    private $chunkStart;

    /** @var string The downloaded data */
    private $data;

    /**
     * ChunkDownloaderParallel constructor
     *
     * @param string $url
     * @param int $chunkStart The first byte of the chunk
     * @param int $chunkSize
     * @throws \InvalidArgumentException
     */
    public function __construct(string $url, int $chunkStart, int $chunkSize)
    {
        if ($this->chunkStart < 0) {
            throw new \InvalidArgumentException('chunkSize of ${chunkSize} is not valid. Must be >= 0.');
        }
        if ($this->chunkSize < 0) {
            throw new \InvalidArgumentException('chunkStart of ${chunkStart} is not valid. Must be >= 0.');
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('The url "${url}" appears to be invalid.');
        }

        $this->url = $url;
        $this->chunkStart = $chunkStart;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Fires when the newly spawned thread is opened after calling start()
     */
    public function run(): void
    {
        $response = $this->requestChunk();
        if ($response->status_code === self::PARTIAL_CONTENT_STATUS_CODE) {
            $this->data = $response->body;
        }
    }

    /**
     * Return the body of the request
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Create and fire a request to get a single chunk
     *
     * @return Requests_Response
     */
    private function requestChunk(): Requests_Response
    {
        // Header will look like "bytes=0-1023"
        $byteRange = 'bytes=' . (string) $this->chunkStart . '-' . (string) ($this->chunkSize - $this->chunkStart - 1);
        $headers = [
            'Range' => $byteRange
        ];

        return Requests::get($this->url, $headers);
    }
}