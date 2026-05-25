<?php

function m($name)
{
    $cls = NULL;
    $pats = $nac = '';
    $nas = $name;
    $asq = explode(':', $nas);
    if (count($asq) > 1) {
        $nas = $asq[1];
        $nac = $asq[0];
        $pats = $nac . '/';
        $_pats = '' . ROOT_PATH . '/' . PROJECT . '/model/' . $nac . '/' . $nac . '.php';
        if (file_exists($_pats)) {
            include_once $_pats;
            $class = '' . $nac . 'Model';
            $cls = new $class($nas);
        }
    }
    $class = '' . $nas . 'ClassModel';
    $path = '' . ROOT_PATH . '/' . PROJECT . '/model/' . $pats . '' . $nas . 'Model.php';
    if (file_exists($path)) {
        include_once $path;
        if ($nac != '') {
            $class = $nac . '_' . $class;
        }
        $cls = new $class($nas);
    }
    if ($cls == NULL) {
        $cls = new sModel($nas);
    }
    return $cls;
}
function c($name, $inbo = true, $param1 = '', $param2 = '')
{
    $class = '' . $name . 'Chajian';
    $path = '' . ROOT_PATH . '/include/chajian/' . $class . '.php';
    $cls = NULL;
    if (file_exists($path)) {
        include_once $path;
        if ($inbo) {
            $cls = new $class($param1, $param2);
        }
    }
    return $cls;
}
function import($name, $inbo = true)
{
    $class = '' . $name . 'Class';
    $path = '' . ROOT_PATH . '/include/class/' . $class . '.php';
    $cls = NULL;
    if (file_exists($path)) {
        include_once $path;
        if ($inbo) {
            $cls = new $class();
        }
    }
    return $cls;
}
function getconfig($key, $dev = '')
{
    $a = array();
    if (isset($GLOBALS['config'])) {
        $a = $GLOBALS['config'];
    }
    $s = '';
    if (isset($a[$key])) {
        $s = $a[$key];
    }
    if ($s === '') {
        $s = $dev;
    }
    return $s;
}
function isempt($str)
{
    $bool = false;
    if (($str == '' || $str == NULL || empty($str)) && !is_numeric($str)) {
        $bool = true;
    }
    return $bool;
}
function contain($str, $a)
{
    $bool = false;
    if (!isempt($a) && !isempt($str)) {
        $ad = strpos($str, $a);
        if ($ad > 0 || !is_bool($ad)) {
            $bool = true;
        }
    }
    return $bool;
}
function getheader($key = '')
{
    $arr = array();
    if (function_exists('getallheaders')) {
        $arr = getallheaders();
    }
    if ($key == '') {
        return $arr;
    }
    return arrvalue($arr, $key);
}
function isajax()
{
    if (strtolower(getheader('X-Requested-With')) == 'xmlhttprequest') {
        return true;
    } else {
        return false;
    }
}
function backmsg($msg = '', $demsg = '处理成功', $da = array())
{
    $code = 201;
    if ($msg == '') {
        $msg = $demsg;
        $code = 200;
    }
    showreturn($da, $msg, $code);
}
function returnerror($msg = '', $code = 201, $carr = array())
{
    $carr['msg'] = $msg;
    $carr['code'] = $code;
    $carr['success'] = false;
    $carr['data'] = '';
    return $carr;
}
function returnsuccess($data = array())
{
    $carr['msg'] = '';
    $carr['code'] = 200;
    $carr['success'] = true;
    $carr['data'] = $data;
    return $carr;
}
function showreturn($arr = '', $msg = '', $code = 200)
{
    $callback = @$_GET['callback'];
    $success = true;
    if ($code != 200) {
        $success = false;
    }
    $result = json_encode(array('code' => $code, 'msg' => $msg, 'data' => $arr, 'success' => $success));
    if (!isempt($callback)) {
        echo '' . $callback . '(' . $result . ')';
    } else {
        echo $result;
    }
    exit;
}
function rockerror($errno, $errstr, $err_file = '', $err_line = 0)
{
    $str = "File:" . $err_file . " Line:[{$err_line}] Error: [{$errno}] {$errstr}";
    echo $str;
    exit;
}
function arrvalue($arr, $k, $dev = '')
{
    $val = $dev;
    if (isset($arr[$k])) {
        $val = $arr[$k];
    }
    return $val;
}
function objvalue($arr, $k, $dev = '')
{
    $val = $dev;
    if (isset($arr->{$k})) {
        $val = $arr->{$k};
    }
    return $val;
}
function trimstr($str)
{
    return trim(str_replace(' ', '', $str));
}
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
function getparams($key, $dev = '')
{
    if (PHP_SAPI != 'cli') {
        return arrvalue($_GET, $key, $dev);
    }
    $arr = arrvalue($GLOBALS, 'argv');
    $sss = '';
    if ($arr) {
        for ($i = 2; $i < count($arr); $i++) {
            $str = $arr[$i];
            if (!isempt($str)) {
                $stra = explode('=', $str);
                if ($stra[0] == '-' . $key . '') {
                    $sss = arrvalue($stra, 1);
                    break;
                }
            }
        }
    }
    if (isempt($sss)) {
        $sss = $dev;
    }
    return $sss;
}
function lang($key)
{
    $data = arrvalue($GLOBALS, 'langdata');
    $val = '';
    if (!$data) {
        return $val;
    }
    if (strpos($key, '.') > 0) {
        $skad = explode('.', $key);
        $key1 = $skad[0];
        $key2 = $skad[1];
        $sdat = arrvalue($data[LANG], $key1);
        if ($sdat) {
            $val = arrvalue($sdat, $key2);
        }
    } else {
        $val = arrvalue($data[LANG], $key);
    }
    return $val;
}
