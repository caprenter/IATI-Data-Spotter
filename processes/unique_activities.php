<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';

  $all_ids = array();
  print('<div id="main-content">');

  if ($handle = opendir($dir)) {
            /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
 
              //load the xml
              if ($xml = simplexml_load_file($dir . $file)) {;
              //print_r($xml); //debug
                  $this_file_ids=array(); //empty array for checking that ids are unique in this file.
                  if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    foreach ($xml as $activity) {
                        //Push this value into 2 different arrays
                        array_push($this_file_ids, (string)$activity->{'iati-identifier'});
                        array_push($all_ids, (string)$activity->{'iati-identifier'});
                    } 
                  }
                  /* Debug
                  print_r($this_file_ids);
                  if (count($this_file_ids) >100 ) {
                      die;
                  }
                  */
                  //Count the numder of items in the array (for just this file)
                  //then run array_unique to reduce any duplicate ids
                  $no_ids = count($this_file_ids);
                  $unique_ids = array_unique($this_file_ids);
                  $flag = FALSE;
                  if (count($unique_ids) != $no_ids) { //flag up any files where the counts don't match
                    if (!$flag) {
                      echo "<h4>Activity files that have duplicate &lt;iati-identifier&gt; tags</h4>";
                      $flag = TRUE;
                    }
                    echo '<a href="' .$url . $file . '">' . $file . '</a> ';
                    echo count($unique_ids) . '/' . $no_ids . '<br/>';
                  } 
                                  
              }
              
          }// end if file is not a system file
      } //end while
      closedir($handle);
      if (!$flag) {
        echo "<h4>Duplicate &lt;iati-identifier&gt; tags within individual activity files</h4><p class='tick'>None found</p>";
      }
      //Count the numder of items in the array (for all files)
      //then run array_unique to reduce any duplicate ids
      
      $no_all_ids = count($all_ids);
      $unique_ids = array_unique($all_ids);
      if (count($unique_ids) != $no_all_ids) {
          //echo "AAaararagh" . $file;
      }
      echo "<h4>Number of activities with unique &lt;iati-identifier&gt; tags: " . count($unique_ids) . "</h4>";
      echo "<h4>Total number of activities found: " . $no_all_ids;
      echo "<h4>Difference: " . " (" . ($no_all_ids - count($unique_ids)) . ")</h4>"; 
      
      //Get some idea of which id's are duplictaed and how many times
      if (($no_all_ids - count($unique_ids)) > 0) {
        theme_toggle_duplicates ($all_ids,$url);
      }
 
}
 print('</div>');
}

function theme_toggle_duplicates ($all_ids,$url) {
    $how_many_of_each = array_count_values ($all_ids);
    arsort($how_many_of_each);
    
    print("<p>
            <a href=\"#\" onclick=\"toggle_visibility('foo');\">Show duplicate activities:</a>
          </p>
          <div id=\"foo\" style=\"display:none;\">");
            foreach ($how_many_of_each as $key => $value) {
                if ($value >1) { 
                  echo $key . '(' . $value . ')' . '<br/>';
                }
            }
    print("</div>");
}
?>
<?php include ("javascript/toggle.js"); ?>
