#!/usr/bin/php
<?php 
if (php_sapi_name() != "cli") {
    echo "Error: phptidy has to be run on command line with CLI SAPI\n";
    exit(1);
}
function getDirectory($path = '.', $level = 0)
{
    $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
    $flattened = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
    foreach ($flattened as $path => $dir) {
        if (!$dir->isDir()) {
            continue;
        }
        if (!(file_exists($path . '/index.html') || file_exists($path . '/index.php'))) {
            file_put_contents($path . '/index.html', '<!DOCTYPE html><title></title>
');
        }
    }
}
$work = $_SERVER['argv'][1];
echo "Working on directory " . $work . "\n";
getDirectory($_SERVER['argv'][1]);
