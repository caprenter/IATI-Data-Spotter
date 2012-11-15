<?php

include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_elements.csv';


$tests = array(// "iati-activity",
        //"iati-activities",
        "title",
        "description",
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

//$tests = array("reporting-org","transaction/transaction-date");
$fh = fopen($output_file, 'w') or die("can't open file");
/*fwrite($fh,"Element,All,,,Hierarchy 1,,,Hierarchy 2,,,\n");
fwrite($fh,",Count,Per Activity,% Activities,Count,Per Activity,% Activities,Count,Per Activity,% Activities");*/
fwrite($fh,"Element,Hierarchy 0,,,Hierarchy 1,,,Hierarchy 2,,,\n");
fwrite($fh,",Element Count,Activity Count,% Activities,Element Count,Activity Count,% Activities,Element Count,Activity Count,% Activities");

$data = count_elements($dir, $tests);
$no_activities = $data[2];
foreach ($data[0] as $element=>$results) {
  $no_xml_elements_found = 0;
  $activities_with_all_hierarchies =0;
  foreach ($results as $result) {
    $no_xml_elements_found += $result["total"];
    $activities_with_all_hierarchies += $result["activities-with"];
  }

  //$percentage = $activities_with_all_hierarchies*100/$no_activities;
  //$column1 = $element . ',' . $no_xml_elements_found . ',' . $activities_with_all_hierarchies . ',' . round($activities_with_all_hierarchies*100/$no_activities,2);
  $column1 = $element;
  fwrite($fh,"\n" . $column1);
  //at hierarchy 1
  for ($i=0;$i<3;$i++) {
    if (!isset($results[$i]["activities-with"])) {
      $results[$i]["activities-with"] = 0;
      $results[$i]["total"] = 0;
    }
      //$column = ',' . $results[$i]["total"] . ',' . $results[$i]["activities-with"] . ',' . $results[$i]["without"] . ',' . round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1);
      $column = ',' . $results[$i]["total"] . ',' . $results[$i]["activities-with"] . ',' . round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1);
      
      echo $column;
      fwrite($fh,$column);
  }
  
}
fclose($fh);
print_r($data);die;
/*for ($i=0;$i<3;$i++) {
        if ($i==0) {
          $counts[$i] = count_elements($dir, $tests);
        } else {
          $counts[$i] = count_elements($dir, $tests, $i);
        }
        //print_r($counts[$i]);
}
*/
//print_r($counts[1]);
//die;



/*$fh = fopen($output_file, 'w') or die("can't open file");
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

*/


function count_elements($dir, $element_array) {
  $counts = array();
  $activity_count = 0;
  $count_all_activities = 0;
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
                    $count_all_activities += count($xml->children()); //php < 5.3
                    $activities  = $xml->xpath('//iati-activity');
                    foreach ($activities as $activity) {
                      $hierarchy = (string)$activity->attributes()->hierarchy;
                      if ($hierarchy && $hierarchy !=NULL) {
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                      } else {
                        $hierarchy = 0;
                      }
                      $activity_count++;
                      foreach ($element_array as $test) {
                          $elements = $activity->xpath($test);
                          //print_r($attributes); die;
                          //print_r($elements);
                          if (count($elements) > 0) {
                            //$activities_with++;
                            $counts[$test][$hierarchy]["activities-with"]++;
                            $counts[$test][$hierarchy]["total"] += count($elements);
                          } else {
                            $activities_without++;
                            $counts[$test][$hierarchy]["without"]++;
                            //echo (string)$activity->{'iati-identifier'};
                            //echo $file;
                          }
                          if (!isset($counts[$test][$hierarchy]["without"])) {
                            $counts[$test][$hierarchy]["without"] = 0;
                          }
                      } //end foreach tests
                      
                    }//end foreach xml
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }

  return array($counts,$activity_count,$count_all_activities);
  
  //return array("activities-with" => $activities_with,
   //             "activities-without" => $activities_without);
}

?>
