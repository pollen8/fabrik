<ul class="list">
	<li class="fabrik_calculations">
		<?php
		foreach ($this->calculations as $cal) {
			echo "<span>";
			echo $cal->calc;
			echo  "</span>";
		}
		?>
	</li>
</ul>