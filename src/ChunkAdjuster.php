<?php declare(strict_types=1);


namespace DownloadBooster;


/**
 * Class ChunkAdjuster
 *
 * Provides utility for checking and adjusting chunk size and count.
 *
 * @package DownloadBooster
 */
class ChunkAdjuster
{
    /**
     * Changes the chunk size or count to effectively download the remote file
     *
     * @param Download $download
     * @throws \Exception
     * @return Download
     */
    public static function adjustChunking(Download $download): Download
    {
        $remoteFileSize = self::getRemoteFileSize($download);
        $chunkCount = $download->getChunkCount();

        if (empty($remoteFileSize)) {
            echo "Could not check remote file size." . PHP_EOL;
            return $download;
        }

        // If the chunk count is larger than the file size, we probably only need one chunk
        if ($remoteFileSize <= $chunkCount) {
            $download->setChunkCount(1);
            return $download;
        }

        $downloadSize = self::getDownloadSize($download);

        // HTTP GET will handle a range that starts inside the Acceptable-Range and extends beyond it.
        if ($downloadSize > $remoteFileSize) {
            $maxChunkSize = (int) ceil($remoteFileSize / $chunkCount);
            $download->setChunkSize($maxChunkSize);
        }

        return $download;
    }

    /**
     * Send a HEAD request to get the remote file size
     *
     * @param Download $download
     * @throws \Exception
     * @return int|null The size of the remote file, null if response did not include content-length
     */
    private static function getRemoteFileSize(Download $download):? int
    {
        $head = curl_init($download->getUrl());

        //Create a "HEAD" request
        curl_setopt_array($head, [
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6
        ]);

        if (!$result = curl_exec($head)) {
            throw new \Exception('Could not connect to url.');
        }
        if (preg_match('/content-length: (\d+)/i', $result, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Return the product of the chunk count and chunk size
     *
     * @param Download $download
     * @return int The number of bytes that will be requested from the remote server
     */
    private static function getDownloadSize(Download $download): int
    {
        return $download->getChunkCount() * $download->getChunkSize();
    }

    /**
     * Do not instantiate this class.
     */
    private function __construct()
    {
    }
}