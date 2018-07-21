# download-booster

Download Booster downloads the first 4 MiB of the specified URL to the current directory. It downloads the file in 
chunks, stitches the chunks back together, and writes them to disk. It should be noted that this library cannot be used
in a webserver environment. If that functionality is needed, HHVM/AMPHP should be used and a suitable ChunkDownloader
will need to be implemented.

## Definitions

One MiB (Mebibyte) is equal to 1,048,576 bytes. 

## Namespace

The library is provided via the DownloadBooster namespace.

## 

## Third Party Libraries

These are the open-source libraries used in this project.

### Requests

Used for making http requests a bit more beautiful. Could easily be replaced by cURL, but wouldn't look as good.