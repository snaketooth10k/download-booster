<?php declare(strict_types=1);


namespace DownloadBooster\ChunkDownloader;


class MockChunkDownloader implements ChunkDownloaderInterface
{
    private $chunkStart;

    public function __construct(string $url, int $chunkStart, int $chunkSize)
    {
        $this->chunkStart = $chunkStart;
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
     * For this mock, the chunkStart is used so it can be used to check the ordering of results
     *
     * @return string
     */
    public function getData(): string
    {
        return (string) $this->chunkStart;
    }

    /**
     * Carry out the chunk download
     *
     * Due to a change in typehint handling in php7.1, TYPE $var = null now resolves to ?TYPE $var = null in the
     * interpreter, which results in a mismatch of Thread::start and interface. An issue will be opened against pthreads
     * for a fix to this issue. This method must still be implemented, but due to the experimental nature of these
     * libraries, a proper interface cannot currently be provided. For now, the function will be wrapped.
     *
     * @param int|null $options
     * @return bool
     */
    public function start($options = 0): bool
    {
        return true;
    }
}