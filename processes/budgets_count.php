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
            echo array_sum($results["results"]) . " budgets reported" . "<br/>";
          }

          if(!empty($results["codes"])) {
            echo "<h4>By type</h4>";
            $budget_types = array_count_values($results["codes"]);
            foreach ($budget_types as $type => $count) {
              echo $type . ": " . $count . "<br/>";
            }
          }
          if(!empty($results["total"])) {
            echo "<h4>Sum of Budgets</h4>";
            echo number_format($results["total"])  . "<br/>";
          }
          
          if ($results["zeros"] !=NULL && count($results["zeros"]) > 0 ) {
            echo "<h4>Zero value budgets</h4>";
            echo count($results["zeros"]) . " budgets of 0 value from these files:" . "<br/>";
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
    }
    
    if ($results['no-budgets']) {
          echo "<h4>Files with no budgets</h4>";
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
          $files = array_unique($results['no-budgets']);
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
 
    
    //Print a table of failing files
    theme_bad_files($results["bad-files"],$url);
    
  print("</div>");//main content
}              






function get_count ($dir) {
  $bad_files = array();
  $total = 0;
  $budgets_in_this_file = 0;
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          
            if ($xml = simplexml_load_file($dir . $file)) {
              
              if(!xml_child_exists($xml, "//iati-organisation")) {//ignore organisation files
                //$count = $xml->count('.//transaction-date'); //php >5.3
                //$count = count($xml->{'iati-activity'}->{'transaction'}); //php < 5.3
                //$default_currency = $xml->attributes->
                //echo $file;
                $activities = $xml->xpath("//iati-activity");
                //print_r($result); die;
                //echo count($activities); die;
                    foreach ($activities as $activity) {
                      //echo count($activites); die;
                      //print_r($value); die;
                      $budgets = $activity->xpath(".//budget");
                     // echo count($budgets); die;
                      if (count($budgets)) {
                        foreach ($budgets as $budget) {
                        //print_r($activity); die;
                          if ($budget->value == 0 ) {
                            $zero_transactions[] = $file;
                            //echo $file;
                          } else {
                            $total += (int)$budget->value; 
                            //echo $total; 
                          }
                          
                          $codes[] = (string)$budget->attributes()->type;
                        }
                        //echo count($codes); die;
                        $budgets_in_this_activity = count($budgets);
                        $budgets_in_this_file += $budgets_in_this_activity;
    
                      } else { //no budgets found
                        //really activities with no budgets
                        $files_with_no_budgets[] = $file;
                      }
                      
                     
                    } //foreach activity
                     $results[$file] = $budgets_in_this_file;
                     $budgets_in_this_file = 0;
                } //if not organisation
            } else { //simpleXML failed to load a file
                  array_push($bad_files,$file);
            }
          
        }
    }
  }
  if (!isset($files_with_no_budgets)) {
      $files_with_no_budgets = NULL;
    }
  if (!isset($zero_transactions)) {
      $zero_transactions = NULL;
    }
  $return = array("results" => $results,
                  "zeros" =>$zero_transactions,
                  "codes" => $codes,
                  "bad-files" => $bad_files,
                  "no-budgets" => $files_with_no_budgets,
                  "total" => $total
                  );
  
  return $return;
}

?>

