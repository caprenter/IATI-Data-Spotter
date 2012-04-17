<?php
//Call this file like:
//php basic_statistics.php dfid

include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir

//Generate country/region array for lookups
$lists = array("Region.csv","Country.csv");
foreach ($lists as $list) {
  if (($handle = fopen("../helpers/code_lists/" . $list, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ',','"')) !== FALSE) {
          $codes[$data[0]] = $data[1];
      }
      fclose($handle);
  }
}

//Save the results as csv
$output_file = $output_dir . $corpus . '_basic_stats.csv';

$fh = fopen($output_file, 'w') or die("can't open file");
  fwrite($fh,"File Name,Country/Region,Size (KB),Activity Count,Date last-generated,Date last-updated\n");








  global $corpus;
  $server_path_to_files = "/home/david/Webs/aidinfo-batch/data/" . $corpus . "/";
  $most_recent = 0;
  $results = array();
  
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) {//ignore organisation files
              $generated = $xml->attributes()->{'generated-datetime'};
              $most_recent = 0;

              //$count = $xml->count(); //php >5.3
              $count = count($xml->children()); //php < 5.3
              
              $path_to_file  = $server_path_to_files . $file;
              //echo $path_to_file;
              $filesize = filesize($path_to_file);
              //$filesize = filesize($file);
              $filesize = format_bytes($filesize);
              $filesize = number_format($filesize);
              
              $activities = $xml->xpath('//iati-activity');
              //print_r($xml); die;
              //print_r($attributes); die;
              if ($activities != NULL) { 
                foreach ($activities as $activity) {
                  //echo (string)$element->attributes()->$attribute;
                  //Special case: lang needs us to reference the XML namespace
                  
                  $last_updated = $activity->attributes()->{'last-updated-datetime'};
                  $last_updated = strtotime($last_updated);
                  if ($last_updated > $most_recent) {
                    $most_recent = $last_updated;
                    //$i++;
                    //echo $i . ',' . $most_recent . PHP_EOL;
                  }
                } //foreach
                unset($activities);
                unset($xml);
                //echo $most_recent .PHP_EOL;
                echo $file .PHP_EOL;
                if ($most_recent == 0) {
                  $most_recent = "Not found";
                } else {
                  $most_recent = date("Y-m-d H:i:s",$most_recent);
                }
                
                $country_or_region = get_country_or_region($file);
                fwrite($fh,$file . ",");
                fwrite($fh,'"' . $country_or_region . '",');
                fwrite($fh,'"' . $filesize . '",');
                fwrite($fh,$count . ",");
                fwrite($fh,$generated . ",");
                fwrite($fh,$most_recent . ",");
                fwrite($fh,"\n");

              } else { //No activities
                $country_or_region = get_country_or_region($file);
                fwrite($fh,$file . ",");
                fwrite($fh,'"' . $country_or_region . '",');
                fwrite($fh,'"' . $filesize . '",');
                fwrite($fh,$count . ",");
                fwrite($fh,$generated . ",");
                fwrite($fh,"not available,");
                fwrite($fh,"\n");
              }
            } //if organisation
          } else {
            $country_or_region = get_country_or_region($file);
            fwrite($fh,$file . ",");
            fwrite($fh,'"' . $country_or_region . '",');
            fwrite($fh,'"' . $filesize . '",');
            fwrite($fh,"fail,");
            fwrite($fh,"fail,");
            fwrite($fh,"fail,");
            fwrite($fh,"\n");

          } //if xml parses
        }//if system file
    }//end while
  } //end if open dir
fclose($fh);

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    //for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    for ($i = 0; $size >= 10 && $i < 1; $i++) $size /= 1024;
    //return round($size, 2).$units[$i];
    return round($size, 2);
}

function exportCSV($data, $output_file, $col_headers = array()) {

    $stream = fopen ($output_file, 'w');
    if (!empty($col_headers)) {
        fputcsv($stream, $col_headers);
    }

    foreach ($data as $record) {
        fputcsv($stream, $record,',','"');
    }

    fclose($stream);
}

function get_country_or_region($file) {
  //$file="DO";
  $row = 1;
  global $corpus;
  global $codes;
  
  //echo $file;
  //Handle country lookup for differnet files
  switch ($corpus) {
    case "worldbank":
      $country_region_code = explode("-",$file);
      $country_region_code = explode(".",$country_region_code[1]);
      $country_region_code = $country_region_code[0];
      break;
    case 'dfid':
      $country_region_code = $file;
      break;
    case 'sida':
      $country_region_code = substr($file,0,-4);
      break;
    default;
      $country_region_code = $file;
    break;
  }
  if (array_key_exists($country_region_code,$codes)) {
   return $codes[$country_region_code];
  } else {
    return $file;
  }

}
?>
