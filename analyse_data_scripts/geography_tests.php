<?php
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_geography_new.csv';

$tests = array("recipient-country","recipient-region");
$geography = geography($dir);
  //echo $geography["no_activities"] . PHP_EOL;
  print_r($geography["no_activities"]);
  print_r($geography["activities_with_recipient_region_only"]);
  print_r($geography["activities_with_recipient_country_only"]);
  echo count($geography["activities_with_both"]) . PHP_EOL;
  print_r($geography["activities_with_more_than_one_recipient_region"]);
  print_r($geography["activities_with_more_than_one_recipient_country"]);
  echo count($geography["multiple_region_fail"]) . PHP_EOL;
  echo count($geography["multiple_country_fail"]) . PHP_EOL;
  //echo "No. Activities," . $geography["no_activities"][0] . ","  . $geography["no_activities"][1] . ","  . $geography["no_activities"][2] . "\n";
  //die;

//Open the file to write
$fh = fopen($output_file, 'w') or die("can't open file");
    foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,",Hierarchy " . $hierarchy);
    }
    fwrite($fh,"\n");

    fwrite($fh,"No. Activities");
    foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,"," . $geography["no_activities"][$hierarchy]);
    }
    fwrite($fh,"\n");
    
    fwrite($fh,"No. Activities with both region and country");
    foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,"," . count($geography["activities_with_both"][$hierarchy]));
    }
    fwrite($fh,"\n");
    
    fwrite($fh,"No. Activities with at least one region or country");
    foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,"," . ($geography["activities_with_recipient_country_only"][$hierarchy] + $geography["activities_with_recipient_region_only"][$hierarchy] + count($geography["activities_with_both"][$hierarchy])));
      //. $geography["activities_with_recipient_country_only"][$hierarchy] + $geography["activities_with_recipient_region_only"][$hierarchy] + count($geography["activities_with_both"][$hierarchy])
    }
    fwrite($fh,"\n");
   //fwrite($fh,"Region Only,Country Only,Both,>1 region,>1country,Country,,\n");
    
    fwrite($fh,"\n");
    foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,,,,");
    }
    fwrite($fh,"\n");foreach ($geography["hierarchies"] as $hierarchy) {
      fwrite($fh,"Region Only,>1 region,Fail 100%,Country Only,>1country,Fail 100%,");
    }
    fwrite($fh,"\n");
    $row="";
    foreach ($geography["hierarchies"] as $hierarchy) {
    $row .= $geography["activities_with_recipient_region_only"][$hierarchy] . ",";
    $row .= $geography["activities_with_more_than_one_recipient_region"][$hierarchy] . ",";
    $row .= count($geography["multiple_region_fail"][$hierarchy]) . ",";
    
    $row .= $geography["activities_with_recipient_country_only"][$hierarchy] . ",";
    $row .= $geography["activities_with_more_than_one_recipient_country"][$hierarchy] . ",";
    $row .= count($geography["multiple_country_fail"][$hierarchy]) . ",";
    
    
    fwrite($fh,$row);
    $row="";
    }
    fwrite($fh,"\n");

    if (count($geography["activities_with_both"]) > 0 ) {
      fwrite($fh,"\nActivities with both\n");
      fwrite($fh,"File,ID,Hierarchy");
      fwrite($fh,"\n");
      foreach ($geography["hierarchies"] as $hierarchy) {
        foreach ($geography["activities_with_both"][$hierarchy] as $array) {
            fwrite($fh,$array[0] . "," . $array[1] . "," . $hierarchy . "\n");
        }
      }
    }
    if (count($geography["multiple_country_fail"]) > 0 ) {
      fwrite($fh,"\nActivities with multiple countries that don't add up to 100%\n");
      fwrite($fh,"File,ID,Hierarchy\n");
      foreach ($geography["hierarchies"] as $hierarchy) {
        foreach ($geography["multiple_country_fail"][$hierarchy] as $array) {
            fwrite($fh,$array[0] . "," . $array[1] . "," . $hierarchy .  "\n");
        }
      }
    }
    if (count($geography["multiple_region_fail"]) > 0 ) {
      fwrite($fh,"\nActivities with multiple regions that don't add up to 100%\n");
      fwrite($fh,"File,ID,Hierarchy\n");
      foreach ($geography["hierarchies"] as $hierarchy) {
        foreach ($geography["multiple_region_fail"][$hierarchy] as $array) {
            fwrite($fh,$array[0] . "," . $array[1] . "," . $hierarchy .  "\n");
        }
      }
    }
        

    
fclose($fh);


function geography($dir) {
  $no_activities = array();
  $activities_with_recipient_region_only = array();
  $activities_with_recipient_country_only = array();
  $activities_with_both = array();
  $activities_with_more_than_one_recipient_region = array();
  $activities_with_more_than_one_recipient_country = array();
  $multiple_region_fail = array();
  $multiple_country_fail = array();
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
                    $activities = $xml->{"iati-activity"};
                    //print_r($attributes); die;
                    foreach ($activities as $activity) {
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                        if ($hierarchy && $hierarchy !=NULL) {
                          $hierarchy = (string)$activity->attributes()->hierarchy;
                        } else {
                          $hierarchy = 0;
                        }
                        $found_hierarchies[] = $hierarchy; 
                        $no_activities[$hierarchy]++;
                        //Is there a recipient country
                        if ($activity->{'recipient-region'} && !$activity->{'recipient-country'}) {
                          $activities_with_recipient_region_only[$hierarchy]++;
                        }
                        //Is there a recipient region
                        if ($activity->{'recipient-country'} && !$activity->{'recipient-region'}) {
                          $activities_with_recipient_country_only[$hierarchy]++;
                        }
                        
                        if ($activity->{'recipient-region'} && $activity->{'recipient-country'}) {
                          $activities_with_both[$hierarchy][] = array($file,(string)$activity->{'iati-identifier'});
                        }
                        
                        if (count($activity->{'recipient-region'}) > 1) {
                          //then we have more than one region specified.
                          $activities_with_more_than_one_recipient_region[$hierarchy]++;
                          //Do percentages add up to 100? If so store the id.
                          $percentage_total_region = 0;
                          foreach ($activity->{'recipient-region'} as $region) {
                            $percentage = $region->attributes()->percentage;
                            $percentage_total_region += $percentage;
                          }
                          if ($percentage_total_region !=100) {
                            $multiple_region_fail[$hierarchy][] = array($file,(string)$activity->{'iati-identifier'});
                          }
                        }
                        if (count($activity->{'recipient-country'}) > 1) {
                          //then we have more than one country specified.
                          $activities_with_more_than_one_recipient_country[$hierarchy]++;
                          $percentage_total_country = 0;
                          //Do percentages add up to 100? If so store the id.
                          foreach ($activity->{'recipient-country'} as $country) {
                            $percentage = $country->attributes()->percentage;
                            $percentage_total_country += $percentage;
                          }
                          if ($percentage_total_country !=100) {
                            $multiple_country_fail[$hierarchy][] = array($file,(string)$activity->{'iati-identifier'});
                          }
                        }
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  $found_hierarchies = array_unique($found_hierarchies);
  sort($found_hierarchies);
  $results = array("no_activities" => $no_activities,
                  "activities_with_recipient_region_only" => $activities_with_recipient_region_only,
                  "activities_with_recipient_country_only" => $activities_with_recipient_country_only,
                  "activities_with_both" => $activities_with_both,
                  "activities_with_more_than_one_recipient_region" => $activities_with_more_than_one_recipient_region,
                  "activities_with_more_than_one_recipient_country" => $activities_with_more_than_one_recipient_country,
                  "multiple_region_fail" => $multiple_region_fail,
                  "multiple_country_fail" => $multiple_country_fail,
                  "hierarchies" => $found_hierarchies
                  );
  return $results;
}

?>
