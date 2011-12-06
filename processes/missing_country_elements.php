<?php
set_time_limit(60);
if (in_array($myinputs['group'],array_keys($available_groups))) {
    //Include variables for each group. Use group name for the argument
    //e.g. php detect_html.php dfid
    require_once 'variables/' .  $_GET['group'] . '.php';
    require_once 'functions/xml_child_exists.php';
    require_once 'functions/validator_link.php';    

    $elements = array("recipient-region","recipient-country");
    $results = files_with_neither_element ($elements);


    print('<div id="main-content">
                <h4>Checking for one of:</h4>');
          echo '<ul class="elements">';
          
          foreach ($elements as $element) {
            echo "<li>&lt;";
            echo $element;
            echo "&gt;</li>";
          }
          echo "</ul>";
    
        
        //Print out Files with neither region or country element
      if ($results["rows"] != NULL) {
        echo "<h4>Files with neither element</h4>";
        print('<p class="table-title check">Table of ' . count($results["files"]) . 
              ' file' . (count($results["files"]) == 1 ? '' : 's') . ' with neither element</p>');
            print('<table id="table3" class="sortable">
                <thead>
                  <tr>
                    <th><h3>#</h3></th>
                    <th><h3>File</h3></th>
                    <th class="nosort"><h3>Validator</h3></th>
                  </tr>
                </thead>
                <tbody>'. $results["rows"] . '</tbody>
                </table>');
        } else {
          echo "<p class=\"tick\">All files have at least one of the elements</p>";
        }

        unset($result);
        unset($rows);
        unset($files);
        unset($xml);

        //Print out Files and activities with missing elements
        //$files_with_no_elements = $results["files"];
        $activities = activites_with_neither_element($elements);
        //$files_with_some_activities_missing_some_elements = $activities["files"];
        //$additional_files = array_diff($files_with_some_activities_missing_some_elements, $files_with_no_elements);


        if (!empty($activities["rows"])) {
            echo "<p class='table-title check'>Table of " . count(array_unique($activities["files"])) . " additional files with SOME activities missing elements</p>";
                print('<table id="table2" class="sortable">
                    <thead>
                      <tr>
                        <th><h3>#</h3></th>
                        <th><h3>Identifier</h3></th>
                        <th><h3>File</h3></th>
                        <th class="nosort"><h3>Validator</h3></th>
                      </tr>
                    </thead>
                    <tbody>' . $activities["rows"] . '</tbody>
                    </table>');
            }


    print('</div>');
}

function files_with_neither_element ($elements) {
    global $dir;
    global $url;
    //$missing= array();
    $files = array();
    //$empty_files = array();
    $rows = '';
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
                    
                      if (xml_child_exists($xml, "//" . $elements[0]) || xml_child_exists($xml, "//" . $elements[1]))  {  
                          //We have at least one - yeah!
                      } else {
                          $i++;
                          $rows .='<tr><td>' . $i . '</td>';
                          $rows .='<td><a href="' . $url . $file .'">' .  $file .'</a></td>';
                          $rows .='<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
                          array_push($files,$file);
                      }
                  }
                } else { //simpleXML failed to load a file
                    //echo $file . ' empty';
                }
                
            }// end if file is not a system file
        } //end while
        closedir($handle);
        
    }
    return array("rows" => $rows,
                "files" => $files,
                );
}

function activites_with_neither_element ($elements) {
  global $dir;
  global $url;
  $missing= array();
  $files = array();
  $rows = '';
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
                    if (count($xml->children()) > 0 ) { //php < 5.3
                      //Check to see that elements exist in the file
                     // if (xml_child_exists($xml, "//" . $elements[0]) || xml_child_exists($xml, "//" . $elements[1]))  {
                        
                          foreach ($xml as $activity) {
                              //we can test conditions for elements of the activity data here
                              // the .// allows us to search relative to element
                              if(xml_child_exists($activity, ".//" . $elements[0]) || xml_child_exists($activity, ".//" . $elements[1]))  {  
                                  //We have at least one - yeah!
                              } else {
                                  $i++;
                                  $rows .='<tr><td>' . $i . '</td>';
                                  $rows .='<td><a href="' . validator_link($url,$file,(string)$activity->{'iati-identifier'}) .'">' . (string)$activity->{'iati-identifier'} . '</a></td>';
                                  $rows .='<td><a href="' . $url . $file .'">' .  $file .'</a></td>';
                                  $rows .='<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
                                  array_push($files,$file);
                              }
                          }
                       //} //if elements are in the file

                   } //if count
                  }//if !organsition
              } else { //simpleXML failed to load a file
                  //echo $file . ' empty';
              }
              
          }// end if file is not a system file
      } //end while
      closedir($handle);
  }
  return array("rows" => $rows,
                "files" => $files,
                );
               
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
	sorter.init("table1",0);
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
	sorter1.init("table2",0);
  </script>

