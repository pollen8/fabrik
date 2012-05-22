<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
//@TODO if we ever get calendars inside packages then the ids will need to be
//replaced with classes contained within a distinct id
FabrikHelperHTML::framework();
FabrikHelperHTML::windows('a.myFabWin');
$document = JFactory::getDocument();
$title = $document->getTitle();
$row = $this->row;
?>
<h1><?php echo $title; ?></h1>
<?php echo $this->loadTemplate('filter'); ?>
<?php 
if(empty( $this->rows )) {	
	echo JText::_('PLG_VIZ_LISTOFEVENTS_NO_DATES_TO_DISPLAY');
	return;
}
?>

	<script type="text/javascript">
		function useIdVenue(idVenue)
		{
			document.getElementById("bottomRightColumn").style.display = "none";
			document.getElementById("middleRightColumn").style.display = "block";
			var url = "index.php?option=com_fabrik&format=raw&view=plugin&task=userAjax&method=displayVenueData&row_id=" + idVenue;
			var venueName = $('venues___name_ro');
			var venueAddress = $('venues___street_ro');
			var venueZipCode = $('venues___zip_code_ro');
			var venueCity = $('venues___city_ro');
			var venueTelephone = $('venues___tel_ro');
			var venueUrl = $('venues___url_ro');
			var venueDescription = $('venues___description_ro');
						
			// console.log( venueCoords );
			// <input type="hidden" value="(50.06465009999999, 19.94497990000002):4" name="venues___map" class="fabrikinput">
			new Request({
				url: url,
				data: {
				method: 'displayVenueData'
				},
				onComplete: function(r){
					var venueData = [ ];
					//var venueMap = $('venues___map_ro');
					//var venueCoords = venueMap.getElementsByTagName( 'input' );
					// console.log( venueCoords );
					venueData = r.split('||');
					venueName.innerHTML = venueData[2];
					venueAddress.innerHTML = venueData[4];
					venueZipCode.innerHTML = venueData[5];
					venueCity.innerHTML = venueData[6];
					venueTelephone.innerHTML = venueData[11];
					venueUrl.innerHTML = '<a href="' + venueData[3] + '" title="' + venueData[2] + '" target="_blank">' + venueData[3] + '</a>';
					venueDescription.innerHTML = venueData[8];
					// venueCoords.setAttribute( "value", venueData[9] + venueData[10] );
					}
				}).send();
		}
	</script>
	
	<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
		<?php if ($this->params->get('show-title', 0)) {?>
			<h1><?php echo $row->label;?></h1>
		<?php } ?>
		<table class="fabrikList">
			<thead>
				<tr class="fabrik___heading">
					<th class="dateEvent"><?php echo JText::_('PLG_VIZ_LISTOFEVENTS_DATE_EVENT'); ?></th>
					<th class="nameEvent"><?php echo JText::_('PLG_VIZ_LISTOFEVENTS_EVENT_NAME')?></th>
					<th class="venueEvent"><?php echo JText::_('PLG_VIZ_LISTOFEVENTS_VENUE_NAME')?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 0;
				foreach ($this->rows as $row) {
					if( !empty( $row )) { ?>
					<tr class="fabrik_row oddRow<?php echo $i%2?>">
						<td><?php echo $row['fromdate']; ?></td>
						<td><?php echo $row['event']; ?></td>
						<td><?php echo $row['venue']; ?></td>
					</tr>
				<?php }
				$i++;
				}?>
			</tbody>
		</table>
	</div>
