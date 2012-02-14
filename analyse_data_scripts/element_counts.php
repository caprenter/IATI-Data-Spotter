<?php
/* Uses xpath to get directly to the elements in the XML with the required attributes
 * We have a function that places each attribute found into an array.
 * We then run an array_count_values on that array to tell us how many of each we have found.
 * Then we output it to a file
*/

SLOW DO NOT USE OR ADAPT FOR THE DIFFICULT COUNTS

//libxml_use_internal_errors ( true );
include ('functions/xml_child_exists.php');


$dir = '../data/dfid/'; //needs trailing slash
$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

$tests = array( "iati-activity",
        "iati-activities",
        "reporting-org",
        "other-identifier",
        "activity-status",
        "activity-date",
        "contact-info",
        "participating-org",
        "sector",
        "default-tied-status",
        "default-flow-type",
        "default-aid-type",
        "default-finance-type",
        "recipient-country",
        "recipient-region",
        "location",
        "coordinates",
        "budget",
        "planned-disbursement",
        "transaction-type",
        "provider-org",
        "receiver-org",
        "document-link",
        "related-activity",
        "conditions",
        "result",
        "indicator", );

//$tests = array( array("budget/period-start","iso-date"),          );

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
      //echo $test . PHP_EOL;
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
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $elements = $xml->xpath($xpath);
                    //print_r($attributes); die;
                    //print_r($elements);
                    $count = count($elements);
                    echo $xpath . "," . $count; 
                    
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
}

?>
