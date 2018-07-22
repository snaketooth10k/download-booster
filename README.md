# download-booster

Download Booster downloads the first 4 MiB of the specified URL to the current directory. It downloads the file in 
chunks, stitches the chunks back together, and writes them to disk. It should be noted that this library cannot be used
in a webserver environment. If that functionality is needed, HHVM/AMPHP should be used and a suitable ChunkDownloader
will need to be implemented.

## Definitions

One MiB (Mebibyte) is equal to 1,048,576 bytes. 

## Namespace

The library is provided via the DownloadBooster namespace.

The CLI support library is provided via the DownloadBoosterCLI namespace.

## GetOpt Library

The GetOpt library is in use to improve support of command line argument handling. PHP's native getopt function is
somewhat ancient and doesn't provide the feel or consistency of the GetOpt Library. 

## pthreads

pthreads is the standard for POSIX Thread usage in PHP. Unforutunately, it has poor stability. There are two issues that
affect this library: 

- Typehints in the Thread class were not updated for 7.1, meaning a wrapper function needs to be 
used to work around the mismatch.

- The internal mechanics of the pthreads class do not handle class composition well, resulting in unusual errors when 
trying to extract download mechanics into its own class. Since PHP doesn't have multiple inheritance, there is some 
duplication in the parallel and serial ChunkDownloaders.

On top of that, setting up threadsafe PHP with pthreads requires recompilation, and it cannot simply be installed via 
PECL as far as I could tell. 

Because pthreads is spawning new threads to run parallel processes, code coverage reports will not show coverage for
the contents of the run() method. PHPUnit does not currently support parallel processing or testing.

## Extensibility

ChunkDownloaderInterface provides a clean way to extend to different Async/parallel processing libraries. The library
will currently perform chunked downloads serially and in parallel using pthreads, but it should be easy to extend for 
use with HHVM in a webserver setting.

## Command Line Utility

download-booster-cli.php provides the library functionality to the command line. Helpful setup steps:

```bash
chmod +x ./bin/download-booster-cli.php

ln -s ./bin/download-booster-cli.php /usr/local/bin/download-booster

download-booster --help
```

In order to use the parallel option (-p, --parallel), you must be running threadsafe php compiled with pthreads.

### Current Features

- Requires that a url is passed in.

- Defaults to downloading 4 chunks, each 1 MiB in size, for a total of 4 MiB.

- The output file name can be specified, but will default to the filename parsed from the url if not provided.

- Tests demonstrate that file parts are downloaded in correct order, and status checks throw exceptions if errors occur.

- The file is retrieved with GET requests, however, a HEAD request is used to check the size of the file.

- Bonus features:

    - Downloads chunks serially and __in parallel if supported by your installation of PHP__
    
    - Supports files as small as 1 Byte
    
    - Adjustable chunk count and size
    
### Possible Improvements

- Set up with HHVM/AMPHP instead for use in webservers

- Improve memory management by implementing a temporary storage scheme

- Rewrite it in a more parallel/async friendly language like Python

- Implement a Factory to provide the appropriate ChunkDownloader to the DownloadBooster

- Add system/acceptance testing