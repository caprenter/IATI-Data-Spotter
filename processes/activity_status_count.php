<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';
  require_once 'functions/bad_files_table.php';

  $activity_status_codes = array( "1" => "Pipeline/identification",
                                  "2" => "Implementation",
                                  "3" => "Completion",
                                  "4" => "Post-completion",
                                  "5" => "Cancelled"
                                  );
  
//echo $activity_status_codes[2];die;
    
  print('<div id="main-content">');
    $results = get_count($dir);
    //print_r($results);

    if (!$results == NULL) {
          if(!empty($results["results"])) {
            echo "<h4>Count Results</h4>";
            echo array_sum($results["results"]) . " activity status elements reported" . "<br/>";
          }
          if(!empty($results["codes"])) {
            echo "<h4>By type</h4>";
            sort($results["codes"]);
            $transaction_types = array_count_values($results["codes"]);
            foreach ($transaction_types as $type => $count) {
              echo $type . " (" . $activity_status_codes[$type] . "): " . $count .  "<br/>";
            }
          }
          if(!empty($results["bad-codes"])) {
            echo "<h4>" . count($results["bad-codes"]) . " mismatched codes</h4>";
            print("
            <table id='table' class='sortable'>
              <thead>
                <tr>
                  <th><h3>Code</h3></th>
                  <th><h3>Found</h3></th>
                  <th><h3>Codelist Value</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            foreach ($results["bad-codes"] as $bad) {
              print('
              <tr>
                  <td>' . $bad[0] . '</td>
                  <td>' . $bad[1] . '</td>
                  <td>' . $bad[2] . '</td>
              </tr>'
              );
            }
             
            
             print("</tbody>
            </table>");
            //print_r($results["bad-codes"]);
            
          }
          
          if (count($results["zeros"]) >0 ) {
            echo "<h4>Zero value transactions</h4>";
            echo count($results["zeros"]) . " transactions of 0 value from these files:" . "<br/>";
            print("
            <table id='table' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Validator</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            $files = array_unique($results["zeros"]);
            $i=0;
            foreach ($files as $file) {
              $i++;
              print('
              <tr>
                  <td>' . $i . '</td>
                  <td><a href="' .$url . rawurlencode($file) . '">' . $file . '</a></td>
                  <td><a href="' . validator_link($url,$file) . '">Validator</a></td>
              </tr>'
              );
            }
              print("</tbody>
            </table>");
          }
    } else {
      echo "<h4>Counts</h4><p class=\"cross\">Unable to get data</p>";
    }
    
    //Print a table of failing files
    theme_bad_files($results["bad-files"],$url);
    
  print("</div>");//main content
}              






function get_count ($dir) {
  global $activity_status_codes;
  $bad_files = array();
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          
            if ($xml = simplexml_load_file($dir . $file)) {
              
              if(!xml_child_exists($xml, "//iati-organisation")) {//ignore organisation files
                //$count = $xml->count('.//transaction-date'); //php >5.3
                //$count = count($xml->{'iati-activity'}->{'transaction'}); //php < 5.3
                 $result = $xml->xpath("//activity-status");
                 //echo count($result); die;
                //print_r($result); die;
                  if (count($result)) {
                    foreach ($result as $status) {
                      /*if ($value->value == 0 ) {
                        $zero_transactions[] = $file;
                        //echo $file;
                      }*/
                      $this_code = (string)$status->attributes()->code;
                      //echo (string)$status;  echo $activity_status_codes[$this_code]; die;
                      //Check the text given matches the code supplied
                      if ((string)$status != $activity_status_codes[$this_code]) {
                        //array_push($bad_files,$file);
                        $bad_codes[] = array($this_code, (string)$status, $activity_status_codes[$this_code]);
                      } else {
                        $codes[] = (string)$status->attributes()->code;
                      }
                    }
                      $results[$file] = count($result);
                  }
                }
            } else { //simpleXML failed to load a file
                  array_push($bad_files,$file);
            }
          
        }
    }
  }
  $return = array("results" => $results,
                  "codes" => $codes,
                  "bad-codes" => $bad_codes,
                  "bad-files" => $bad_files);
  
  return $return;
}

?>

