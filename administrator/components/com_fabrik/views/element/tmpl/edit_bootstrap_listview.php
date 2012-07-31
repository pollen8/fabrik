<div class="tab-pane" id="tab-listview">

	<ul class="nav nav-tabs">
		<li class="active">
	    	<a data-toggle="tab" href="#listview-details">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_LIST_SETTINGS_DETAILS'); ?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#listview-icons">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_ICONS_SETTINGS_DETAILS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#listview-filters">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_FILTERS_DETAILS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#listview-css">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_CSS_DETAILS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#listview-calculations">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_CALCULATIONS_DETAILS')?>
	    	</a>
	    </li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="listview-details">
		    <fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('listsettings') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
				<?php foreach ($this->form->getFieldset('listsettings2') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="listview-icons">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('icons') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="listview-filters">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('filters') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
				<?php foreach ($this->form->getFieldset('filters2') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="listview-css">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('viewcss') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="listview-calculations">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('calculations') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>
	</div>
</div>
