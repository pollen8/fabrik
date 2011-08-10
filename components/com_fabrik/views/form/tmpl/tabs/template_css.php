<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "
#form_$c .fabrikElement {
    margin-left: 10px;
}

#form_$c .fabrikSubElementContainer {
    margin-left: 100px;
}

#form_$c .fabrikLabel {
    width: 100px;
    clear: left;
    float: left;
}

#form_$c .fabrikActions {
    padding-top: 15px;
    clear: left;
    padding-bottom: 15px;
}

#form_$c .fabrikGroupRepeater {
    float: left;
    width: 19%;
}

/** used by password element */
#form_$c .fabrikSubLabel {
    margin-left: -10px;
    clear: left;
    margin-top: 10px;
    float: left;
}

#form_$c .fabrikSubElement {
    display: block;
    margin-top: 10px;
    margin-left: 100px;
}

/* tabs */
#form_$c dl.tabs {
    float: left;
    margin: 10px 0 -1px 0;
    z-index: 50;
}

#form_$c dl.tabs dt {
    float: left;
    padding: 4px 10px;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
    border-top: 1px solid #ccc;
    margin-left: 3px;
    background: #f0f0f0;
    color: #666;
}

#form_$c dl.tabs dt.open {
    background: #F9F9F9;
    border-bottom: 1px solid #F9F9F9;
    z-index: 100;
    color: #000;
}

#form_$c div.current {
    clear: both;
    border: 1px solid #ccc;
    padding: 10px 10px;
}

#form_$c div.current dd {
    padding: 0;
    margin: 0;
}

#form_$c dd {
    border: 1px solid transparent !important;
}
";?>
