<?php

defined('_JEXEC') or die;
$name = $displayData;
?>
<div class="toggle-editor btn-toolbar pull-right clearfix">
	<div class="btn-group">
		<a class="btn" href="#"
			onclick="tinyMCE.execCommand('mceToggleEditor', false, '<?php 
echo $name;
?>');return false;"
			title="<?php 
echo JText::_('PLG_TINY_BUTTON_TOGGLE_EDITOR');
?>"
		>
			<span class="icon-eye" aria-hidden="true"></span> <?php 
echo JText::_('PLG_TINY_BUTTON_TOGGLE_EDITOR');
?>
		</a>
	</div>
</div>
