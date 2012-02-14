<?php
/* Uses xpath to get directly to the elements in the XML with the required attributes
 * We have a function that places each attribute found into an array.
 * We then run an array_count_values on that array to tell us how many of each we have found.
 * Then we output it to a file
*/


//libxml_use_internal_errors ( true );


$dir = '../data/dfid/'; //needs trailing slash
$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

$tests = array( "reporting-org",
                "participating-org",
                "transaction/provider-org",
                "transaction/receiver-org",
              );
//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_names.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");

    fwrite($fh,"Element\n");
    echo "Element" . PHP_EOL;
    //Run through each value pair counting
    foreach ($tests as $test) {
      //Special case
      if ($test != "iati-activity") {
        $xpath = "//iati-activity/";
      } else {
        $xpath = "";
      }
      //Get our variables for the count_attributes function
      $xpath .= $test;

      $types = count_attributes($xpath, $dir);
      
      //Write our results to the file
      fwrite($fh,$test . "\n"); //simple headers about what we are counting this time round
      echo $test . PHP_EOL;
      //print_r($types);
      if ($types) {
        $types = array_count_values($types);
        //print_r($types);
        arsort($types);
        //print_r($types);
        
        //echo $test[0] . "," .$test[1] . PHP_EOL;
        foreach ($types as $key=>$value) {
          fwrite($fh,'"' . $key . '";' . $value . "\n");
          echo '"' . $key . '";' . $value . PHP_EOL;
        }
      } else {
        fwrite($fh,"None found\n");
        echo "None found" . PHP_EOL;
      }
    }
    
fclose($fh);


function count_attributes($xpath, $dir) {
  
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
                  //echo (string)$element->attributes()->$attribute;
                  //Special case: lang needs us to reference the XML namespace
                  
                    $types[] = (string)$element;
                    //This allows us to check the date given in the element instead of the attribute
                    //if ($date==NULL) {
                    //  $date = (string)$element;
                   // }
                   //Special case for dfid cos we know their dates don't process properly
                    //if (strstr($dir,"dfid")) {
                    //  $date=substr($date,0,-1);
                    //}
                    //echo $date; die;
                    //$year = date("Y",strtotime($date));
                    //$types[] = $year;

                } //end foreach
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  if (isset($types)) {
    return $types;
  } else {
    return FALSE;
  }
}

?>
