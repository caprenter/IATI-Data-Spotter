<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';
  require_once 'functions/bad_files_table.php';

  

    
  print('<div id="main-content">');
    $results = get_count($dir);
    //print_r($results);

    if (!$results == NULL) {
          if(!empty($results["results"])) {
            echo "<h4>Count Results</h4>";
            echo array_sum($results["results"]) . " transactions reported" . "<br/>";
          }
          if(!empty($results["codes"])) {
            echo "<h4>By type</h4>";
            $transaction_types = array_count_values($results["codes"]);
            foreach ($transaction_types as $type => $count) {
              echo $type . ": " . $count . "<br/>";
            }
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
  $bad_files = array();
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          
            if ($xml = simplexml_load_file($dir . $file)) {
              
              if(!xml_child_exists($xml, "//iati-organisation")) {//ignore organisation files
                //$count = $xml->count('.//transaction-date'); //php >5.3
                //$count = count($xml->{'iati-activity'}->{'transaction'}); //php < 5.3
                 $result = $xml->xpath("//transaction");
                //print_r($result); die;
                  if (count($result)) {
                    foreach ($result as $value) {
                      if ($value->value == 0 ) {
                        $zero_transactions[] = $file;
                        //echo $file;
                      }
                      $codes[] = (string)$value->{'transaction-type'}->attributes()->code;
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
                  "zeros" =>$zero_transactions,
                  "codes" => $codes,
                  "bad-files" => $bad_files);
  
  return $return;
}

?>

