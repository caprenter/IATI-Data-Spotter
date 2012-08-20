/*
 *      generate_variable_files.php
 *      
 *      This can be used to mass generate the variable files that need to sit in this directory
 *      The URL line is a bit generic - there may be a better way to generate that.
 */
<?php

include("available_groups.php");
foreach($available_groups as $key=>$value) {
  $file = "./" . $key . ".php";
  //if (!file_exists($file) && $key == "yipl") {
  if (!file_exists($file)) {
    $contents =  "<?php" . PHP_EOL;
    $contents .=  "\$dir = 'data/" . $key . "/'; //needs trailing slash" . PHP_EOL;
    $contents .= "\$url = 'http://bntest.vm.bytemark.co.uk/david/iati/batch/data/';" . PHP_EOL;
    $contents .= "?>";
    echo $key . ":" . $value . PHP_EOL;
    echo $contents . PHP_EOL;
    file_put_contents($key . '.php',$contents);
  }
} 
?>
