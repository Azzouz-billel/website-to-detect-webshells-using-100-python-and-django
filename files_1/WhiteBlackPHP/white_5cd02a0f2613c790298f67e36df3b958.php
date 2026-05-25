<?php

include_once './libraries/lib.inc.php';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (!isset($msg)) {
    $msg = '';
}
function doDefault($msg = '')
{
    global $data, $conf, $misc;
    global $lang;
    $misc->printTrail('schema');
    $misc->printTabs('schema', 'opclasses');
    $misc->printMsg($msg);
    $opclasses = $data->getOpClasses();
    $columns = array('accessmethod' => array('title' => $lang['straccessmethod'], 'field' => field('amname')), 'opclass' => array('title' => $lang['strname'], 'field' => field('opcname')), 'type' => array('title' => $lang['strtype'], 'field' => field('opcintype')), 'default' => array('title' => $lang['strdefault'], 'field' => field('opcdefault'), 'type' => 'yesno'), 'comment' => array('title' => $lang['strcomment'], 'field' => field('opccomment')));
    $actions = array();
    $misc->printTable($opclasses, $columns, $actions, 'opclasses-opclasses', $lang['strnoopclasses']);
}
function doTree()
{
    global $misc, $data;
    $opclasses = $data->getOpClasses();
    $proto = concat(field('opcname'), '/', field('amname'));
    $attrs = array('text' => $proto, 'icon' => 'OperatorClass', 'toolTip' => field('opccomment'));
    $misc->printTree($opclasses, $attrs, 'opclasses');
    exit;
}
if ($action == 'tree') {
    doTree();
}
$misc->printHeader($lang['stropclasses']);
$misc->printBody();
switch ($action) {
    default:
        doDefault();
        break;
}
$misc->printFooter();
