<?php

include ('functions/xml_child_exists.php');

//$dir = '../data/dfid/'; //needs trailing slash
$corpus = $_SERVER['argv'][1];
$dir = '../data/' . $corpus . "/";


//Count activities in files
$data = get_file_data ($dir);
$output_file = 'basic_stats.csv';
$col_headers = array("File Name","Size","Activity Count","Date last-generated","Date last-updated");
exportCSV($data, $output_file, $col_headers);
print_r($data); die;

//Export data in required format
print('<div id="main-content">
        <h4>Results</h4>');
  theme_size_count($count,$size,$url);
print('</div>');
      
    
             

function theme_size_count ($count,$size,$url) {
  //Get a count of all files
  $total_files = count($count);
  
  //Find any failing files and take them out of the array
  //Print a table of failing files
  $rows = "";
  $i=0;
  foreach ($count as $file => $number) {
    if ($number === "FAIL") {
      $rows .= '<tr><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
      $i++;
      unset($count[$file]);
    }
  }
  if ($rows) {
      print("
          <table id='fail-table' class='sortable'>
            <thead>
              <tr>
                <th><h3>" . $i ." file" . ($i == 1 ? '' : 's') ."  could not be parsed:</h3></th>
              </tr>
            </thead>
            <tbody>
              $rows
            </tbody>
          </table>"
         );
  }
  
  $rows ='';
  $i=0;
  foreach ($count as $file => $number) {
    if ($number === 0) {
      $rows .= '<tr><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
      $i++;
      unset($count[$file]);
    }
  }
  if ($rows) {
      print("
          <table id='fail-table2' class='sortable'>
            <thead>
              <tr>
                <th><h3>" . $i ." file" . ($i == 1 ? '' : 's') ." have 0 activites:</h3></th>
              </tr>
            </thead>
            <tbody>
              $rows
            </tbody>
          </table>"
         );
  }
  
  //Print out a table of all the files that have a good file count
  print("
    <table id='table' class='sortable'>
      <thead>
        <tr>
          <th><h3>No. Activities</h3></th>
          <th><h3>Size</h3></th>
          <th><h3>File</h3></th>
          <th class='nosort'></th>
        </tr>
      </thead>
      <tbody>
      ");
    arsort($count);
    $remaining_files = count($count);
    echo 'Table below shows ' . $remaining_files . ' out of ' . $total_files . ' files in the dataset';
  foreach ($count as $file => $number) {
    print('
      <tr>
        <td>' . $number . '</td>
        <td>' . $size[$file] . '</td>
        <td><a href="' .$url . rawurlencode($file) . '">' . $file . '</a></td>
        <td><a href="' . validator_link($url,$file) . '">Validator</a></td>
    </tr>
    ');
  }
  print("</tbody>
      </table>");
}


function get_file_data ($dir) {
  global $corpus;
  $server_path_to_files = "/home/david/Webs/aidinfo-batch/data/" . $corpus . "/";
  $most_recent = 0;
  
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) {//ignore organisation files
              $generated = $xml->attributes()->{'generated-datetime'};
              //$count = $xml->count(); //php >5.3
              $count = count($xml->children()); //php < 5.3
              
              $path_to_file  = $server_path_to_files . $file;
              //echo $path_to_file;
              $filesize = filesize($path_to_file);
              //$filesize = filesize($file);
              $filesize = format_bytes($filesize);
              
              $activities = $xml->xpath('//iati-activity');
              //print_r($xml); die;
              //print_r($attributes); die;
              
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
              $results[] = array( 'file' => $file,
                                  'filesize' => $filesize,
                                  'count' => $count,
                                  'generated' => $generated,
                                  'most-recent' => date("Y-m-d H:i:s",$most_recent)
                                  );
            } //if organisation
          } else {
            $results[] = array( 'file' => $file,
                                  'count' => "fail",
                                  'filesize' => "fail",
                                  'generated' => "fail",
                                  'most-recent' => "fail"
                                  );
          } //if xml parses
        }//if system file
    }//end while
  } //end if open dir
  return $results;
}

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    //for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    for ($i = 0; $size >= 10 && $i < 1; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
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

?>
