<div class="tab-pane active" id="details">


	<ul class="nav nav-tabs">
		<li>
	    	<a data-toggle="tab active" href="#details-details">
	    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS'); ?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-filters">
	    		<?php echo JText::_('COM_FABRIK_FILTERS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-nav">
	    		<?php echo JText::_('COM_FABRIK_NAVIGATION')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-layout">
	    		<?php echo JText::_('COM_FABRIK_LAYOUT')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-link">
	    		<?php echo JText::_('COM_FABRIK_LINKS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-notes">
	    		<?php echo JText::_('COM_FABRIK_NOTES')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#details-advanced">
	    		<?php echo JText::_('COM_FABRIK_ADVANCED')?>
	    	</a>
	    </li>
	</ul>

	<div class="tab-content">

		<div class="tab-pane active" id="details-details">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('details2') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-filters">
		    <fieldset class="form-horizontal">
				<?php
				foreach ($this->form->getFieldset('main_filter') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				foreach ($this->form->getFieldset('filters') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-nav">
			 <fieldset class="form-horizontal">
				<?php
				foreach ($this->form->getFieldset('main_nav') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				foreach ($this->form->getFieldset('navigation') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-layout">
			 <fieldset class="form-horizontal">
				<?php
				foreach ($this->form->getFieldset('main_template') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				foreach ($this->form->getFieldset('layout') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;

				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-link">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('links') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-notes">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('notes') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="details-advanced">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('advanced') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>
	</div>
</div>