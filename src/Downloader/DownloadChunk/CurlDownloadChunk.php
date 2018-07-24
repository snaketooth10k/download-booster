<?php declare(strict_types=1);


namespace DownloadBooster\Downloader\DownloadChunk;


/**
 * Class CurlDownloadChunk
 *
 * Represents the download information for a single chunk of data.
 *
 * @package DownloadBooster\Downloader\DownloadChunk
 */
class CurlDownloadChunk implements DownloadChunkInterface
{
    /** @var int HTTP Status Code returned when partial content is returned */
    const PARTIAL_CONTENT_STATUS_CODE = 206;

    /** @var string */
    private $url;

    /** @var int */
    private $lowByteOffset;

    /** @var int */
    private $highByteOffset;

    /** @var string */
    private $chunkData;

    /**
     * @param string $url
     * @param int $lowByteOffset
     * @param int $highByteOffset
     */
    public function __construct(string $url, int $lowByteOffset, int $highByteOffset)
    {
        $this->url = $url;
        $this->lowByteOffset = $lowByteOffset;
        $this->highByteOffset = $highByteOffset;
    }

    /**
     * Get the remote content and store it to the download object
     *
     * This is designed to match into the run method of Threaded. This way, Threaded::extend could be used to add
     * parallel processing functionality to the class at runtime if desired.
     *
     * @throws \Exception
     */
    public function run(): void
    {
        $this->chunkData = $this->downloadChunk();
    }


    /**
     * @return string The data chunk downloaded from the remote server
     */
    public function getChunkData(): string
    {
        return $this->chunkData;
    }

    /**
     * Create the cURL request and execute it
     *
     * @return string
     * @throws \Exception
     */
    private function downloadChunk(): string
    {
        $request = $this->createRequest();
        return $this->fireRequest($request);
    }

    /**
     * Form the cURL request for this chunk
     *
     * @return resource
     * @throws \Exception
     */
    private function createRequest()
    {
        if (!$request = curl_init($this->url)) {
            throw new \Exception('A cURL object could not be created.');
        }
        $byteRange = (string)$this->lowByteOffset . '-' . (string)$this->highByteOffset;

        curl_setopt_array($request, [
            CURLOPT_RANGE => $byteRange,
            CURLOPT_RETURNTRANSFER => 1,

        ]);
        return $request;
    }

    /**
     * Fire the request, check the status, and return the chunk data
     *
     * @param $request
     * @return string
     * @throws \Exception
     */
    private function fireRequest($request): string
    {
        $data = curl_exec($request);

        $responseStatusCode = curl_getinfo($request)['http_code'];

        curl_close($request);

        if ($responseStatusCode === self::PARTIAL_CONTENT_STATUS_CODE) {
            return $data;
        } else {
            throw new \Exception('Invalid response from server.');
        }
    }
}