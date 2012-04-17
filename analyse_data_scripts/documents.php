<?php

include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_document.csv';

//Look up data for the DocumentCategory.csv code list
$list = "DocumentCategory.csv";
if (($handle = fopen("helpers/code_lists/" . $list, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',','"')) !== FALSE) {
            $codes[$data[0]] = $data[1];
        }
        fclose($handle);
}
//print_r($codes);die;
$data = count_elements($dir);

//$tests = array("reporting-org","transaction/transaction-date");
$fh = fopen($output_file, 'w') or die("can't open file");
/*fwrite($fh,"Element,All,,,Hierarchy 1,,,Hierarchy 2,,,\n");
fwrite($fh,",Count,Per Activity,% Activities,Count,Per Activity,% Activities,Count,Per Activity,% Activities");*/

  fwrite($fh,"Element,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,");
  }
  fwrite($fh,"\n");
  fwrite($fh,",");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Element Count,Activity Count,");
  }


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
      //$column .= round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1). ',';
      //$column .= $data["activities_with_more_than_one_category"][$i] . ',';
      //$column .= $data["no_activities_with_more_than_one_of_the_same_vocab"][$i] . ',';
      
      //echo $column;
      fwrite($fh,$column);
  }
  //categories used
  
}
//categories used
fwrite($fh,"\n");


fwrite($fh,"\n");

fwrite($fh,"Categories:\n,,");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,");
  }
fwrite($fh,"\n");
fwrite($fh,"Category,Look up value,");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Count,No. Activities,");
  }
fwrite($fh,"\n");
foreach ($data["activities_with_category"] as $hierarchy=>$categories) {
  //Get all array values from all arrays at each hierarchy
  foreach ($categories as $category=>$activities) {
    //echo($category);die;
   $all_values[] = $category;
 }
}
 $all_values = array_unique($all_values);
 sort($all_values);
 //print_r($all_values);die;
  foreach ($all_values as $value) {
    fwrite($fh,$value . ",");
    fwrite($fh,'"' . code_lookup($value)  . '",');//insert quotes around the lookup values incase they includ commas
    foreach ($data["hierarchies"] as $hierarchy) {
      if (isset($data["activities_with_category"][$hierarchy][$value])) {
        
        fwrite($fh,count($data["activities_with_category"][$hierarchy][$value]) . ",");
        fwrite($fh,count(array_unique($data["activities_with_category"][$hierarchy][$value])) . ",");

      } else {
         fwrite($fh,",");
      }
      
    }
    fwrite($fh,"\n");
  }
die;
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
  $categories = array();
  $found_hierarchies = array(); 
  $no_activities = array();
  $activities_with_more_than_one_category = array();
  $failing_activities = array();
  $no_activities_with_more_than_one_of_the_same_vocab = array();
  $all_categories = array();
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
                      
                      $test = "document-link";
                      $documents = $activity->{'document-link'};

                      //print_r($documents); die;
                      //print_r($elements);
                      if (count($documents) > 0) {
                        //echo $file;die;
                        //$activities_with++;
                        //Count activites with documents && number of documents
                        $counts[$test][$hierarchy]["activities-with"]++;
                        $counts[$test][$hierarchy]["total"] += count($documents);
                        
                        //Store categories per activity
                        foreach ($documents as $document) {
                        //print_r($document->category->attributes());
                          $this_category = (string)$document->category->attributes()->code;
                          if($this_category == NULL) {
                            $this_category = "Empty";
                          }
                          $activities_with_category[$hierarchy][$this_category][] = (string)$activity->{'iati-identifier'};
                        }
                      } else {
                        $activities_without++;
                        $counts[$test][$hierarchy]["without"]++;
                        //echo (string)$activity->{'iati-identifier'};
                        //echo $file;
                      }
                      if (!isset($counts[$test][$hierarchy]["without"])) {
                        $counts[$test][$hierarchy]["without"] = 0;
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
  //print_r($all_categories);die;
  //print_r($failing_activities[1]);die;
  //print_r($counts);die;
  //print_r($activities_with_category);die;
  return array("counts"=>$counts,
              //"number_of_activities" => $activity_count,
              //"count_all_activities" => $count_all_activities, //should be the same as number_of_activities
              //"no-activities-hierarchy" => $no_activities,
              "activities_with_category" => $activities_with_category,
              "hierarchies"=>$found_hierarchies,              
              );
  //return array("activities-with" => $activities_with,
   //             "activities-without" => $activities_without);
}

function code_lookup($term) {
  global $codes;
  
  if (array_key_exists($term,$codes)) {
   return $codes[$term];
  } else {
    return $term;
  }

}

?>
