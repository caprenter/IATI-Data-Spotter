<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';

  $flag = FALSE;
  $files = array();

    if ($handle = opendir($dir)) {
        //echo "Directory handle: $handle\n";
        //echo "Files:\n";

        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            //if ($file =="IATI_ORG.xml") { //ignore these system files
            if ($file != "." && $file != "..") { //ignore these system files
                //echo $file . PHP_EOL;
                //load the xml
                if ($xml = simplexml_load_file($dir . $file)) {
                    //echo $xml->getName();
                    //print_r($xml); //debug
                    //We're just checking each file to see if it's an organisation file!!
                    // the // allows us to search relative to root
                    if(xml_child_exists($xml, "//iati-organisation"))  {           //$i++;
                        array_push($files,$file);
                        $flag = TRUE;
                    } 
                    
                } else { //simpleXML failed to load a file
                    //echo "failed to load xml";
                }
            }// end if file is not a system file
        } //end while
        closedir($handle);
        print('<div id="main-content">');
        if ($flag) {
          print('<h4>Organisation file:</h4><p class="tick">File found</p>');
          foreach($files as $file) {
              echo '<a href="' .$url . $file . '">' . $file . '</a><br/>';
            }
        } else {
                  print('<h4>No organisation file found </h4>');
        }
        print('</div>');
    }


}
?>
