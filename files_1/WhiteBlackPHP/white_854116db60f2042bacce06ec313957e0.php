<?php

defined('_JEXEC') or die;
JHtml::_('behavior.core');
$doTask = $displayData['doTask'];
$class = $displayData['class'];
$text = $displayData['text'];
$name = $displayData['name'];
?>
<button value="<?php 
echo $doTask;
?>" class="btn btn-small modal" data-toggle="modal" data-target="#modal-<?php 
echo $name;
?>">
	<span class="<?php 
echo $class;
?>" aria-hidden="true"></span>
	<?php 
echo $text;
?>
</button>

