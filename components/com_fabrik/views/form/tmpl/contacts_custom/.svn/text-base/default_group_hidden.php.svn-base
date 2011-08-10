<?php
/*
 You shouldn't need to edit this.  All you need to do it include this template from
 default_group.php at the end of each group you render in that template.  What it does
 is render all the hidden elements from the group, plus it adds any non-hidden elements which
 have not yet been rendered.
*/
?>

<?php
reset($this->groups);
foreach ($this->groups as $group) {
	foreach ( $group->elements as $element) {
		if ($element->hidden == '1' || !isset($element->rendered)) {
			if ($element->hidden != '1') {
				$element->containerClass .= ' fabrikHide';
			}
			$this->element = $element;
			echo $this->loadTemplate('element');
		}
	}
}
?>

