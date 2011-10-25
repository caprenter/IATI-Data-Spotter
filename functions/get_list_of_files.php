<?php
  function get_list_of_files ($dir) {
    if ($handle = opendir($dir)) {
        //echo "Directory handle: $handle\n";
        //echo "Files:\n";
        /* This is the correct way to loop over the directory. */
        $files = array();
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") { //ignore these system files
                array_push($files,$file);
                //print('<li><a href="' . $_SERVER['DOCUMENT_ROOT'] . 'processes/' . $file . 'process.php?group=' . $myinputs['group'] . '">'  . ucwords($file) .  '</a></li>');
                
            }// end if file is not a system file
        } //end while
        closedir($handle);
    }
    return $files;
  }
?>
