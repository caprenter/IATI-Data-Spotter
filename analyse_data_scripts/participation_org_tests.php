<?php
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_participating_org.csv';


$data = count_attributes($dir);
//Open the file to write
$fh = fopen($output_file, 'w') or die("can't open file");

    foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,,");
    }
    fwrite($fh,"\n");
    foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"No. Activities,No. participating-org elements,No. Activities with a participating-org,%,");
    }
      fwrite($fh,"\n");
    
    foreach ($data["hierarchies"] as $hierarchy) {
    //foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh, $data["no-activities"][$hierarchy]);
    //}
    //fwrite($fh,"\n");
      fwrite($fh,"," . $data["no_participating_orgs"][$hierarchy]);
    //fwrite($fh,"No. Activities with a participating-org");
   // foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"," . $data["activities_with"][$hierarchy]);
    //}
    //fwrite($fh,"\n");
      fwrite($fh,"," . round(($data["activities_with"][$hierarchy]*100/$data["no-activities"][$hierarchy]),1));
    //fwrite($fh,"No. participating-org elements");
    //foreach ($data["hierarchies"] as $hierarchy) {
      
      //. $data["activities_with_recipient_country_only"][$hierarchy] + $data["activities_with_recipient_region_only"][$hierarchy] + count($data["activities_with_both"][$hierarchy])
    //}
      fwrite($fh,",");
   //fwrite($fh,"Region Only,Country Onl
    //Run through each value pair counting
  }
    fwrite($fh,"\n");
    fwrite($fh,"\n");
    fwrite($fh,"Attribute,");
    foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,");
    }
    fwrite($fh,"\n");
    foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,",Value,Count,Activities with at least one");
    }
    fwrite($fh,"\n");
    
    //Deal with the attribute counts
    foreach ($data["types"] as $attribute_name=>$attribute) {
      echo $attribute_name;
      fwrite($fh, $attribute_name . ",");
      //We need to find all the attribute values and print a csv row for each, even if empty
      //$attribute contains all the values at each hierarchy
      $all_keys = array();
      //print_r($attribute);die;
      foreach($attribute as $hierarchy=>$value) {
        //Count values gives us unique keys with counts. We just wan tkeys for now
        $value = array_count_values($value);
        $keys = array_keys($value);
        //print_r($keys);die;
        $all_keys = array_merge($all_keys,$keys);
        //print_r(array_unique($all_keys)); die;
      }
      $all_keys = array_unique($all_keys);
      print_r($all_keys);
      //Now we have all the possible keys
      //Loop through each key and output a number of lines. If it's the second time through we need a one cell gap at the start
      $i=0;
      foreach ($all_keys as $key) {
        if ($i>0) {
           fwrite($fh,",");
         } 
         $i++;
         foreach($attribute as $hierarchy=>$value) {

          $value = array_count_values($value);
          print_r($value);
          if (isset($value[$key])) {
            $key_text = $key;
            if ($key_text == NULL  ) {
              $key_text = "empty";
            }
            fwrite($fh,$key_text . "," . $value[$key] . "," . count(array_unique($data["activities_with_attribute"][$key][$hierarchy])) . ",");
          } else {
            fwrite($fh,",,,");
          }
          //fwrite($fh,"\n,");
        }
        fwrite($fh,"\n");
      }
    }
      
    
    
    
fclose($fh);
//print_r($data["activities_with_attribute"]);
die;
function count_attributes($dir) {
  $no_participating_orgs = array();
  $activities_with = array();
  $no_activities = array();
  $found_hierarchies= array();
  $activities_with_attribute = array();
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
                        
                        $participating_orgs = $activity->{"participating-org"};
                        if ($participating_orgs !=NULL) {
                          $activities_with[$hierarchy]++;
                        }
                        foreach ($participating_orgs as $participating_org) {
                          $attributes = array("role","type","ref");
                          $no_participating_orgs[$hierarchy]++;
                          foreach($attributes as $attribute) {
                            $this_attribute = (string)$participating_org->attributes()->$attribute;
                            if ($this_attribute == NULL) {
                              $this_attribute = "null";
                            }
                            $types[$attribute][$hierarchy][]=$this_attribute;
                            
                            $activities_with_attribute[$this_attribute][$hierarchy][]=(string)$activity->{'iati-identifier'};
                          } 
                        }
                      
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  if (isset($types)) {
    
    echo "no_activities" . PHP_EOL;
    print_r($no_activities);
    echo "activities_with" . PHP_EOL;
    print_r($activities_with);
    echo "no_participating_orgs" . PHP_EOL;
    print_r($no_participating_orgs);
    //foreach($types as $attribute_name=>$attribute) {
    ///  echo $attribute_name;
//foreach($attribute as $hierarchy=>$values) {
     //   echo $hierarchy;
     //   print_r(array_count_values($values));
     // }
   // }
    $found_hierarchies = array_unique($found_hierarchies);
    sort($found_hierarchies);
    
    return array("types" => $types,
                  "no-activities" => $no_activities,
                  "activities_with" => $activities_with,
                  "no_participating_orgs" => $no_participating_orgs,
                  "hierarchies" => array_unique($found_hierarchies),
                  "activities_with_attribute" => $activities_with_attribute,
                );
  } else {
    return FALSE;
  }
}

?>
