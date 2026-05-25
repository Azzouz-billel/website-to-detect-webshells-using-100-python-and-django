<?php

class doorGetsApi extends Langue
{
    private $controllerNameNow = 'index';
    public $requestMethod = '';
    public $authorizedMethods = array('GET' => false, 'POST' => false, 'PUT' => false, 'PATCH' => false, 'DELETE' => false);
    public $user = array();
    public $Params = array();
    public $Action = 'index';
    public $Response;
    public $Uri;
    public $Table;
    private $Controller;
    private $View;
    private $Model;
    private $isUserLogged = false;
    public function __construct($controllerName = "index")
    {
        parent::__construct('en');
        $this->user = $this->isValidAccessToken();
        $this->controllerNameNow = $controllerName;
        $this->isUserLogged = $this->user ? true : false;
        $this->loadRequestMethod();
        $this->getParams();
        $this->getController();
        $this->reloadController();
    }
    public function ControllerNameNow()
    {
        return $this->controllerNameNow;
    }
    public function Params()
    {
        return $this->Params;
    }
    public function Action()
    {
        return $this->Action;
    }
    public function setUser()
    {
        return $this->user;
    }
    public function setController($Controller)
    {
        return $this->Controller = $Controller;
    }
    public function setModel($Model)
    {
        return $this->Model = $Model;
    }
    public function setView($View)
    {
        return $this->View = $View;
    }
    public function getUri()
    {
        return $this->doorGets->Uri;
    }
    public function User()
    {
        return $this->User;
    }
    public function Controller()
    {
        return $this->Controller;
    }
    public function Model()
    {
        return $this->Model;
    }
    public function View()
    {
        return $this->View;
    }
    public function genController()
    {
        $nameController = $this->controllerNameNow . 'Controller';
        $fileNameController = CONTROLLERS . 'api/' . $nameController . '.php';
        if (!is_file($fileNameController)) {
            header('Location:./#' . $fileNameController);
            exit;
        }
        require_once $fileNameController;
        if (!class_exists($nameController)) {
            header('Location:./#' . $nameController);
            exit;
        }
        $this->_ControllerObj = new $nameController($this);
        return $this->_ControllerObj;
    }
    public function getParams()
    {
        $HTMLPurifierService = new HTMLPurifierService();
        $GET = $POST = array();
        if (!empty($_GET)) {
            foreach ($_GET as $k => $v) {
                $GET[$k] = filter_input(INPUT_GET, $k, FILTER_SANITIZE_STRING);
            }
        }
        $this->Params['GET'] = $GET;
        if (!empty($_POST)) {
            foreach ($_POST as $k => $v) {
                $rest = substr($k, -8);
                $restHtml = substr($k, -5);
                if ($rest !== '_tinymce' && $restHtml !== '_html') {
                    $POST[$k] = filter_input(INPUT_POST, $k, FILTER_SANITIZE_STRING);
                    
                } else {
                    $v = str_replace('</textarea', '', $v);
                    $v = str_replace('&lt;/textarea', '', $v);
                    $v = str_replace('%3c/textarea', '', $v);
                    $v = str_replace('&#60;/textarea', '', $v);
                    $v = str_replace('<body', '', $v);
                    $v = str_replace('&lt;body', '', $v);
                    $v = str_replace('%3c/body', '', $v);
                    $v = str_replace('&#60;/body', '', $v);
                    $POST[$k] = htmlentities($HTMLPurifierService->purify($v), ENT_QUOTES);
                }
            }
        }
        $this->Params['POST'] = $POST;
        if (array_key_exists('uri', $this->Params['GET'])) {
            $uri = $this->Params['GET']['uri'];
            $isContent = $this->dbQS($uri, '_modules', 'uri');
            if (!empty($isContent)) {
                $this->Table = '_m_' . $this->getRealUri($uri);
                $this->Uri = $uri;
            }
        }
        parse_str(file_get_contents("php://input"), $outPut);
        if (!empty($outPut)) {
            foreach ($outPut as $k => $v) {
                $rest = substr($k, -8);
                $restHtml = substr($k, -5);
                if ($rest !== '_tinymce' && $restHtml !== '_html') {
                    $outPut[$k] = filter_var($v, FILTER_SANITIZE_STRING);
                    
                } else {
                    $v = str_replace('</textarea', '', $v);
                    $v = str_replace('&lt;/textarea', '', $v);
                    $v = str_replace('%3c/textarea', '', $v);
                    $v = str_replace('&#60;/textarea', '', $v);
                    $v = str_replace('<body', '', $v);
                    $v = str_replace('&lt;body', '', $v);
                    $v = str_replace('%3c/body', '', $v);
                    $v = str_replace('&#60;/body', '', $v);
                    $outPut[$k] = htmlentities($HTMLPurifierService->purify($v), ENT_QUOTES);
                }
            }
        }
        $this->Params['PUT'] = $outPut;
    }
    public function getParam($name)
    {
        $value = '';
        $Params = $this->Params;
        if (array_key_exists($name, $Params['GET'])) {
            $value = $Params['GET'][$name];
        }
        if (array_key_exists($name, $Params['POST'])) {
            $value = $Params['POST'][$name];
        }
        return $value;
    }
    public function getController()
    {
        if (array_key_exists('GET', $this->Params) && array_key_exists('controller', $this->Params['GET']) && ctype_alnum($this->Params['GET']['controller'])) {
            $this->controllerNameNow = $this->Params['GET']['controller'];
        }
    }
    public function getAction()
    {
        if (array_key_exists('GET', $this->Params) && array_key_exists('action', $this->Params['GET']) && ctype_alnum($this->Params['GET']['action'])) {
            $this->Action = $this->Params['GET']['action'];
        }
    }
    public function getIsUserLogged()
    {
        return $this->isUserLogged;
    }
    public function setControllerName($name)
    {
        $this->controllerNameNow = $name;
        $this->reloadController();
    }
    public function setAction($name)
    {
        $this->Action = $name;
        $this->reloadController();
    }
    public function reloadController()
    {
        $this->getAction();
        $this->genController();
    }
    public function loadUserInfos()
    {
        if (array_key_exists('doorgets', $_SESSION) && array_key_exists('id', $_SESSION['doorgets']) && array_key_exists('groupe', $_SESSION['doorgets'])) {
            $this->user['id'] = $_SESSION['doorgets']['id'];
            $this->user['groupe'] = $_SESSION['doorgets']['groupe'];
            $this->user['timezone'] = $this->getWebsiteInfoByAttribute('horaire');
            $this->user['langue'] = $this->getWebsiteInfoByAttribute('langue');
        }
    }
    public function isValidAccessToken()
    {
        $hasAccessToken = $isValidAccessToken = false;
        if (array_key_exists('access_token', $_POST)) {
            $hasAccessToken = filter_input(INPUT_POST, 'access_token', FILTER_SANITIZE_STRING);
        } elseif (array_key_exists('access_token', $_GET)) {
            $hasAccessToken = filter_input(INPUT_GET, 'access_token', FILTER_SANITIZE_STRING);
        }
        if ($hasAccessToken) {
            $isAccessToken = $this->dbQS($hasAccessToken, '_users_access_token', 'token');
            if (!empty($isAccessToken) && $isAccessToken['is_valid']) {
                $isUser = $this->getProfileInfos($isAccessToken['id_user']);
                $isValidAccessToken = $isUser;
            }
        }
        return $isValidAccessToken;
    }
    public function loadRequestMethod()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (array_key_exists($requestMethod, $this->authorizedMethods)) {
            $this->requestMethod = $requestMethod;
        }
    }
    public function __destruct()
    {
        parent::__destruct();
    }
}
