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


//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_generated.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");

    fwrite($fh,"Element,Attribute\n");
    echo "Element,Attribute" . PHP_EOL;
    //Run through each value pair counting
    //foreach ($tests as $test) {
      
      $updates = get_updated($dir);
      print_r($updates);
      die;
      $types = count_attributes($xpath, $dir);
      
      //Write our results to the file
      fwrite($fh,$test . "\n"); //simple headers about what we are counting this time round
      echo $test . PHP_EOL;
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
    //}
    
fclose($fh);


function get_updated($dir) {
  $most_recent = 0;
  $i=0;
  $generated = array();
  $newest_file = 0;
  $oldest_file = time();

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
                    $generated = $xml->attributes()->{'generated-datetime'};
                    $generated = strtotime($generated);
                    if ($generated > $newest_file) {
                      $newest_file = $generated;
                      echo "newest";
                    }
                    if ($generated < $oldest_file) {
                      $oldest_file = $generated;
                      echo "oldest";
                    }
                    
                    $activities = $xml->xpath('//iati-activity');
                    //print_r($xml); die;
                    //print_r($attributes); die;
                    
                    foreach ($activities as $activity) {
                      //echo (string)$element->attributes()->$attribute;
                      //Special case: lang needs us to reference the XML namespace
                      
                        $last_updated = $activity->attributes()->{'last-updated-datetime'};
                        /*echo $last_updated;
                        if (strstr("Z",$last_updated)) {
                          $last_updated = substr($last_updated,-1);
                          echo $last_updated;
                        }
                        */
                        $last_updated = strtotime($last_updated);
                        if ($last_updated > $most_recent) {
                          $most_recent = $last_updated;
                          $i++;
                          echo $i . ',' . $most_recent . PHP_EOL;
                        }
                        //This allows us to check the date given in the element instead of the attribute
                        //if ($date==NULL) {
                        //  $date = (string)$element;
                       // }
                       //Special case for dfid cos we know their dates don't process properly
                        //if (strstr($dir,"dfid")) {
                        //  $date=substr($date,0,-1);
                        //}
                        //echo $date; die;
                        //$year = date("Y",strtotime($date));
                        //$types[] = $year;

                    } //end foreach
                } //exclude organisations
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  $generated = array( "newest" => date("Y-m-d",$newest_file),
                      "oldest" => date("Y-m-d",$oldest_file),
                      "most-recent" => date("Y-m-d",$most_recent)
                      );
  if (isset($generated)) {
    return $generated;
  } else {
    return FALSE;
  }
}

?>
