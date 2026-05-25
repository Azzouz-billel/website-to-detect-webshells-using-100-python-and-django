<?php

const _JEXEC = 1;
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}
if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_LIBRARIES . '/cms.php';
require_once JPATH_CONFIGURATION . '/configuration.php';
class Updatecron extends JApplicationCli
{
    public function doExecute()
    {
        $component = JComponentHelper::getComponent('com_installer');
        $params = $component->params;
        $cache_timeout = $params->get('cachetimeout', 6, 'int');
        $cache_timeout = 3600 * $cache_timeout;
        $this->out('Fetching updates...');
        $updater = JUpdater::getInstance();
        $updater->findUpdates(0, $cache_timeout);
        $this->out('Finished fetching updates');
    }
}
JApplicationCli::getInstance('Updatecron')->execute();
