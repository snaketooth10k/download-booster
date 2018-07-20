# download-booster

Download Booster downloads the first 4 MiB of the specified URL to the current directory. It downloads the file in 
chunks, stitches the chunks back together, and writes them to disk. 

## Definitions

One MiB (Mebibyte) is equal to 1,048,576 bytes. 

## Third Party Libraries

These are the open-source libraries used in this project.

### Requests

Used for making http requests a bit more beautiful. Could easily be replaced by cURL, but wouldn't look as good.