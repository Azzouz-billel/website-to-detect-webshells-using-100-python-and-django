<?php

defined('FOF_INCLUDED') or die;
class FOFDownload
{
    private $params = array();
    private $adapter = null;
    private $adapterOptions = array();
    public function __construct()
    {
        $allAdapters = self::getFiles(__DIR__ . '/adapter', array(), array('abstract.php'));
        $priority = 0;
        foreach ($allAdapters as $adapterInfo) {
            if (!class_exists($adapterInfo['classname'], true)) {
                continue;
            }
            $adapter = new $adapterInfo['classname']();
            if (!$adapter->isSupported()) {
                continue;
            }
            if ($adapter->priority > $priority) {
                $this->adapter = $adapter;
                $priority = $adapter->priority;
            }
        }
        FOFPlatform::getInstance()->loadTranslations('lib_f0f');
    }
    public function setAdapter($className)
    {
        $adapter = null;
        if (class_exists($className, true)) {
            $adapter = new $className();
        } elseif (class_exists('FOFDownloadAdapter' . ucfirst($className))) {
            $className = 'FOFDownloadAdapter' . ucfirst($className);
            $adapter = new $className();
        }
        if (is_object($adapter) && $adapter instanceof FOFDownloadInterface) {
            $this->adapter = $adapter;
        }
    }
    public function getAdapterName()
    {
        if (is_object($this->adapter)) {
            $class = get_class($this->adapter);
            return strtolower(str_ireplace('FOFDownloadAdapter', '', $class));
        }
        return '';
    }
    public function setAdapterOptions(array $options)
    {
        $this->adapterOptions = $options;
    }
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }
    private function getParam($key, $default = null)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        } else {
            return $default;
        }
    }
    public function getFromURL($url)
    {
        try {
            return $this->adapter->downloadAndReturn($url, null, null, $this->adapterOptions);
        } catch (Exception $e) {
            return false;
        }
    }
    public function importFromURL($params)
    {
        $this->params = $params;
        $url = $this->getParam('url');
        $localFilename = $this->getParam('localFilename');
        $frag = $this->getParam('frag', -1);
        $totalSize = $this->getParam('totalSize', -1);
        $doneSize = $this->getParam('doneSize', -1);
        $maxExecTime = $this->getParam('maxExecTime', 5);
        $runTimeBias = $this->getParam('runTimeBias', 75);
        $length = $this->getParam('length', 1048576);
        if (empty($localFilename)) {
            $localFilename = basename($url);
            if (strpos($localFilename, '?') !== false) {
                $paramsPos = strpos($localFilename, '?');
                $localFilename = substr($localFilename, 0, $paramsPos - 1);
            }
        }
        $tmpDir = JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp');
        $tmpDir = rtrim($tmpDir, '/\\');
        $retArray = array("status" => true, "error" => '', "frag" => $frag, "totalSize" => $totalSize, "doneSize" => $doneSize, "percent" => 0, "localfile" => $localFilename);
        try {
            $timer = new FOFUtilsTimer($maxExecTime, $runTimeBias);
            $start = $timer->getRunningTime();
            $break = false;
            $local_file = $tmpDir . '/' . $localFilename;
            while ($timer->getTimeLeft() > 0 && !$break) {
                if ($frag == -1) {
                    $doneSize = 0;
                    if (@file_exists($local_file)) {
                        @unlink($local_file);
                    }
                    $fp = @fopen($local_file, 'wb');
                    if ($fp !== false) {
                        @fclose($fp);
                    }
                    $frag = 0;
                    $retArray['totalSize'] = $this->adapter->getFileSize($url);
                    $totalSize = $retArray['totalSize'];
                }
                $from = $frag * $length;
                $to = $length + $from - 1;
                $required_time = 1.0;
                try {
                    $result = $this->adapter->downloadAndReturn($url, $from, $to, $this->adapterOptions);
                    if ($result === false) {
                        throw new Exception(JText::sprintf('LIB_FOF_DOWNLOAD_ERR_COULDNOTDOWNLOADFROMURL', $url), 500);
                    }
                } catch (Exception $e) {
                    $result = false;
                    $error = $e->getMessage();
                }
                if ($result === false) {
                    if ($frag == 0) {
                        $retArray['status'] = false;
                        $retArray['error'] = $error;
                        return $retArray;
                    } else {
                        $frag = -1;
                        $totalSize = $doneSize;
                        $break = true;
                    }
                }
                if ($result) {
                    $filesize = strlen($result);
                    $doneSize += $filesize;
                    $fp = @fopen($local_file, 'ab');
                    if ($fp === false) {
                        $retArray['status'] = false;
                        $retArray['error'] = JText::sprintf('LIB_FOF_DOWNLOAD_ERR_COULDNOTWRITELOCALFILE', $local_file);
                        return $retArray;
                    }
                    fwrite($fp, $result);
                    fclose($fp);
                    $frag++;
                    if ($filesize < $length || $filesize > $length) {
                        $frag = -1;
                        $totalSize = $doneSize;
                        $break = true;
                    }
                }
                $end = $timer->getRunningTime();
                $required_time = max(1.1 * ($end - $start), $required_time);
                if ($required_time > 10 - $end + $start) {
                    $break = true;
                }
                $start = $end;
            }
            if ($frag == -1) {
                $percent = 100;
            } elseif ($doneSize <= 0) {
                $percent = 0;
            } else {
                if ($totalSize > 0) {
                    $percent = 100 * ($doneSize / $totalSize);
                } else {
                    $percent = 0;
                }
            }
            $retArray = array("status" => true, "error" => '', "frag" => $frag, "totalSize" => $totalSize, "doneSize" => $doneSize, "percent" => $percent);
        } catch (Exception $e) {
            $retArray['status'] = false;
            $retArray['error'] = $e->getMessage();
        }
        return $retArray;
    }
    protected static function getFiles($path, array $ignoreFolders = array(), array $ignoreFiles = array())
    {
        $return = array();
        $files = self::scanDirectory($path, $ignoreFolders, $ignoreFiles);
        foreach ($files as $file) {
            $clean = str_replace($path, '', $file);
            $clean = trim(str_replace('\\', '/', $clean), '/');
            $parts = explode('/', $clean);
            $return[] = array('fullpath' => $file, 'classname' => 'FOFDownloadAdapter' . ucfirst(basename($parts[0], '.php')));
        }
        return $return;
    }
    protected static function scanDirectory($path, array $ignoreFolders = array(), array $ignoreFiles = array())
    {
        $return = array();
        $handle = @opendir($path);
        if (!$handle) {
            return $return;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fullpath = $path . '/' . $file;
            if (is_dir($fullpath) && in_array($file, $ignoreFolders) || is_file($fullpath) && in_array($file, $ignoreFiles)) {
                continue;
            }
            if (is_dir($fullpath)) {
                $return = array_merge(self::scanDirectory($fullpath, $ignoreFolders, $ignoreFiles), $return);
            } else {
                $return[] = $path . '/' . $file;
            }
        }
        return $return;
    }
}
