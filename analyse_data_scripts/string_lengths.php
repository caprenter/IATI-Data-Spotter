<?php
//libxml_use_internal_errors ( true );

$dir = '../data/dfid/'; //needs trailing slash
$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

$tests = array( "iati-activity/description",
                "iati-activity/title"
              );

$string_lengths = array(10,100);
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_string.txt";
//echo $data_file; die;
$fh = fopen($data_file, 'w') or die("can't open file");



foreach ($tests as $test) {
  $xpath = $test;

  $data = string_lengths($xpath, $dir, $string_lengths);
  fwrite($fh,$test . "\n");
  echo $test . PHP_EOL;
  
  if ($data) {
    //$types = array_count_values($types);
    //print_r($types);
    ksort($data);
    //print_r($types);
    
    //echo $test[0] . "," .$test[1] . PHP_EOL;
    foreach ($data as $key=>$value) {
      switch ($key) {
        case 0:
            $length = "0";
          break;
        case 1:
          $length = "<" . $string_lengths[0];
          break;
        case 2:
          $length = "<" . $string_lengths[1];
          break;
        case 3:
          $length = ">" . $string_lengths[1];
          break;
        default:
          break;
        }
      fwrite($fh,$length . "," . $value . "\n");
      echo $length . "," . $value . PHP_EOL;
    }
  } else {
    fwrite($fh,"None found\n");
    echo "None found" . PHP_EOL;
  }
}
fclose($fh);


function string_lengths($xpath, $dir, $string_lengths) {
  
  $data = array();
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
             if ($xml = simplexml_load_file($dir . $file)) {
                //print_r($xml);
                $elements = $xml->xpath($xpath);
                //print_r($attributes); die;
                foreach ($elements as $element) {
                  //echo (string)$reporting_org->attributes()->$attribute;
                  $string=(string)$element;
                  $length = strlen($string);
                  switch ($length) {
                    case ($length == 0):
                      $data[0]++;
                      break;
                    case ($length < $string_lengths[0]): //e.g. 10
                     $data[1]++;
                     //echo $string; print_r($data);
                     //die;
                     break;
                    case ($length < $string_lengths[1]): //e.g. 100 
                     $data[2]++;
                     break;
                    case ($length > $string_lengths[1]): //e.g. 100 
                     $data[3]++;
                     break;
                    default:
                     break;
                   }
                  //echo $string; print_r($data); 
                }
                
                //$count = $xml->{'iati-activity'}->{'reporting-org'}->attributes()->ref->count();
                //$filesize = filesize($file);
                //echo $count . ',' . $file . PHP_EOL;
            }
            
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  if (isset($data)) {
    return $data;
  } else {
    return FALSE;
  }
}

?>
