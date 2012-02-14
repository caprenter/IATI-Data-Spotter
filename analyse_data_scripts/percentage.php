<?php
/* Uses xpath to get directly to the elements in the XML
 * Then loops through the elements to add the percentage attributes together
 * 
 * We then run an array_count_values on that array to tell us how many of each we have found.
 * Then we output it to a file
*/

//libxml_use_internal_errors ( true );

//Helps us check that we only test activity files
include ('functions/xml_child_exists.php');

//$dir = '../data/dfid/'; //needs trailing slash
$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

$tests = array( "sector","recipient-region" );

//Create a separate results file base on the data directory name
$save_directory = substr($dir,8,-1);
$data_file = $save_directory . "/" . substr($dir,8,-1) . "_percentages.txt";
//echo $data_file; die;
//Open the file to write
$fh = fopen($data_file, 'w') or die("can't open file");


    //Run through each value pair counting
    foreach ($tests as $test) {

      $percentages = count_attributes($test, $dir);
      
      //Write our results to the file
      //frite($fh,$test . "\n"); //simple headers about what we are counting this time round
      //echo $test . PHP_EOL;
      //print_r($types);
      if ($percentages) {
        $number_activites = $percentages[1];
        fwrite($fh,$number_activites . " activities have more than one " . $test . " element\n");
        echo $number_activites . " activities have more than one " . $test . " element" . PHP_EOL;
        
        $percentages = array_count_values($percentages[0]);
        $copy_percentages = $percentages;
        if(isset($copy_percentages[100])) {
          unset($copy_percentages[100]);
        }
        $not_100 = array_sum($copy_percentages);
         
        
        fwrite($fh,$not_100 . " " . $test . " sums don't make 100%\n");
        echo $not_100 . " " . $test . " sums don't make 100%" . PHP_EOL;
        //print_r($types);
        ksort($percentages);
        //print_r($types);
        
        //echo $test[0] . "," .$test[1] . PHP_EOL;
        fwrite($fh,"Percentage,Count\n");
        echo "Percentage,Count" . PHP_EOL;
        foreach ($percentages as $key=>$value) {
          fwrite($fh,$key . "," . $value . "\n");
          echo $key . "," . $value . PHP_EOL;
        }
      } else {
        
        fwrite($fh,$test . "\nNone found\n");
        echo $test .PHP_EOL . "None found" . PHP_EOL;
      }
    }
    
fclose($fh);


function count_attributes($element, $dir) {
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";
    $number_of_elements = 0;
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
             if (@$xml = simplexml_load_file($dir . $file)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                  foreach ($xml as $activity) {
                    $elements = $activity->xpath("./" . $element);
                    //print_r($elements); die;
                    //print_r($attributes); die;
                    //print_r($elements);
                    $percentage = 0;
                    $percentage_per_vocabulary = array();
                    if (count($elements) >1) {
                      //echo $element . "," . count($elements); 
                      $number_of_elements ++;
                      foreach ($elements as $item) {
                        $vocabulary = $item->attributes()->vocabulary;
                        if ($vocabulary != NULL) {
                          //echo $vocabulary;
                          @$percentage_per_vocabulary["{$vocabulary}"] +=$item->attributes()->percentage;
                          //print_r($percentage_per_vocabulary); die;
                          $vocabulary = NULL;
                        } else {
                          $percentage += $item->attributes()->percentage;
                        }
                      }
                      
                      if (isset($percentage_per_vocabulary) && $percentage_per_vocabulary !=NULL) {
                        //print_r($percentage_per_vocabulary); die;
                        foreach ($percentage_per_vocabulary as $score) {
                            $percentages[] = $score;
                        }
                      } else {
                        //echo "," . $percentage;
                        $percentages[] = $percentage;
                      }
                      //if ($percentage > 100) {
                       // echo $file; die;
                      //}
                    } else {
                      continue;
                    }
                  }
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  if (isset($percentages)) {
    return array($percentages,$number_of_elements);
  } else {
    return FALSE;
  }
}

?>
