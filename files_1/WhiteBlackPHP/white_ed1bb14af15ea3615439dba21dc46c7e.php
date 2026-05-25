<?php

if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_MBSTRING')) {
    define('SMARTY_MBSTRING', function_exists('mb_get_info'));
}
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
    define('SMARTY_RESOURCE_CHAR_SET', SMARTY_MBSTRING ? 'UTF-8' : 'ISO-8859-1');
}
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
    define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
}
if (!class_exists('Smarty_Autoloader')) {
    include dirname(__FILE__) . '/bootstrap.php';
}
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_data.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_extension_handler.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_templatebase.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_template.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_resource.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_variable.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_source.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_resource_base.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_resource_file.php';
class Smarty extends Smarty_Internal_TemplateBase
{
    const SMARTY_VERSION = '3.1.39';
    const SCOPE_LOCAL = 1;
    const SCOPE_PARENT = 2;
    const SCOPE_TPL_ROOT = 4;
    const SCOPE_ROOT = 8;
    const SCOPE_SMARTY = 16;
    const SCOPE_GLOBAL = 32;
    const CACHING_OFF = 0;
    const CACHING_LIFETIME_CURRENT = 1;
    const CACHING_LIFETIME_SAVED = 2;
    const CLEAR_EXPIRED = -1;
    const COMPILECHECK_OFF = 0;
    const COMPILECHECK_ON = 1;
    const COMPILECHECK_CACHEMISS = 2;
    const DEBUG_OFF = 0;
    const DEBUG_ON = 1;
    const DEBUG_INDIVIDUAL = 2;
    const PHP_PASSTHRU = 0;
    const PHP_QUOTE = 1;
    const PHP_REMOVE = 2;
    const PHP_ALLOW = 3;
    const FILTER_POST = 'post';
    const FILTER_PRE = 'pre';
    const FILTER_OUTPUT = 'output';
    const FILTER_VARIABLE = 'variable';
    const PLUGIN_FUNCTION = 'function';
    const PLUGIN_BLOCK = 'block';
    const PLUGIN_COMPILER = 'compiler';
    const PLUGIN_MODIFIER = 'modifier';
    const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';
    public static $global_tpl_vars = array();
    public static $_MBSTRING = SMARTY_MBSTRING;
    public static $_CHARSET = SMARTY_RESOURCE_CHAR_SET;
    public static $_DATE_FORMAT = SMARTY_RESOURCE_DATE_FORMAT;
    public static $_UTF8_MODIFIER = 'u';
    public static $_IS_WINDOWS = false;
    public $auto_literal = true;
    public $error_unassigned = false;
    public $use_include_path = false;
    public $_templateDirNormalized = false;
    public $_joined_template_dir = null;
    public $_configDirNormalized = false;
    public $_joined_config_dir = null;
    public $default_template_handler_func = null;
    public $default_config_handler_func = null;
    public $default_plugin_handler_func = null;
    public $_compileDirNormalized = false;
    public $_pluginsDirNormalized = false;
    public $_cacheDirNormalized = false;
    public $force_compile = false;
    public $use_sub_dirs = false;
    public $allow_ambiguous_resources = false;
    public $merge_compiled_includes = false;
    public $extends_recursion = true;
    public $force_cache = false;
    public $left_delimiter = "{";
    public $right_delimiter = "}";
    public $literals = array();
    public $security_class = 'Smarty_Security';
    public $security_policy = null;
    public $php_handling = self::PHP_PASSTHRU;
    public $allow_php_templates = false;
    public $debugging = false;
    public $debugging_ctrl = 'NONE';
    public $smarty_debug_id = 'SMARTY_DEBUG';
    public $debug_tpl = null;
    public $error_reporting = null;
    public $config_overwrite = true;
    public $config_booleanize = true;
    public $config_read_hidden = false;
    public $compile_locking = true;
    public $cache_locking = false;
    public $locking_timeout = 10;
    public $default_resource_type = 'file';
    public $caching_type = 'file';
    public $default_config_type = 'file';
    public $cache_modified_check = false;
    public $registered_plugins = array();
    public $registered_objects = array();
    public $registered_classes = array();
    public $registered_filters = array();
    public $registered_resources = array();
    public $registered_cache_resources = array();
    public $autoload_filters = array();
    public $default_modifiers = array();
    public $escape_html = false;
    public $start_time = 0;
    public $_current_file = null;
    public $_parserdebug = false;
    public $_objType = 1;
    public $_debug = null;
    protected $template_dir = array('./templates/');
    protected $_processedTemplateDir = array();
    protected $config_dir = array('./configs/');
    protected $_processedConfigDir = array();
    protected $compile_dir = './templates_c/';
    protected $plugins_dir = array();
    protected $cache_dir = './cache/';
    protected $obsoleteProperties = array('resource_caching', 'template_resource_caching', 'direct_access_security', '_dir_perms', '_file_perms', 'plugin_search_order', 'inheritance_merge_compiled_includes', 'resource_cache_mode');
    protected $accessMap = array('template_dir' => 'TemplateDir', 'config_dir' => 'ConfigDir', 'plugins_dir' => 'PluginsDir', 'compile_dir' => 'CompileDir', 'cache_dir' => 'CacheDir');
    public function __construct()
    {
        $this->_clearTemplateCache();
        parent::__construct();
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(Smarty::$_CHARSET);
        }
        $this->start_time = microtime(true);
        if (isset($_SERVER['SCRIPT_NAME'])) {
            Smarty::$global_tpl_vars['SCRIPT_NAME'] = new Smarty_Variable($_SERVER['SCRIPT_NAME']);
        }
        Smarty::$_IS_WINDOWS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        if (Smarty::$_CHARSET !== 'UTF-8') {
            Smarty::$_UTF8_MODIFIER = '';
        }
    }
    public static function muteExpectedErrors()
    {
        return Smarty_Internal_ErrorHandler::muteExpectedErrors();
    }
    public static function unmuteExpectedErrors()
    {
        restore_error_handler();
    }
    public function templateExists($resource_name)
    {
        $source = Smarty_Template_Source::load(null, $this, $resource_name);
        return $source->exists;
    }
    public function enableSecurity($security_class = null)
    {
        Smarty_Security::enableSecurity($this, $security_class);
        return $this;
    }
    public function disableSecurity()
    {
        $this->security_policy = null;
        return $this;
    }
    public function addTemplateDir($template_dir, $key = null, $isConfig = false)
    {
        if ($isConfig) {
            $processed =& $this->_processedConfigDir;
            $dir =& $this->config_dir;
            $this->_configDirNormalized = false;
        } else {
            $processed =& $this->_processedTemplateDir;
            $dir =& $this->template_dir;
            $this->_templateDirNormalized = false;
        }
        if (is_array($template_dir)) {
            foreach ($template_dir as $k => $v) {
                if (is_int($k)) {
                    $dir[] = $v;
                } else {
                    $dir[$k] = $v;
                    unset($processed[$key]);
                }
            }
        } else {
            if ($key !== null) {
                $dir[$key] = $template_dir;
                unset($processed[$key]);
            } else {
                $dir[] = $template_dir;
            }
        }
        return $this;
    }
    public function getTemplateDir($index = null, $isConfig = false)
    {
        if ($isConfig) {
            $dir =& $this->config_dir;
        } else {
            $dir =& $this->template_dir;
        }
        if ($isConfig ? !$this->_configDirNormalized : !$this->_templateDirNormalized) {
            $this->_normalizeTemplateConfig($isConfig);
        }
        if ($index !== null) {
            return isset($dir[$index]) ? $dir[$index] : null;
        }
        return $dir;
    }
    public function setTemplateDir($template_dir, $isConfig = false)
    {
        if ($isConfig) {
            $this->config_dir = array();
            $this->_processedConfigDir = array();
        } else {
            $this->template_dir = array();
            $this->_processedTemplateDir = array();
        }
        $this->addTemplateDir($template_dir, null, $isConfig);
        return $this;
    }
    public function addConfigDir($config_dir, $key = null)
    {
        return $this->addTemplateDir($config_dir, $key, true);
    }
    public function getConfigDir($index = null)
    {
        return $this->getTemplateDir($index, true);
    }
    public function setConfigDir($config_dir)
    {
        return $this->setTemplateDir($config_dir, true);
    }
    public function addPluginsDir($plugins_dir)
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
        }
        $this->plugins_dir = array_merge($this->plugins_dir, (array) $plugins_dir);
        $this->_pluginsDirNormalized = false;
        return $this;
    }
    public function getPluginsDir()
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
            $this->_pluginsDirNormalized = false;
        }
        if (!$this->_pluginsDirNormalized) {
            if (!is_array($this->plugins_dir)) {
                $this->plugins_dir = (array) $this->plugins_dir;
            }
            foreach ($this->plugins_dir as $k => $v) {
                $this->plugins_dir[$k] = $this->_realpath(rtrim($v, '/\\') . DIRECTORY_SEPARATOR, true);
            }
            $this->_cache['plugin_files'] = array();
            $this->_pluginsDirNormalized = true;
        }
        return $this->plugins_dir;
    }
    public function setPluginsDir($plugins_dir)
    {
        $this->plugins_dir = (array) $plugins_dir;
        $this->_pluginsDirNormalized = false;
        return $this;
    }
    public function getCompileDir()
    {
        if (!$this->_compileDirNormalized) {
            $this->_normalizeDir('compile_dir', $this->compile_dir);
            $this->_compileDirNormalized = true;
        }
        return $this->compile_dir;
    }
    public function setCompileDir($compile_dir)
    {
        $this->_normalizeDir('compile_dir', $compile_dir);
        $this->_compileDirNormalized = true;
        return $this;
    }
    public function getCacheDir()
    {
        if (!$this->_cacheDirNormalized) {
            $this->_normalizeDir('cache_dir', $this->cache_dir);
            $this->_cacheDirNormalized = true;
        }
        return $this->cache_dir;
    }
    public function setCacheDir($cache_dir)
    {
        $this->_normalizeDir('cache_dir', $cache_dir);
        $this->_cacheDirNormalized = true;
        return $this;
    }
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        if ($cache_id !== null && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent !== null && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        if (!$this->_templateDirNormalized) {
            $this->_normalizeTemplateConfig(false);
        }
        $_templateId = $this->_getTemplateId($template, $cache_id, $compile_id);
        $tpl = null;
        if ($this->caching && isset(Smarty_Internal_Template::$isCacheTplObj[$_templateId])) {
            $tpl = $do_clone ? clone Smarty_Internal_Template::$isCacheTplObj[$_templateId] : Smarty_Internal_Template::$isCacheTplObj[$_templateId];
            $tpl->inheritance = null;
            $tpl->tpl_vars = $tpl->config_vars = array();
        } elseif (!$do_clone && isset(Smarty_Internal_Template::$tplObjCache[$_templateId])) {
            $tpl = clone Smarty_Internal_Template::$tplObjCache[$_templateId];
            $tpl->inheritance = null;
            $tpl->tpl_vars = $tpl->config_vars = array();
        } else {
            $tpl = new $this->template_class($template, $this, null, $cache_id, $compile_id, null, null);
            $tpl->templateId = $_templateId;
        }
        if ($do_clone) {
            $tpl->smarty = clone $tpl->smarty;
        }
        $tpl->parent = $parent ? $parent : $this;
        if (!empty($data) && is_array($data)) {
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Smarty_Variable($_val);
            }
        }
        if ($this->debugging || $this->debugging_ctrl === 'URL') {
            $tpl->smarty->_debug = new Smarty_Internal_Debug();
            if (!$this->debugging && $this->debugging_ctrl === 'URL') {
                $tpl->smarty->_debug->debugUrl($tpl->smarty);
            }
        }
        return $tpl;
    }
    public function loadPlugin($plugin_name, $check = true)
    {
        return $this->ext->loadPlugin->loadPlugin($this, $plugin_name, $check);
    }
    public function _getTemplateId($template_name, $cache_id = null, $compile_id = null, $caching = null, Smarty_Internal_Template $template = null)
    {
        $template_name = strpos($template_name, ':') === false ? "{$this->default_resource_type}:{$template_name}" : $template_name;
        $cache_id = $cache_id === null ? $this->cache_id : $cache_id;
        $compile_id = $compile_id === null ? $this->compile_id : $compile_id;
        $caching = (int) ($caching === null ? $this->caching : $caching);
        if (isset($template) && strpos($template_name, ':.') !== false || $this->allow_ambiguous_resources) {
            $_templateId = Smarty_Resource::getUniqueTemplateName(isset($template) ? $template : $this, $template_name) . "#{$cache_id}#{$compile_id}#{$caching}";
        } else {
            $_templateId = $this->_joined_template_dir . "#{$template_name}#{$cache_id}#{$compile_id}#{$caching}";
        }
        if (isset($_templateId[150])) {
            $_templateId = sha1($_templateId);
        }
        return $_templateId;
    }
    public function _realpath($path, $realpath = null)
    {
        $nds = array('/' => '\\', '\\' => '/');
        preg_match('%^(?<root>(?:[[:alpha:]]:[\\\\/]|/|[\\\\]{2}[[:alpha:]]+|[[:print:]]{2,}:[/]{2}|[\\\\])?)(?<path>(.*))$%u', $path, $parts);
        $path = $parts['path'];
        if ($parts['root'] === '\\') {
            $parts['root'] = substr(getcwd(), 0, 2) . $parts['root'];
        } else {
            if ($realpath !== null && !$parts['root']) {
                $path = getcwd() . DIRECTORY_SEPARATOR . $path;
            }
        }
        $path = str_replace($nds[DIRECTORY_SEPARATOR], DIRECTORY_SEPARATOR, $path);
        $parts['root'] = str_replace($nds[DIRECTORY_SEPARATOR], DIRECTORY_SEPARATOR, $parts['root']);
        do {
            $path = preg_replace(array('#[\\\\/]{2}#', '#[\\\\/][.][\\\\/]#', '#[\\\\/]([^\\\\/.]+)[\\\\/][.][.][\\\\/]#'), DIRECTORY_SEPARATOR, $path, -1, $count);
        } while ($count > 0);
        return $realpath !== false ? $parts['root'] . $path : str_ireplace(getcwd(), '.', $parts['root'] . $path);
    }
    public function _clearTemplateCache()
    {
        Smarty_Internal_Template::$isCacheTplObj = array();
        Smarty_Internal_Template::$tplObjCache = array();
    }
    public function setUseSubDirs($use_sub_dirs)
    {
        $this->use_sub_dirs = $use_sub_dirs;
    }
    public function setErrorReporting($error_reporting)
    {
        $this->error_reporting = $error_reporting;
    }
    public function setEscapeHtml($escape_html)
    {
        $this->escape_html = $escape_html;
    }
    public function getAutoLiteral()
    {
        return $this->auto_literal;
    }
    public function setAutoLiteral($auto_literal = true)
    {
        $this->auto_literal = $auto_literal;
    }
    public function setForceCompile($force_compile)
    {
        $this->force_compile = $force_compile;
    }
    public function setMergeCompiledIncludes($merge_compiled_includes)
    {
        $this->merge_compiled_includes = $merge_compiled_includes;
    }
    public function getLeftDelimiter()
    {
        return $this->left_delimiter;
    }
    public function setLeftDelimiter($left_delimiter)
    {
        $this->left_delimiter = $left_delimiter;
    }
    public function getRightDelimiter()
    {
        return $this->right_delimiter;
    }
    public function setRightDelimiter($right_delimiter)
    {
        $this->right_delimiter = $right_delimiter;
    }
    public function setDebugging($debugging)
    {
        $this->debugging = $debugging;
    }
    public function setConfigOverwrite($config_overwrite)
    {
        $this->config_overwrite = $config_overwrite;
    }
    public function setConfigBooleanize($config_booleanize)
    {
        $this->config_booleanize = $config_booleanize;
    }
    public function setConfigReadHidden($config_read_hidden)
    {
        $this->config_read_hidden = $config_read_hidden;
    }
    public function setCompileLocking($compile_locking)
    {
        $this->compile_locking = $compile_locking;
    }
    public function setDefaultResourceType($default_resource_type)
    {
        $this->default_resource_type = $default_resource_type;
    }
    public function setCachingType($caching_type)
    {
        $this->caching_type = $caching_type;
    }
    public function testInstall(&$errors = null)
    {
        Smarty_Internal_TestInstall::testInstall($this, $errors);
    }
    public function _getSmartyObj()
    {
        return $this;
    }
    public function __get($name)
    {
        if (isset($this->accessMap[$name])) {
            $method = 'get' . $this->accessMap[$name];
            return $this->{$method}();
        } elseif (isset($this->_cache[$name])) {
            return $this->_cache[$name];
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return null;
        } else {
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
        }
        return null;
    }
    public function __set($name, $value)
    {
        if (isset($this->accessMap[$name])) {
            $method = 'set' . $this->accessMap[$name];
            $this->{$method}($value);
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return;
        } elseif (is_object($value) && method_exists($value, $name)) {
            $this->{$name} = $value;
        } else {
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
        }
    }
    private function _normalizeDir($dirName, $dir)
    {
        $this->{$dirName} = $this->_realpath(rtrim($dir, "/\\") . DIRECTORY_SEPARATOR, true);
        if (class_exists('Smarty_Internal_ErrorHandler', false)) {
            if (!isset(Smarty_Internal_ErrorHandler::$mutedDirectories[$this->{$dirName}])) {
                Smarty_Internal_ErrorHandler::$mutedDirectories[$this->{$dirName}] = null;
            }
        }
    }
    private function _normalizeTemplateConfig($isConfig)
    {
        if ($isConfig) {
            $processed =& $this->_processedConfigDir;
            $dir =& $this->config_dir;
        } else {
            $processed =& $this->_processedTemplateDir;
            $dir =& $this->template_dir;
        }
        if (!is_array($dir)) {
            $dir = (array) $dir;
        }
        foreach ($dir as $k => $v) {
            if (!isset($processed[$k])) {
                $dir[$k] = $v = $this->_realpath(rtrim($v, "/\\") . DIRECTORY_SEPARATOR, true);
                $processed[$k] = true;
            }
        }
        $isConfig ? $this->_configDirNormalized = true : ($this->_templateDirNormalized = true);
        $isConfig ? $this->_joined_config_dir = join('#', $this->config_dir) : ($this->_joined_template_dir = join('#', $this->template_dir));
    }
}
