<?php

include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_sector.csv';

$data = count_elements($dir);

//$tests = array("reporting-org","transaction/transaction-date");
$fh = fopen($output_file, 'w') or die("can't open file");
/*fwrite($fh,"Element,All,,,Hierarchy 1,,,Hierarchy 2,,,\n");
fwrite($fh,",Count,Per Activity,% Activities,Count,Per Activity,% Activities,Count,Per Activity,% Activities");*/

  fwrite($fh,"Element,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,,,");
  }
  fwrite($fh,"\n");
  fwrite($fh,",");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Element Count,Activity Count,% Activities,>1 vocabulary,Activities with more than one of the same vocab,");
  }

$no_activities = $data["count_all_activities"];
//echo $data["number_of_activities"];
//echo $no_activities;
//print_r($data["no-activities-hierarchy"]);die;
foreach ($data["counts"] as $element=>$results) {
  //$no_xml_elements_found = 0;
  //$activities_with_all_hierarchies =0;
  //foreach ($results as $result) {
  //  $no_xml_elements_found += $result["total"];
  ////  $activities_with_all_hierarchies += $result["activities-with"];
 // }

  //$percentage = $activities_with_all_hierarchies*100/$no_activities;
  //$column1 = $element . ',' . $no_xml_elements_found . ',' . $activities_with_all_hierarchies . ',' . round($activities_with_all_hierarchies*100/$no_activities,2);
  $column1 = $element;
  fwrite($fh,"\n" . $column1);
  //at hierarchy 1
  $j=0;
  foreach ( $data["hierarchies"] as $i) {
      if (!isset($results[$i]["activities-with"])) {
        $results[$i]["activities-with"] = 0;
        $results[$i]["total"] = 0;
      } 
      if ($j==0) {
        fwrite($fh,",");
      } 
      $j++;
      //$column = ',' . $results[$i]["total"] . ',' . $results[$i]["activities-with"] . ',' . $results[$i]["without"] . ',' . round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1);
      $column = $results[$i]["total"] . ',';
      $column .= $results[$i]["activities-with"] . ',';
      $column .= round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1). ',';
      $column .= $data["activities_with_more_than_one_vocabulary"][$i] . ',';
      $column .= $data["no_activities_with_more_than_one_of_the_same_vocab"][$i] . ',';
      
      echo $column;
      fwrite($fh,$column);
  }
  //Vocabularies used
  
}
//Vocabularies used
fwrite($fh,"\n");
fwrite($fh,"\n");
fwrite($fh,"Vocabularies used\n");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",");
  }
fwrite($fh,"\n");
foreach ($data["all_vocabs"] as $hierarchy=>$vocabularies) {
  //Get all array values from all arrays at each hierarchy
  foreach ($vocabularies as $vocabulary) {
   $all_values[] = $vocabulary;
 }
}
 $all_values = array_unique($all_values);
 print_r($all_values);
  foreach ($all_values as $value) {
    foreach ($data["hierarchies"] as $hierarchy) {
      if (in_array($value,$data["all_vocabs"][$hierarchy])) {
        fwrite($fh,$value . ",");
      } else {
         fwrite($fh,",");
      }
      
    }
    fwrite($fh,"\n");
  }

//Failing Activities
//echo count($data["failing_activities"]);die;
fwrite($fh,"\n");
fwrite($fh,"\n");
fwrite($fh,"Failing Activities\n,");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",");
  }
fwrite($fh,"\ncount,");

$failing_activities = $data["failing_activities"];
$all_activities = array();
foreach ($data["hierarchies"] as $hierarchy) {
  $activities = array_unique($failing_activities[$hierarchy]);
  $count[] = count($activities);
}
$max = max($count);
//echo $max; die;

foreach ($data["hierarchies"] as $hierarchy) {
    $activities = array_unique($failing_activities[$hierarchy]);
    foreach ($activities as $activity) {
    $new_activities[$hierarchy][] = $activity;
  }
    //print_r($new_activities);die;
    fwrite($fh,count($new_activities[$hierarchy]) . ",");
}

fwrite($fh,"\n,");
//print_r($activities_at);die;
for ($i=0;$i<$max;$i++) {
  echo $i . ",";
  foreach ($data["hierarchies"] as $hierarchy) {
    echo $hierarchy .PHP_EOL;
    if (isset($new_activities[$hierarchy][$i])) {
      fwrite($fh,$new_activities[$hierarchy][$i]. ",");
    } else {
      fwrite($fh,",");
    }
  }
  fwrite($fh,"\n,");
}
fclose($fh);
//print_r($data);die;



function count_elements($dir) {
  $counts = array();
  $activity_count = 0;
  $count_all_activities = 0;
  $vocabularies = array();
  $found_hierarchies = array(); 
  $no_activities = array();
  $activities_with_more_than_one_vocabulary = array();
  $failing_activities = array();
  $no_activities_with_more_than_one_of_the_same_vocab = array();
  $all_vocabs = array();
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
                      //print_r($activity); die;
                      $hierarchy = (string)$activity->attributes()->hierarchy;
                      if ($hierarchy && $hierarchy !=NULL) {
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                      } else {
                        $hierarchy = 0;
                      }
                      $found_hierarchies[] = $hierarchy; 
                      $no_activities[$hierarchy]++;
                      $activity_count++;
                      $test = "sector";
                      $sectors = $activity->sector;
                      //print_r($sectors); die;
                      //print_r($elements);
                      if (count($sectors) > 0) {
                        //$activities_with++;
                        $counts[$test][$hierarchy]["activities-with"]++;
                        $counts[$test][$hierarchy]["total"] += count($sectors);
                      } else {
                        $activities_without++;
                        $counts[$test][$hierarchy]["without"]++;
                        //echo (string)$activity->{'iati-identifier'};
                        //echo $file;
                      }
                      if (!isset($counts[$test][$hierarchy]["without"])) {
                        $counts[$test][$hierarchy]["without"] = 0;
                      }
                      if (count($sectors) > 1) {
                        //We have more than one sector, we want to d more tests!
                        //echo count($sectors); 
                        //$i=0;
                        $vocabularies_in_this_activity = array();
                        $vocabularies = array();
                        foreach ($sectors as $sector) {
                          //$i++;
                          //echo $i .PHP_EOL;
                          
                          $this_vocabulary = (string)$sector->attributes()->vocabulary;
                          if($this_vocabulary == NULL) {
                            $this_vocabulary = "Assume DAC";
                          }
                          //echo $this_vocabulary;
                          //echo (int)$sectors->attributes()->percentage;
                          $vocabularies[$this_vocabulary] += (int)$sector->attributes()->percentage;
                          
                          $vocabularies_in_this_activity[] = $this_vocabulary;
                          if (!in_array($this_vocabulary,$all_vocabs[$hierarchy])) {
                            $all_vocabs[$hierarchy][] = $this_vocabulary;
                          }
                          
                          //print_r($vocabularies);
                          //die;
                        }
                        if (isset($vocabularies_in_this_activity) && $vocabularies_in_this_activity !=NULL) {
                          $unique_vocabs = array_unique($vocabularies_in_this_activity);
                          //print_r($unique_vocabs);die;
                          $no_unique_vocabs = count($unique_vocabs);
                          $no_vocabs = count($vocabularies_in_this_activity);
                          
                          if ($no_unique_vocabs != $no_vocabs) {
                            $no_activities_with_more_than_one_of_the_same_vocab[$hierarchy]++;
                          }
                        }
                        if (count($vocabularies) >1) {
                         $activities_with_more_than_one_vocabulary[$hierarchy]++;
                        }
                        foreach ($vocabularies as $vocabulary=>$percentage) {
                          if ($percentage !=100) {
                            //$failing_activities[$hierarchy][] = array($activity->{'iati-identifier'},$vocabulary);
                            $failing_activities[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                        }
                      }

                      
                    }//end foreach xml
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  //echo count($found_hierarchies);die;
  $found_hierarchies = array_unique($found_hierarchies);
  sort($found_hierarchies);
  //print_r($all_vocabs);die;
  //print_r($failing_activities[1]);die;
  return array("counts"=>$counts,
              "number_of_activities" => $activity_count,
              "count_all_activities" => $count_all_activities, //should be the same as number_of_activities
              "no-activities-hierarchy" => $no_activities,
              "activities_with_more_than_one_vocabulary" => $activities_with_more_than_one_vocabulary,
              "no_activities_with_more_than_one_of_the_same_vocab" => $no_activities_with_more_than_one_of_the_same_vocab,
              "failing_activities" => $failing_activities,
              "hierarchies"=>$found_hierarchies,
              "all_vocabs" => $all_vocabs
              
              );
  //return array("activities-with" => $activities_with,
   //             "activities-without" => $activities_without);
}

?>
