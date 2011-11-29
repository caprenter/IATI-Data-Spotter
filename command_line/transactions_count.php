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
require_once '../functions/xml_child_exists.php';


$results = get_count("../".$dir);
//print_r($results);

echo array_sum($results[0]) . " transactions reported" . PHP_EOL;
echo count($results[1]) . " transactions of 0 value from these files:" .PHP_EOL;
$files = array_unique($results[1]);
foreach ($files as $file) {
  echo $file . PHP_EOL;
}

print_r(array_count_values($results[2]));

function get_count ($dir) {
  //$zero_transactions = 0;
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
            } else {
              //$results[$file] = 'FAIL';
            }
          
        }
    }
  }
  $return = array($results,$zero_transactions,$codes);
  return $return;
}
  





?>





