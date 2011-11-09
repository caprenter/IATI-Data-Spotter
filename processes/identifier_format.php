<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  $flag = FALSE;
  $inconsistencies = array();
  $bad_files = array();
  print('<div id="main-content">');
  
  include_once('helpers/parse_csv.php');
  if ($handle = opendir($dir)) {

      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
              //echo $file . PHP_EOL;
              //load the xml
              if ($xml = simplexml_load_file($dir . $file)) {;
              //print_r($xml); //debug
                  foreach ($xml as $activity) {
                      //CHECK: Reporting Org is included in iati-identifier string
                      $reporting_org_ref = (string)$activity->{'reporting-org'}->attributes()->ref;
                      //echo $reporting_org_ref;
                      $iati_identifier = (string)$activity->{'iati-identifier'};
                      //echo $iati_identifier;
                      
                      $parts = explode("-", $iati_identifier);
                      if (!$parts[0] == $reporting_org_ref ) {
                       //Report if inconsistent
                        array_push($inconsistencies,array($iati_identifier => $file)); 
                        $flag = TRUE;
                      } 
                  }
                  
                  
              } else { //simpleXML failed to load a file
                  array_push($bad_files,$file);
              }
              
          }// end if file is not a system file
      } //end while
      closedir($handle);
  }
  print('<div id="main-content">');
    if (!$flag) {
      print('<h4>Identifiers (&lt;iati-activity&gt;) are all of the correct form:</h4>
            <p class="tick">&lt;reporting-org@ref&gt; "-" &lt;string&gt;</p>');
    } else {
      print('<h3>Identifiers should be of the form:</h3>
            <p>&lt;reporting-org@ref&gt; "-" &lt;string&gt;</p>
            <p>The following do not conform:</p>');
      print_r($inconsistencies);
    }
    
    //Print a table of failing files
  if ($bad_files != NULL) {
    foreach ($bad_files as $file) {
      $rows .= '<tr><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
    }

    print("
        <table id='fail-table' class='sortable'>
          <thead>
            <tr>
              <th><h3>These files could not be parsed:</h3></th>
            </tr>
          </thead>
          <tbody>
            $rows
          </tbody>
        </table>"
       );
  }
    
  print('</div>');
}
?>
