<?php

defined('_JEXEC') or die;
if ($displayData->params->get('show_page_heading')) {
    ?>
<h1>
	<?php 
    echo $displayData->escape($displayData->params->get('page_heading'));
    ?>
</h1>
<?php 
}
?>

<?php 
if ($displayData->params->get('show_base_description')) {
    ?>
	<?php 
    
    ?>
	<?php 
    if ($displayData->params->get('categories_description')) {
        ?>
		<div class="category-desc base-desc">
			<?php 
        echo JHtml::_('content.prepare', $displayData->params->get('categories_description'), '', $displayData->get('extension') . '.categories');
        ?>
		</div>
	<?php 
    } else {
        ?>
		<?php 
        
        ?>
		<?php 
        if ($displayData->parent->description) {
            ?>
			<div class="category-desc base-desc">
				<?php 
            echo JHtml::_('content.prepare', $displayData->parent->description, '', $displayData->parent->extension . '.categories');
            ?>
			</div>
		<?php 
        }
        ?>
	<?php 
    }
}
