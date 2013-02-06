<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';

  //Now look for a transaction type variable. See variables/transaction_types.php for the array
 // if ((strlen($myinputs['transaction'])<=2) && in_array(strtolower($myinputs['transaction']),array_keys($transaction_types))) {
 //     $type = strtoupper($myinputs['transaction']);
    
      print('<div id="main-content">');
          $transactions = transactions_integer_check($dir); //returns an array with all transactions that have non-integer values
          if ($transactions == NULL) {
              echo "<h4>Results</h4>";
              echo "<p class=\"tick\">We did not find any non-integer transaction values.</p>";
              echo "<p>This could be because there are no transactions!</p>";
          } else {
            print("
            <table id='table1' class='sortable'>
              <thead>
                <tr>
                  <th><h3>Id</h3></th>
                  <th><h3>Value</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Validate</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            foreach ($transactions as $transaction) {
              print('
                <tr>   
                  <td><a href="' . validator_link($url,$transaction["file"],$transaction["id"]) .'">' . $transaction["id"] . '</a></td>
                  <td>' . $transaction["value"] . '</td>
                  <td><a href="' . $url . $transaction["file"] .'">' . $transaction["file"] .'</a></td>
                  <td><a href="' . validator_link($url,$transaction["file"]) . '">Validator</a></td>
                </tr>
              ');
            }
            print("</tbody>
                </table>");
          }


    print("</div>");//main content
}              


function transactions_integer_check ($dir) {
  //echo $type;
  $result = array();
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
              foreach ($xml as $activity) {
                  $id = (string)$activity->{'iati-identifier'};
                  foreach ($activity->{'transaction'} as $transaction) {
                    $value = (string)$transaction->value;
                    //echo $value . '<br/>';
                    $integer = (int)$value;
                    //echo $integer.'<br/>';
                    if ((string)$value != (string)$integer) {
                      $result[] = array("id" => $id,
                                        "file" => $file,
                                        "value" => $value
                                        );
                    }
                    //die;
                  }
               }
             }
            //$results[$file] = $count;
          } else {
            //$results[$file] = 'FAIL';
          }
        }
    }
  }
  return $result;
}
?>
