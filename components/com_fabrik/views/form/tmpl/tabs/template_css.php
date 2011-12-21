<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "
#{$view}_$c .fabrikElement {
    margin-left: 10px;
}

#{$view}_$c dd ul{
	padding:0;
	list-style:none;
	margin:0;
}

#{$view}_$c .fabrikForm .fabrikGroup ul .fabrikElementContainer,
#{$view}_$c .fabrikElementContainer{
	padding:5px 10px;
	margin-top:10px;
	background:none !important;
	display:-webkit-box;
	display:-moz-box;
	display:box;
	overflow:visible;
	width:50%;
}

#{$view}_$c .fabrikSubElementContainer {
    margin-left: 100px;
}

#{$view}_$c .fabrikLabel {
    width: 100px;
    clear: left;
    float: left;
}

#{$view}_$c .fabrikActions {
    padding-top: 15px;
    clear: left;
    padding-bottom: 15px;
}

#{$view}_$c .fabrikGroupRepeater {
    float: left;
    width: 19%;
}

/** used by password element */
#{$view}_$c .fabrikSubLabel {
    margin-left: -10px;
    clear: left;
    margin-top: 10px;
    float: left;
}

#{$view}_$c .fabrikSubElement {
    display: block;
    margin-top: 10px;
    margin-left: 100px;
}

/* tabs */
#{$view}_$c dl.tabs {
    float: left;
    margin: 10px 0 -1px 0;
    z-index: 50;
}

#{$view}_$c dl.tabs dt {
    float: left;
    padding: 4px 10px;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
    border-top: 1px solid #ccc;
    margin-left: 3px;
    background: #f0f0f0;
    color: #666;
}

#{$view}_$c dl.tabs dt.open {
    background: #F9F9F9;
    border-bottom: 1px solid #F9F9F9;
    z-index: 100;
    color: #000;
}

#{$view}_$c div.current {
    clear: both;
    border: 1px solid #ccc;
    padding: 10px 10px;
}

#{$view}_$c div.current dd {
    padding: 0;
    margin: 0;
}

#{$view}_$c dd {
    border: 1px solid transparent !important;
}
";?>
