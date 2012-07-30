<div class="tab-pane" id="publishing">

	<ul class="nav nav-tabs">
		<li>
	    	<a data-toggle="tab active" href="#publishing-details">
	    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS'); ?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#publishing-rss">
	    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_RSS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#publishing-csv">
	    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_CSV')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#publishing-search">
	    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_SEARCH')?>
	    	</a>
	    </li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane" id="publishing-details">
		    <fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('publishing-details') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="publishing-rss">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('rss') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="publishing-csv">
			<fieldset class="form-horizontal">
				<?php
				foreach ($this->form->getFieldset('csv') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				foreach ($this->form->getFieldset('csvauto') as $this->field) :
				echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="publishing-search">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('search') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>
	</div>
</div>
