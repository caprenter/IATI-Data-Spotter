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

$tests = array(// "iati-activity",
        //"iati-activities",
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
        "location/location-type",
        "location/name",
        "location/description",
        "location/administrative",
        "location/coordinates",
        "location/gazetteer-entry",
        "budget",
        "budget/period-start",
        "budget/period-end",
        "budget/value",
        "planned-disbursement",
        "planned-disbursement/period-start",
        "planned-disbursement/period-end",
        "planned-disbursement/value",
        "activity-website",
        "collaboration-type",
        "policy-marker",
        "transaction",
        "transaction/value",
        "transaction/description",
        "transaction/transaction-type",
        "transaction/provider-org",
        "transaction/receiver-org",
        "transaction/transaction-date",
        "transaction/flow-type",
        "transaction/aid-type",
        "transaction/finance-type",
        "transaction/tied-status",
        "transaction/disbursement-channel",
        "provider-org",
        "receiver-org",
        "document-link",
        "related-activity",
        "legacy-data",
        "conditions",
        "conditions/condition",
        "result",
        "result/indicator", 
        "result/indicator/baseline",
        "result/indicator/period",
        "result/indicator/period/period-start",
        "result/indicator/period/period-end",
        "result/indicator/period/target",
        "result/indicator/period/actual",
        "contact-info/organisation",
        "contact-info/person-name",
        "contact-info/telephone",
        "contact-info/email",
        "contact-info/mailing-address",
        );

//$tests = array( array("budget/period-start","iso-date"),          );

//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_activities_with_elements.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");
fwrite($fh,"Activities with/without elements\n");

      for ($i=0;$i<3;$i++) {
        if ($i==0) {
          fwrite($fh,"Ignore Hierarchy");
          $counts = count_attributes($dir, $tests);
        } else {
          fwrite($fh,"\nHierarchy=" . $i);
          $counts = count_attributes($dir, $tests, $i);
        }
        //$counts = count_attributes($dir, $tests, 2);
        //echo $xpath . "," . $types[0] . "," . $types[1] . PHP_EOL;
        //print_r($counts);die;
        
        //Write our results to the file
        //fwrite($fh,$test . "\n"); //simple headers about what we are counting this time round
        //echo $test . PHP_EOL;
        //print_r($types);
        if ($counts) {
          //$types = array_count_values($types);
          //print_r($types);
          //ksort($types);
          //print_r($types);
          fwrite($fh," Activities counted=" . $counts[1] ."\n\n");
          //echo $test[0] . "," .$test[1] . PHP_EOL;
          foreach ($counts[0] as $element=>$values) {
            foreach ($values as $key=>$value) {
              fwrite($fh,$element . ": " . $key . "," . $value . "\n");
              echo $element . ": " . $key . "," . $value . PHP_EOL;
            }
          }
        } else {
          fwrite($fh,"None found\n");
          echo "None found" . PHP_EOL;
        }
      } //end for
    
fclose($fh);


function count_attributes($dir, $element_array, $hierarchy = FALSE) {
  $counts = array();
  $activity_count = 0;
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
                    foreach ($xml as $activity) {
                      if ($hierarchy && !((string)$activity->attributes()->hierarchy == $hierarchy)) {
                          //echo "skip";
                          continue 1;
                      }
                      $activity_count++;
                      foreach ($element_array as $test) {
                          $elements = $activity->xpath($test);
                          //print_r($attributes); die;
                          //print_r($elements);
                          if (count($elements) > 0) {
                            //$activities_with++;
                            $counts[$test]["with"]++;
                          } else {
                            //$activities_without++;
                            $counts[$test]["without"]++;
                            //echo (string)$activity->{'iati-identifier'};
                            //echo $file;
                          }
                      } //end foreach tests
                    }//end foreach xml
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  return array($counts,$activity_count);
  
  //return array("activities-with" => $activities_with,
   //             "activities-without" => $activities_without);
}

?>
