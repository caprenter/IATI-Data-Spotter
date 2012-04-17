<?php
/* Uses xpath to get directly to the elements in the XML with the required attributes
 * We have a function that places each attribute found into an array.
 * We then run an array_count_values on that array to tell us how many of each we have found.
 * Then we output it to a file
*/


//libxml_use_internal_errors ( true );
include ('functions/xml_child_exists.php');


$dir = '../data/dfid/'; //needs trailing slash
$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

$tests = array( array("iati-activity","hierarchy"),
                array("iati-activity","default-currency"),
                array("iati-activity","lang"),
                array("iati-activity/title","lang"),
                array("iati-activity/description","lang"),
                array("reporting-org","type"),
                array("reporting-org","ref"),
                array("activity-status","code"),
                array("activity-date","type"),
                array("activity-date","iso-date"),
                array("participating-org","role"),
                array("participating-org","type"),
                array("participating-org","ref"),
                array("sector","vocabulary"),
                array("sector","code"),
                array("policy-marker","code"),
                array("policy-marker","vocabulary"),
                array("policy-marker","significance"),
                array("default-tied-status","code"),
                array("default-flow-type","code"),
                array("default-aid-type","code"),
                array("default-finance-type","code"),
                array("recipient-country","code"),
                array("recipient-region","code"),
                array("budget","type"),
                array("budget/value","currency"),
                array("budget/period-start","iso-date"),
                array("planned-disbursement/value","currency"),
                array("planned-disbursement/period-start","iso-date"),
                array("transaction/transaction-type","code"),
                array("transaction/value","currency"),
                array("transaction/provider-org","ref"),
                array("transaction/receiver-org","ref"),
                array("transaction/transaction-date","iso-date"),
                array("document-link","format"),
                array("document-link","category"),
                array("related-activity","type"),
                array("conditions","type"),
                array("result","type"),
                array("result/indicator","measure"),
              );

//$tests = array( array("transaction/transaction-type","code"),          );

//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_attributes.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");

    fwrite($fh,"Element,Attribute\n");
    echo "Element,Attribute" . PHP_EOL;
    //Run through each value pair counting
    foreach ($tests as $test) {
      //Special case
      if ($test[0]!="iati-activity") {
        $xpath = "//iati-activity/";
      } else {
        $xpath = "";
      }
      //Get our variables for the count_attributes function
      $xpath .= $test[0];
      $attribute = $test[1];

      $types = count_attributes($xpath, $attribute, $dir);
      
      //Write our results to the file
      fwrite($fh,$test[0] . "," .$test[1] . "\n"); //simple headers about what we are counting this time round
      echo $test[0] . "," .$test[1] . PHP_EOL;
      //print_r($types);
      if ($types) {
        $types = array_count_values($types);
        //print_r($types);
        ksort($types);
        //print_r($types);
        
        //echo $test[0] . "," .$test[1] . PHP_EOL;
        foreach ($types as $key=>$value) {
          fwrite($fh,$key . "," . $value . "\n");
          echo $key . "," . $value . PHP_EOL;
        }
      } else {
        fwrite($fh,"None found\n");
        echo "None found" . PHP_EOL;
      }
    }
    
fclose($fh);


function count_attributes($xpath, $attribute, $dir) {
  
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
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $elements = $xml->xpath($xpath);
                    //print_r($attributes); die;
                    foreach ($elements as $element) {
                      //echo (string)$element->attributes()->$attribute;
                      //Special case: lang needs us to reference the XML namespace
                      if ($attribute == "lang") {
                        $types[]=(string)$element->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                      } elseif ($attribute == "iso-date") {  //For dates we want to process these to just get the year
                        $date = (string)$element->attributes()->$attribute;
                        //This allows us to check the date given in the element instead of the attribute
                        //if ($date==NULL) {
                        //  $date = (string)$element;
                       // }
                       //Special case for dfid cos we know their dates don't process properly
                        //if (strstr($dir,"dfid")) {
                        //  $date=substr($date,0,-1);
                        //}
                        //echo $date; die;
                        $year = date("Y",strtotime($date));
                        $types[] = $year;
                      } else {                 
                        $types[]=(string)$element->attributes()->$attribute;
                      }
                    } //end foreach
                }//end if not organisation file
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
