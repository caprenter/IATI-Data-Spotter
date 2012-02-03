<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';
  require_once 'functions/bad_files_table.php';

  $all_ids = array();
  $bad_files = array();
  $i=0;
  print('<div id="main-content">
            <h4>All Activities</h4>');
         print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>#</h3></th>
                      <th><h3>Id</h3></th>
                      <th><h3>sector</h3></th>
                      <th><h3>@code<br/>def/title</h3></th>
                      <th><h3>@vocabulary</h3></th>
                      <th><h3>@percentage</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validate</h3></th>
                    </tr>
                  </thead>
                  <tbody>");

  if ($handle = opendir($dir)) {
      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
 
              //load the xml
              if ($xml = simplexml_load_file($dir . $file)) {
              //print_r($xml); //debug
                  if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    foreach ($xml as $activity) {
                      $sectors = $activity->xpath('.//sector');
                      //print_r($sectors); //die;
                      foreach ($sectors as $sector) { 
                        $i++;
                          //Push this value into 2 different arrays
                          echo '<tr>';
                          echo '<td>'. $i . '</td>';
                          echo '<td><a href="' . validator_link($url,$file,(string)$activity->{'iati-identifier'}) .'">' . (string)$activity->{'iati-identifier'} . '</a></td>';
                          echo '<td>' . (string)$sector . '</td>';
                          echo '<td>' . (string)$sector->attributes()->code  .'</td>';
                          echo '<td>' . (string)$sector->attributes()->vocabulary . '</td>';
                          echo '<td>' . (string)$sector->attributes()->percentage . '</td>';
                          echo '<td><a href="' . $url . $file .'">' . $file .'</a></td>';
                          echo '<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
                      }
                    } 
                  }
    
                                  
              } else { //simpleXML failed to load a file
                  array_push($bad_files,$file);
              }
              
              
          }// end if file is not a system file
      } //end while
      closedir($handle);
}
  print("</tbody></table>");
  
  //Print a table of failing files
  theme_bad_files($bad_files,$url);
  
  print('</div>');
}
?>

