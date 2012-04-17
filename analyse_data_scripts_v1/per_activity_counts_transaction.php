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
//$save_directory = substr($dir,8,-1);
//$data_file = $save_directory . "/" . substr($dir,8,-1) . "_attributes.txt";
//echo $data_file; die;
//Open the file to write
//$fh = fopen($data_file, 'w') or die("can't open file");

    //fwrite($fh,"Element,Attribute\n");
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

      //$types = count_attributes($xpath, $attribute, $dir);
      $types = count_it('transaction-type','code',$dir); die;
      
      
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
    
//fclose($fh);


function count_it($iati_element, $attribute, $dir) {
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";
    $activities_with = $activities_without = 0;
    $no_transaction_types = $no_transactions = 0; 
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
                      //if ((string)$element->attributes()->hierarchy == 2) {
                        $our_node = $element->{$iati_element};
                        if ($iati_element == "transaction-type") {
                          $our_node = $element->transaction;
                        }
                        if (isset($our_node) && count($our_node) > 0) { //something not quite right here
                          //Count the number of activities that have this element at least once
                          $activities_with++;
                          //print_r($our_node);
                          //echo $activities_with;
                          $no_participating_orgs = 0; //set/rest this counter
                          //Loop through each of the elements
                          foreach ($our_node as $transaction) {
                            //print_r($transaction);
                            //Counts number of elements of this type in this activity
                            $no_transactions++;
                            $transaction_types = $transaction->{'transaction-type'};
                            foreach ($transaction_types as $transaction_type) {
                              $no_transaction_types++;
                              //Gives a count of the total number of these elements
                              //$total_participating_orgs++;
                              $roles[] = $transaction_type->attributes()->$attribute;
                              
                              if ($transaction_type->attributes()->$attribute == NULL) {
                                $this_activity_fails = TRUE;
                              }
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
                          $files[(string)$element->{'iati-identifier'}] = $file;
                        }
                      //}//end if hierarchy
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  echo $activities_with . PHP_EOL;
  echo $total_participating_orgs++;
  print_r ($types) . PHP_EOL;
  echo $no_transaction_types . PHP_EOL;
  echo $no_transactions . PHP_EOL;
  echo $fails . PHP_EOL;
  echo $activities_without . PHP_EOL;
  print_r(array_count_values($numbers));
  print_r($files);
  print_r(array_count_values($files));
  
  //if (isset($types)) {
  //  return $types;
  //} else {
  //  return FALSE;
  //}
}

?>
