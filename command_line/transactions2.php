<?php
//error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(0);
//require_once '../functions/init.php';

//bring in the $available_groups array
require_once("../variables/available_groups.php");
require_once("../variables/transaction_types.php");
require_once("../functions/get_list_of_files.php");

//Filter GET vars
$args = array(
  'group'   => FILTER_SANITIZE_ENCODED,
  'transaction'   => FILTER_SANITIZE_ENCODED
);
$myinputs = filter_input_array(INPUT_GET, $args);


//if (in_array($myinputs['group'],array_keys($available_groups))) {
//Include variables for each group. Use group name for the argument
//e.g. php detect_html.php dfid
//Include variables for each group. Use group name for the argument
//e.g. php string_check.php dfid
require_once '../variables/' .  $_SERVER['argv'][1] . '.php';
require_once '../variables/transaction_types.php';
require_once '../functions/xml_child_exists.php';

$separator = ";";

$transaction_types_keys = array_keys($transaction_types); //See variables/transaction_types.php for the array
echo "Type" . $separator;
echo "Sum of negative transactions" . $separator;
echo "Sum of positive transactions" . $separator;
echo "Difference" .PHP_EOL;
    
    
$transactions = get_transactions ("../" . $dir); //returns an array with all transactions and a sum of all +ve and -ve transactions

foreach ($transaction_types_keys as $type) {

     if (isset($transactions[strtoupper($type)])) {
        $negative_value = $transactions[strtoupper($type)]["negative"];
     } else {
        $negative_value = 0;
     }
     if (isset($transactions[strtoupper($type)])) {
        $postive_value = $transactions[strtoupper($type)]["positive"];
     } else {
        $postive_value = 0;
     }

          
     echo $transaction_types[strtolower($type)] . $separator;
     echo number_format($negative_value) . $separator;
     echo number_format($postive_value) . $separator;
     echo number_format($negative_value + $postive_value);
     echo PHP_EOL;


}


function get_transactions ($dir) {
  //global $transaction_types;
  //$transaction_types_keys = array_keys($transaction_types); //See variables/transaction_types.php for the array
  //echo $type;
  $result = array();
  //$positive = $negative = 0;
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
              foreach ($xml as $activity) {
                  //$id = (string)$activity->{'iati-identifier'};
                  foreach ($activity->{'transaction'} as $transaction) {
                        $type = (string)$transaction->{'transaction-type'}->attributes()->code;
                        //echo 'yes';
                        $value = $transaction->value;
                        if ($value > 0) {
                          $result[$type]["positive"] +=$value;
                        } else {
                          $result[$type]["negative"] +=$value; 
                        }
                         //$date = $transaction->value->attributes()->{'value-date'} . '<br/>';
                         //An iso date can be specified in an attribute
                         //but if not present a string may be cited instead
                        // $date = $transaction->{'transaction-date'}->attributes()->{'iso-date'};
                         // if (empty($date)) {
                        //    $date = $transaction->{'transaction-date'};
                       //   }
                      //  array_push($result, array($id,array($value,$date)));
                     
                       
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
  //print_r($result);
  return $result;
}

function get_filesize ($dir) {
  global $server_path_to_files; //set in variables/server_vars.php
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
           //$path_to_file  = $server_path_to_files .  substr($dir,3) . $file;
           $path_to_file  = $server_path_to_files .  $dir . $file;
           $filesize = filesize($path_to_file);
           //$filesize = filesize($file);
           $filesize = format_bytes($filesize);
           $results[$file] = $filesize;
          
        }
    }
  }
  return $results;
}

?>
