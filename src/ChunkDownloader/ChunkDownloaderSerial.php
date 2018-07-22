<?php declare(strict_types=1);


namespace DownloadBooster\ChunkDownloader;


class ChunkDownloaderSerial implements ChunkDownloaderInterface
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
            throw new \InvalidArgumentException("chunkSize of ${chunkSize} is not valid. Must be >= 0.");
        }
        if ($this->chunkSize < 0) {
            throw new \InvalidArgumentException("chunkStart of ${chunkStart} is not valid. Must be >= 0.");
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("The url '${url}' appears to be invalid.");
        }

        $this->url = $url;
        $this->chunkStart = $chunkStart;
        $this->chunkSize = $chunkSize;
    }
    /**
     * Carry out the chunk download
     *
     * This class downloads in serial, so start will run the download.
     *
     * @param int|null $options
     * @return bool
     * @throws \Exception
     */
    public function start($options = 0): bool
    {
        $this->requestChunk();
        return true;
    }

    /**
     * Join the subthread back to the caller
     *
     * This method must return true if the ChunkDownloader does not process in parallel
     *
     * @return bool
     */
    public function join()
    {
        return true;
    }

    /**
     * Get the data downloaded from the server
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
     * @throws \Exception
     */
    private function requestChunk(): void
    {
        $request = $this->createRequest();

        $data = curl_exec($request);

        $responseStatusCode = curl_getinfo($request)['http_code'];

        curl_close($request);

        if ($responseStatusCode === self::PARTIAL_CONTENT_STATUS_CODE) {
            $this->data = $data;
        } else {
            throw new \Exception('Invalid response from server.');
        }
    }

    /**
     * Create the cURL resource and set options
     *
     * @return resource
     * @throws \Exception
     */
    private function createRequest()
    {
        if (!$request = curl_init($this->url)) {
            throw new \Exception('A cURL object could not be created.');
        }
        $chunkEnd = $this->chunkSize + $this->chunkStart - 1;
        $byteRange = (string)$this->chunkStart . '-' . (string)$chunkEnd;

        curl_setopt_array($request, [
            CURLOPT_RANGE => $byteRange,
            CURLOPT_RETURNTRANSFER => 1,

        ]);
        return $request;
    }
}