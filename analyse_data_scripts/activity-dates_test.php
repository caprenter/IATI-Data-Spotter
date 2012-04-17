<?php
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_activity_dates.csv';

/*
 *  <activity-date type="start-actual" iso-date="2010-11-15"/>
 *  <activity-date type="end-planned" iso-date="2011-03-31"/>
*/

$data = count_attributes($dir);
//Open the file to write
$fh = fopen($output_file, 'w') or die("can't open file");
    //Column Headers
    fwrite($fh,",");
    foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,,");
    }
    fwrite($fh,"\n");
    //2nd line of column headers
    fwrite($fh,",");
    $attribute_values = array("start-planned","start-actual","end-planned","end-actual");
    foreach ($data["hierarchies"] as $hierarchy) {
      foreach ($attribute_values as $value) {
        fwrite($fh,$value . ",");
      }
    } 
    fwrite($fh,"\n");
    
    //Activities with each activity-date attribute
    fwrite($fh,"Activities with,");
      
    foreach ($data["hierarchies"] as $hierarchy) {
    //foreach ($data["hierarchies"] as $hierarchy) {
      foreach ($attribute_values as $value) {
        fwrite($fh, count(array_unique($data["activities_with_attribute"][$hierarchy][$value])) . ",");
      }
    }
    //die;
    fwrite($fh,"\n");
    fwrite($fh,"\n");
    
    //Breakdown by year
    fwrite($fh,"Year\n");
    
    foreach($data["activity_by_year"] as $year=>$types) { //loop through years
      //echo $year; die;
      fwrite($fh,$year . ",");
      foreach ($data["hierarchies"] as $hierarchy) { //loop through hierarchies
        foreach ($attribute_values as $value) { //loop through values
          //to get all of the csv line either print the value or a comma
          if (isset($types[$hierarchy][$value])) {
            fwrite($fh,$types[$hierarchy][$value] . ",");
          } else {
            fwrite($fh,",");
          }
        }
      }
      fwrite($fh,"\n");
    }
fclose($fh);



function count_attributes($dir) {
  $no_activity_dates = array();
  $activities_with_at_least_one = array();
  $no_activities = array();
  $found_hierarchies= array();
  $activities_with_attribute = array();
  $activity_by = array();
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
                        
                        $activity_dates = $activity->{"activity-date"};
                        //if (count($activity_dates) > 0) {
                        if ($activity_dates !=NULL) {
                          $activities_with_at_least_one[$hierarchy]++;
                        }
                        foreach ($activity_dates as $activity_date) {
                          //$attributes = array("end-actual","end-planned","start-actual","start-planned");
                          $no_activity_dates[$hierarchy]++;
                          //foreach($attributes as $attribute) {
                          $type = (string)$activity_date->attributes()->type;
                          $date = (string)$activity_date->attributes()->{'iso-date'};
                          //Special Case for DFID
                          //$date = (string)$activity_date;
                          //echo $date; die;
                          $unix_time = strtotime($date);
                          if ($unix_time) {
                            $year = date("Y",strtotime($date));
                          } else {
                            $year = 0; //we could not parse the date, so store the year as 0
                          }
                          $activity_by[$year][$hierarchy][$type]++;
                          $activities_with_attribute[$hierarchy][$type][]=(string)$activity->{'iati-identifier'};
                           
                          //What years was this activity active in??
                          
                        }
                      
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  //if (isset($types)) {
    
    echo "no_activities" . PHP_EOL;
    print_r($no_activities);
    echo "activities_with_at_least_one" . PHP_EOL;
    print_r($activities_with_at_least_one);
    echo "no_activity_dates" . PHP_EOL;
    print_r($no_activity_dates);
    echo "activity_by_year" . PHP_EOL;
    ksort($activity_by);
    print_r($activity_by);
    echo "activities_with_attribute" . PHP_EOL;
    //print_r($activities_with_attribute);
    //foreach($types as $attribute_name=>$attribute) {
    ///  echo $attribute_name;
//foreach($attribute as $hierarchy=>$values) {
     //   echo $hierarchy;
     //   print_r(array_count_values($values));
     // }
   // }
    $found_hierarchies = array_unique($found_hierarchies);
    sort($found_hierarchies);
    //die;
    return array(//"types" => $types,
                  "no-activities" => $no_activities,
                  "activities_with_at_least_one" => $activities_with_at_least_one,
                  "no_activity_dates" => $no_activity_dates,
                  "activity_by_year" => $activity_by,
                  "hierarchies" => array_unique($found_hierarchies),
                  "activities_with_attribute" => $activities_with_attribute,
                );
  //} else {
  //  return FALSE;
  //}
}

?>
