<?php
function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    //for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    for ($i = 0; $size >= 10 && $i < 1; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}
?>
