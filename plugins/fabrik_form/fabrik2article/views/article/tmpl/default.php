<?php
// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

/*
$model = $this->getModel();
$css_files = $model->getArticleCss('default');
foreach ($css_files as $css_file) {
	echo '<link rel="stylesheet" href="' . $css_file . '" type="text/css" />';
}
*/

echo "{fabrik view=form_css id=" . $this->form->id . "}";
$form = $this->form;
echo $form->startTag;

foreach ( $this->groups as $group ) {?>
<div class="fabrikGroup" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">
<h3><?php echo $group->title;?></h3>
<?php if ($group->canRepeat) {
    foreach ($group->subgroups as $subgroup) {
    ?>
        <div class="fabrikSubGroup">
            <div class="fabrikSubGroupElements">
                <?php
                $this->elements = $subgroup;
                echo $this->loadTemplate('group');
                ?>
            </div>
            <?php if ($group->editable) { ?>
                <div class="fabrikGroupRepeater">
                    <a class="addGroup" href="#">
                        <img src="components/com_fabrik/views/form/tmpl/default/images/add.png" alt="<?php echo JText::_('Add group');?>" />
                    </a>
                    <a class="deleteGroup" href="#">
                        <img src="components/com_fabrik/views/form/tmpl/default/images/del.png" alt="<?php echo JText::_('Delete group');?>" />
                    </a>
                </div>
            <?php } ?>
            <div style="clear:left;"></div>
        </div>
        <?php
    }
} else {
    $this->elements = $group->elements;
    echo $this->loadTemplate('group');
}?>
<div style="clear:left;"></div>
	</div>
<?php
echo $form->endTag;
}
