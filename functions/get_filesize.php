<?php
function get_filesize ($dir) {
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
           $path_to_file  = '/home/david/Webs/aidinfo-2/' .  substr($dir,3) . $file;
           $path_to_file  = '/home/david/Webs/aidinfo-2/' .  $dir . $file;
           $filesize = filesize($path_to_file);
           //$filesize = filesize($file);
           $filesize = format_bytes($filesize);
           $results[$file] = $filesize;
          
        }
    }
  }
  return $results;
}
?>
