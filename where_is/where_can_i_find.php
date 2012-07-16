<?php
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
include ('settings.php'); //sets all the elements we can loop over
//$output_file = $output_dir . $corpus . '_elements.csv';


/* Get a list of data directories*/
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


//$elements = array("result/indicator/period/period-start"); //Override the big  elementarray for testing
$directories = array("dfid"); //Override the big directory array for testing
foreach ($elements as $element) {

  $results = array();
  
  foreach ($directories as $provider) {
   $data = count_elements($dir . $provider . "/", $element);
   $results[] = array("provider"=>$provider,
                    "data" => array("all_files" => $data[0],
                                    "org_files" => $data[1],
                                    "activity_files" => $data[2],
                                    "activity_files_with_element" => $data[3],
                                    "failed_to_parse" => $data[4],
                                    "files_with_element" => $data[5],
                                    )
                    );
   //echo $provider . "|" . $data[0] . "|" . $data[1] . "|" . $data[2] . "|" . $data[3] . "|" . $data[4] . PHP_EOL;
   //die;
  }
  //print_r($results);
  
  //Save results to file with the name of the element in the file
  $filename = preg_replace("/\//","_",$element);
  //echo $filename; die;
  $output_file = "data/" . $filename . ".php";
  $fh = fopen($output_file, 'w') or die("can't open file");
    fwrite($fh,serialize($results));
  fclose($fh);
  //$fh = file_get_contents($output_file);
  //$fh = unserialize($fh);
  //print_r($fh);
  //echo $fh[0]["provider"];
}

function count_elements($dir, $element) {
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
                    if (count($xml->xpath('//iati-activity/' . $element)) > 0) {
                    //if ($xml->xpath('//iati-activity/' . $element)) {
                      $good_files++;
                      $files_with_element[] = $file;
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
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo "This page was created in ".$totaltime." seconds";
?>
