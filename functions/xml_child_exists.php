<?php
function xml_child_exists($xml, $childpath)
 {
    $result = $xml->xpath($childpath);
    if (count($result)) {
        //echo 'true';
        return true;
    } else {
        //echo 'false';
        return false;
    }
 }
?>
