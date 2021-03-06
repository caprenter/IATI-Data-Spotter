<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
    //Include variables for each group. Use group name for the argument
    //e.g. php detect_html.php dfid
    require_once 'variables/' .  $_GET['group'] . '.php';
    require_once 'functions/xml_child_exists.php';
    require_once 'functions/validator_link.php';
    //require_once 'variables/elements_list.php';
    

    
    $results = check_iati_elements ($dir,$url);


    print('<div id="main-content">
            <h4>Looking at the &lt;iati-activites&gt; element</h4>');

            
            
      //Print out Files with no elements
      if ($results["files"] != NULL) {
        //echo "<h3></h3>";
        echo '<p class="cross">Some files need attention. Sort the table to find them</p>';
        
        //foreach ($results["files"] as $file) {
        //   echo '<a href="' . $url . $file .'">' . $url . $file .'</a><br/>';
        //}
      } else {
          //echo "";
          //echo '<p class="tick">All files have AT LEAST one of the elements we are checking for.</p>';
      }
      
      if (!empty($results["rows"])) {
        print('<p class="table-title">Table of files with opening attributes</p>');
            print('<table id="table1" class="sortable">
                <thead>
                  <tr>
                  <th><h3>Count</h3></th>
                    <th><h3>iati-version</h3></th>
                    <th><h3>Generated</h3></th>
                    <th><h3>File</h3></th>
                    <th class="nosort"><h3>Validator</h3></th>
                  </tr>
                </thead>
                <tbody>' . $results["rows"] . '</tbody>
                </table>');
        }



 


    print('</div>');
}

function check_iati_elements ($dir,$url) {
    $rows = '';
    $files=array();
    $i=0;
    if ($handle = opendir($dir)) {
        //echo "Directory handle: $handle\n";
        //echo "Files:\n";

        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") { //ignore these system files
                //echo $file . PHP_EOL;
                //load the xml
                if ($xml = simplexml_load_file($dir . $file)) {
                //print_r($xml); //debug
                  if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    //We're just checking each file for at least one occurance!!
                      //print_r($xml);
                      $i++;
                      if ($xml->attributes()->version) {
                        $version  = $xml->attributes()->version;
                      } else {
                        array_push($files,$file);
                      }
                      $generated  = $xml->attributes()->{'generated-datetime'};
                      if (!strtotime($generated)) {
                        $generated = "x";
                        array_push($files,$file);
                      }
                      //echo $version;
                      //echo $generated;

                      
                      $rows .='<tr><td>' . $i . '</td>';
                      $rows .='<td>' . $version . '</td>';
                      $rows .='<td>'. $generated . '</td>';
                      $rows .='<td><a href="' . $url . $file .'">' . $url . $file .'</a></td>';
                      $rows .='<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
                      //echo '"' .  $element . '","' . $activity->{'iati-identifier'} . '","' . $url . '","' . $file .PHP_EOL;
                      //array_push($missing, $element);
                      
                      //continue 3;
                    }                    
                 
                   
                } else { //simpleXML failed to load a file
                    //echo $file . ' empty';
                }
                
            }// end if file is not a system file
        } //end while
        closedir($handle);
        
    }
    return array("rows" => $rows,
                  "files" => $files
                  );
}




function theme_how_many_of_each ($array) {
$how_many_of_each = array_count_values ($array);
    arsort($how_many_of_each);
    foreach ($how_many_of_each as $key => $value) {
      if ($value >0 ) { 
        echo '<p class="cross">&lt;' . $key . '&gt; not found at all in (' . $value . ') files' . '</p>';
      }
    }
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

	<script type="text/javascript">
  var sorter1 = new TINY.table.sorter("sorter1");
	sorter1.head = "head";
	sorter1.asc = "asc";
	sorter1.desc = "desc";
	sorter1.even = "evenrow";
	sorter1.odd = "oddrow";
	sorter1.evensel = "evenselected";
	sorter1.oddsel = "oddselected";
	sorter1.paginate = true;
	sorter1.currentid = "currentpage";
	sorter1.limitid = "pagelimit";
	sorter1.init("table2",1);
  </script>
