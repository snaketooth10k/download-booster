<?php declare(strict_types=1);


namespace DownloadBooster;


use DownloadBooster\ChunkDownloader\ChunkDownloaderInterface;
use DownloadBooster\ChunkDownloader\ChunkDownloaderParallel;

/**
 * Class DownloadBooster
 *
 * Downloads a file in chunks, trading HTTP overhead for parallel downloads.
 *
 * @package DownloadBooster
 */
class DownloadBooster
{
    /** @var string */
    private $url;

    /** @var int */
    private $chunkCount;

    /** @var int */
    private $chunkSize;

    /** @var string */
    private $chunkDownloaderClass;

    /** @var string */
    private $data;

    /**
     * DownloadBooster constructor.
     *
     * Validation of the $url and $chunkSize are handled at the ChunkDownloader level to allow for extension.
     *
     * @param string $url
     * @param int $chunkCount The number of chunks to get
     * @param int $chunkSize The size, in bytes, of each chunk. Defaults to 1/4 of 4 MiB.
     * @param string|null $chunkDownloaderClass The fully qualified class name of a ChunkDownloader
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $url,
        int $chunkCount = 4,
        int $chunkSize = 262144,
        string $chunkDownloaderClass = null
    )
    {
        if ($chunkCount < 1) {
            throw new \InvalidArgumentException('The chunkCount ${chunkCount} is invalid. It must be > 0');
        }

        $this->url = $url;
        $this->chunkCount = $chunkCount;
        $this->chunkSize = $chunkSize;
        $this->setChunkDownloaderClass($chunkDownloaderClass);
    }

    /**
     * Download the content of $this->url and store the data to $this->data
     */
    public function download(): bool
    {
        $tasks = range(0, $this->chunkCount - 1);
        if ($this->downloadChunks($tasks)) {
            return true;
        }

        return false;
    }

    /**
     * Creates and runs tasks
     *
     * @param int[] $tasks
     * @return bool
     */
    private function downloadChunks($tasks): bool
    {
        // The index list will be mutated to a list of ChunkDownloaders in startTasks
        if (!$this->startTasks($tasks)) {
            return false;
        }

        if (!$this->joinTasks($tasks)) {
            return false;
        }

        $this->mergeChunks($tasks);

        return true;
    }

    /**
     * Starts running the tasks
     *
     * @param array $tasks
     * @return bool
     */
    private function startTasks(array &$tasks): bool
    {
        $startingByte = 0;

        foreach ($tasks as &$task) {
            /** @var ChunkDownloaderInterface $task */
            $task = new $this->chunkDownloaderClass($this->url, $startingByte, $this->chunkSize);
            $startingByte += $this->chunkSize;

            if (!$task->start()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Waits for all tasks to be finished running if running in parallel
     *
     * @param ChunkDownloaderInterface[] $tasks
     * @return bool
     */
    private function joinTasks(array &$tasks): bool
    {
        foreach ($tasks as &$task) {
            if (!$task->join()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Takes an array of ChunkDownloaders and gathers the data they've obtained
     *
     * @param ChunkDownloaderInterface[] $tasks
     * @return bool
     */
    private function mergeChunks(array &$tasks): bool
    {
        foreach ($tasks as &$task) {
            $data[] = $task->getData();
        }

        $this->data = implode('', $data);

        return true;
    }

    /**
     * Provide the class for ChunkDownloader creation
     *
     * Creates an instance of a provided custom class by instantiating it and ensuring it implements the
     * ChunkDownloaderInterface.
     *
     * @param string|null $chunkDownloaderClass
     * @throws \InvalidArgumentException
     */
    private function setChunkDownloaderClass(?string $chunkDownloaderClass): void
    {
        if (empty($chunkDownloaderClass)) {
            $this->chunkDownloaderClass = ChunkDownloaderParallel::class;

        } else if (new $chunkDownloaderClass('http://ietf.org', 1, 1) instanceof ChunkDownloaderInterface) {
            // Check manually provided ChunkDownloaders for interface compliance
            $this->chunkDownloaderClass = $chunkDownloaderClass;

        } else {
            throw new \InvalidArgumentException(
                'The class "${chunkDownloaderClass}" must implement '
                . 'DownloadBooster\ChunkDownloader\ChunkDownloaderInterface.');
        }
    }
}