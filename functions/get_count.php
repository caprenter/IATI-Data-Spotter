<?php
function get_count ($dir) {
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {;
            $count = $xml->count();
            $results[$file] = $count;
          }
        }
    }
  }
  return $results;
}
?>
