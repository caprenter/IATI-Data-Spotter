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
                array("transaction-type","code"),
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

//$tests = array( array("budget/period-start","iso-date"),          );

//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_per_activity_counts.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");


      //Get our variables for the count_attributes function
      $xpath .= $test[0];
      $attribute = $test[1];
      $hierarchy = "not specified";
      $tests = array(array('participating-org','role'),
                     array('activity-date','type')
                    );
      foreach ($tests as $test) {
        $types = count_it($test[0],$test[1],$dir); 
      
      
        //Write our results to the file
        fwrite($fh,"Testing: " . $test[0] . "," .$test[1] . "\n"); //simple headers about what we are counting this time round
        echo $test[0] . "," .$test[1] . PHP_EOL;
        //print_r($types);
        if ($types) {
          
          fwrite($fh,"Out of " . $types["total"] . " activities at level " . $hierarchy . "\n");
          fwrite($fh,"Activities with " . $test[0] . " elements = " . $types["with"] . "\n");
          fwrite($fh,"Activities without " . $test[0] . " elements = " . $types["without"] . "\n");
          fwrite($fh,"Failing activities " . $types["fails"] . "\n");
          foreach ($types["types"] as $key=>$value) {
            fwrite($fh,"Number of activities with a " . $key . " " . $test[0] . " code = " . $value . "\n");
          }

        } else {
          fwrite($fh,"None found\n");
          echo "None found" . PHP_EOL;
        }
      }

fclose($fh);


function count_it($iati_element, $attribute, $dir) {
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";
    $activities_with = $activities_without = 0;
    $no_activities = 0;
    $total_participating_orgs = 0;
    $fails = 0;
    $types=array();
    $roles=array();
    $this_activity_fails = FALSE;
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
             if ($xml = simplexml_load_file($dir . $file)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $elements = $xml->xpath('//iati-activity');
                    //print_r($attributes); die;
                    foreach ($elements as $element) {
                      $no_activities++;
                      $our_node = $element->{$iati_element};
                      if ($iati_element == "transaction-type") {
                        $our_node = $element->transaction->{$iati_element};
                      }
                      if (isset($our_node)) {
                        //Count the number of activities that have this element at least once
                        $activities_with++;
                        //echo $activities_with;
                        $no_participating_orgs = 0; //set/rest this counter
                        //Loop through each of the elements
                        foreach ($our_node as $participating_org) {
                          //Counts number of elements of this type in this activity
                          $no_participating_orgs++;
                          //Gives a count of the total number of these elements
                          //$total_participating_orgs++;
                          $roles[] = $participating_org->attributes()->$attribute;
                          
                          if ($participating_org->attributes()->$attribute == NULL) {
                            $this_activity_fails = TRUE;
                          }
                          
                        }
                        //Gives a count of the total munber of these elements
                        $total_participating_orgs += $no_participating_orgs;
                        //array of all numbers of elements per activity
                        //allows us to pull mean, media and mode
                        $numbers[] = $no_participating_orgs;
                        
                        $roles = array_unique($roles);
                        foreach ($roles as $role) {
                          $types["{$role}"]++;
                        }
                        $roles = array();
                        if ($this_activity_fails) {
                          $fails++;
                          $this_activity_fails = FALSE;
                        }
                      } else {
                        $activities_without++;
                      }
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  return array("with" => $activities_with,
              "total" => $no_activities,
              "types" => $types,
              "fails" => $fails,
              "without" => $activities_without,
              "numbers" => $numbers
              );
  echo $activities_with . PHP_EOL;
  echo $total_participating_orgs++;
  print_r ($types) . PHP_EOL;
  
  echo $fails . PHP_EOL;
  echo $activities_without . PHP_EOL;
  print_r(array_count_values($numbers));
  
  //if (isset($types)) {
  //  return $types;
  //} else {
  //  return FALSE;
  //}
}

?>
