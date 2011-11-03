<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';

  $all_ids = array();
  print('<div id="main-content">');
         print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>Id</h3></th>
                      <th><h3>Date</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validator</h3></th>
                    </tr>
                  </thead>
                  <tbody>");

  if ($handle = opendir($dir)) {
            /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
 
              //load the xml
              if ($xml = simplexml_load_file($dir . $file)) {
                $xml->registerXPathNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
                //$lang = $xml->xpath('//iati-activity/@xml:lang');
                //print_r($lang);

              //print_r($xml); //debug
                  $this_file_ids=array(); //empty array for checking that ids are unique in this file.
                  if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    foreach ($xml as $activity) {
                      //$lang = $activity->xpath('//@xml:lang');
                //print_r($lang);
                //print_r($activity);
                //die;
                        //Push this value into 2 different arrays
                        echo '<tr><td><a href="' . validator_link($url,$file,(string)$activity->{'iati-identifier'}) .'">' . (string)$activity->{'iati-identifier'} . '</a></td>';
                        echo '<td>' . (string)$activity->{'title'} . $activity->attributes()->lang . '</td>';
                        echo '<td><a href="' . $url . $file .'">' . $url . $file .'</a></td>';
                        echo '<td><a href="' . validator_link($url,$file) . '">Validator</td></tr>';
                    } 
                  }
    
                                  
              }
              
          }// end if file is not a system file
      } //end while
      closedir($handle);

 
}
  print("</tbody></table>");
  print('</div>');
}


?>

