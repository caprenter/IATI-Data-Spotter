<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
//This helps us monitor how long the script takes to run.
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;
?> 
<?php
/* We want to find out which data providers report a specific element
 * Further we want to know which files contain that element
 * (We may want to know how many times that element occurs in those files)
 * (We may want to worry about hierarchy)
 * We probably want to know which activities contain that element
 * i.e. we want a path to possible examples
 * 
 * Data providers reporting THIS ELEMENT:
 * Provider | Number of Files | Total Files
 * Provider view: DFID
 * <reporting-org> occurs X times in Y activities in  FILENAME
 * List Activity IDs and parse out element
 * 
*/
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets all the elements we can loop over and also has an array of providers

/* Get a list of data directories that contain the XML*/
$dir    = '../data/';
$files = scandir($dir);
$banned_folders = array(); //Set up a list of directories to ignore here
$directories = array(); //An array to store our resluts
//print_r($files); //die;

foreach($files as $file) {
  if ($file != "." && $file != "..") { //ignore system directories and files
    if (is_dir($dir . $file)) {
      $directories[] = $file;
    } 
  }
}
//print_r($directories); //die;

//Now DELETE all existing files in our aggregated/processed data directory
$dir2    = 'data/';
$files2 = scandir($dir2);
$banned_folders2 = array(); //Set up a list of directories to ignore here
//print_r($files); //die;

foreach($files2 as $file2) {
  //echo $file;
  if ($file2 != "." && $file2 != "..") { //ignore system directories and files
    if (is_file($dir2 . $file2)) {
       unlink($dir2 . $file2); // delete file
       //echo $dir2 . $file . PHP_EOL;
    } 
  }
}

/*TESTING TESTING*/
//$elements = array("result/indicator/period/period-start"); //Override the big  elementarray for testing
//$directories = array("dfid","aa"); //Override the big directory array for testing
/*END TESTING*/


foreach ($directories as $provider) {
   echo $provider . PHP_EOL;
   $files = array(); //reset
    
   //This processes all XML files for a data provider and returns some aggregated data 
   $data = count_elements($dir . $provider . "/", $elements);
   
   //We have looped through all the files and stored element=>file in a big array..
   $files_with_elements = $data[5]; //This is an array of many elements and many files...
  
   //Loop through all elements and store data for each of them
   //We store the count and file info for this element
   //We also add provider meta data (no of files,no of activ ity files etc)
   foreach ($elements as $element) {
      //Reset a load of stuff used in this loop
      unset($results); 
      unset($new_data);
      unset($old_data);
      //echo $element;
      
      //Save results to file with the name of the element in the file
      $filename = preg_replace("/\//","_",$element);
      //echo $filename; die;
      $output_file = "data/" . $filename . ".php";
      
      //We need to write to this file a lot, and we need to append data to a php serialised array
      //So if it's there we need to extract the data into an array
      if (file_exists($output_file)) {
        if ($old_data = file_get_contents($output_file)) {
               $old_data = unserialize($old_data);
        }
      }
            
      
      //Create an array of files for this element for this data provider
      $files = array();
      foreach ($files_with_elements as $record) {
        $key = key($record);
        if (key($record) == $element) {
          $files[] = $record[$key];
        }
      }
   
      //We end up with an array of this data for this element of this provider
      //We save more than we really need, but that's useful
      $results = array( "provider"=>$provider,
                          "data" => array("all_files" => $data[0],
                                    "org_files" => $data[1],
                                    "activity_files" => $data[2],
                                    "activity_files_with_element" => count($files),
                                    "failed_to_parse" => $data[4],
                                    "files_with_element" => $files,
                                    )
                      );
      //Add our new data to the exisiting data....
      if (isset($old_data)) {
             $old_data[] = $results;
             $new_data = $old_data;
      }
        
      //...unless it's the first pass, in which case we just store our first result
      if (!isset($new_data)) {
          $new_data = array();
          $new_data[] = $results;
      }
    
      //Save results to file with the name of the element in the file
      file_put_contents($output_file,serialize($new_data));
    
  } //end foreach element
} //end foreach directory

function count_elements($dir, $elements) {
  //Count number of files this provider has
  $all_files = $activity_files = $org_files = $good_files = $failed_to_parse = 0;
  $files_with_element = array();
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
            $all_files++;
             if ($xml = @simplexml_load_file($dir . $file)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation file
                    $activity_files++;
                    
                    foreach ($elements as $element) {
                    
                      if (count($xml->xpath('//iati-activity/' . $element)) > 0) {
                      //if ($xml->xpath('//iati-activity/' . $element)) {
                        $good_files++;
                        $files_with_element[] = array($element => $file);
                      }
                    }
                        
                } else {//end if not organisation file
                  $org_files++;
                }
            } else {//end if xml is created
              $failed_to_parse++;
            }
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }

  return array( $all_files,
                $org_files,
                $activity_files,
                $good_files,
                $failed_to_parse,
                $files_with_element
                );
}

?>
<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo "This page was created in ".$totaltime." seconds";
?>
