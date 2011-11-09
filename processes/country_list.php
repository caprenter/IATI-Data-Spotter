<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';

$recipient_countries = array();
$recipient_regions = array();
$bad_files = array();
$i=0;
//$url = 'http://ec.europa.eu/europeaid/files/iati/';  //EU url endpoint
if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
             
            $i++;
            if ($xml = simplexml_load_file($dir . $file)) {
              foreach ($xml as $activity) {
                $code = (string)$activity->{'recipient-country'}->attributes()->code;
                $country = (string)$activity->{'recipient-country'};
                $region = (string)$activity->{'recipient-region'};
                $region_code = (string)$activity->{'recipient-region'}->attributes()->code;
                //echo $code;

                if (!empty($code)) { $code = ";" . $code; }
                if (!empty($region_code)) { $region_code = ";" . $region_code; }
                array_push($recipient_countries, $country . $code);  
                //array_push($recipient_countries, $country);
                array_push($recipient_regions, $region . $region_code);    
              }          
            } else {
              array_push($bad_files,$file);
            }
        }
    }        
}

print('<div id="main-content">');
  print('<h4>No. of files: ' . $i . '</h4>');
  theme_country_table ($recipient_regions,1,'Region');
  theme_country_table ($recipient_countries,2,'Country');
  //print_r(array_count_values ($recipient_countries));
  //echo count($recipient_countries);


  //Print a table of failing files
  if ($bad_files != NULL) {
    foreach ($bad_files as $file) {
      $rows .= '<tr><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
    }

    print("
        <table id='fail-table' class='sortable'>
          <thead>
            <tr>
              <th><h3>These files could not be parsed:</h3></th>
            </tr>
          </thead>
          <tbody>
            $rows
          </tbody>
        </table>"
       );
  }
print('</div>');
}

function theme_country_table ($recipient,$id,$string) {
  //Get a count of all files
  //$total_countries = count($recipient_countries);
  $country_counts = array_count_values ($recipient);
  
 
  //Print out a table of all the files that have a good file count
  print("
    <table id='table" . $id . "' class='sortable'>
      <thead>
        <tr>
          
          <th><h3>Recipient " . $string . "</h3></th>
          <th><h3>Recipient " . $string . " Code</h3></th>
          <th><h3>No. of Activities</h3></th>
        </tr>
      </thead>
      <tbody>
      ");
    //ksort($country_counts);
    //$remaining_files = count($count);
    //echo 'Table below shows ' . $remaining_files . ' out of ' . $total_files . ' files in the dataset';
  foreach ($country_counts as $country => $number) {
    if ($country == NULL) {
      $country = "NO " . strtoupper($string) . " NAME GIVEN;";
    } 
    
      $country_data = explode(";",$country);
      if ($country_data[0]=="") { 
          $country_data[0] = "NO " . strtoupper($string) . " NAME FOUND"; 
      }
    
    print('
      <tr>
        <td>' . $country_data[0] . '</td>
        <td>' . $country_data[1] . '</td>
        <td>' . $number . '</td>
      </tr>
    ');
  }
  print("</tbody>
      </table>");
}




?>

<script type="text/javascript" src="javascript/tinytable/script.js"></script>
	<script type="text/javascript">
  var sorter = new TINY.table.sorter("sorter");
	sorter.head = "head";
	sorter.asc = "asc";
	sorter.desc = "desc";
	sorter.even = "evenrow";
	sorter.odd = "oddrow";
	sorter.evensel = "evenselected";
	sorter.oddsel = "oddselected";
	sorter.paginate = true;
	sorter.currentid = "currentpage";
	sorter.limitid = "pagelimit";
	sorter.init("table1",1);
  </script>
  
  <script type="text/javascript" src="javascript/tinytable/script.js"></script>

	<script type="text/javascript">
  var sorter2 = new TINY.table.sorter("sorter2");
	sorter2.head = "head";
	sorter2.asc = "asc";
	sorter2.desc = "desc";
	sorter2.even = "evenrow";
	sorter2.odd = "oddrow";
	sorter2.evensel = "evenselected";
	sorter2.oddsel = "oddselected";
	sorter2.paginate = true;
	sorter2.currentid = "currentpage";
	sorter2.limitid = "pagelimit";
	sorter2.init("table2",1);
  </script>
