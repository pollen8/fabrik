<?php

require_once('class.polylineEncoder.php');

// read points from file (bristish shoreline from http://facstaff.unca.edu/mcmcclur/GoogleMaps/EncodePolyline/BritishCoastline.html
$points = file('BritishShoreline.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($points as $key => $point)
{
  $points[$key] = explode(',', $point);
}

$encoder = new PolylineEncoder();
$polyline = $encoder->encode($points);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAANE0WF4ORtlpNk94qhyLbixTU4XYMib-DjEpB6gWbEqPTdmn-qhTJDTeEJNLFrmU7IWoTLQxpGVxRqg"
      type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        map.addControl(new GScaleControl());

        map.setCenter(new GLatLng(54.3, -2.23), 4);
        
        var encodedPolyline = new GPolyline.fromEncoded({
          color: "#FF0000",
          weight: 5,
          points: "<?= $polyline->points ?>",
          levels: "<?= $polyline->levels ?>",
          zoomFactor: <?= $polyline->zoomFactor ?>,
          numLevels: <?= $polyline->numLevels ?>
        });
        map.addOverlay(encodedPolyline);
      }
    }

    //]]>
    </script>
  </head>
  <body onload="load()" onunload="GUnload()">
    <div id="map" style="width:500px;height:280px"></div>
  </body>
</html>