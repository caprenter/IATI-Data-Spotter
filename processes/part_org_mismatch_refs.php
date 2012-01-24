<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  $participating_org_ref_count = 0;
  $exclude = array("GB","EU");
  $bad_codes = array();
  $bad_files = array();
  $rows ="";

  include_once('helpers/parse_csv.php');
  //$unique_codes = array_keys($codes);
  //if (array_key_exists('41300', $codes)) {
    //   echo "iuyyu";
  //}
  //die;
  //print_r($unique_codes);
  //die;
  if ($handle = opendir($dir)) {
      //echo "Directory handle: $handle\n";
      //echo "Files:\n";

      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
              //echo $file . PHP_EOL;
              //load the xml
              if ($xml = simplexml_load_file($dir . $file)) {;
              //print_r($xml); //debug
                  foreach ($xml as $activity) {
                      
                      //CHECK: Participating org Code matches output text
                      foreach ($activity->{'participating-org'} as $participating_org) {
                          $participating_org_ref = (string)$participating_org->attributes()->ref;
                          //if ($participating_org_ref == NULL) { $participating_org_ref = ""; }
                          //echo $participating_org_ref . PHP_EOL;
                          if ($participating_org_ref != NULL) {
                            if (array_key_exists($participating_org_ref, $codes) && !in_array($participating_org_ref,$exclude)) {
                                if ($codes[$participating_org_ref][2] == $participating_org[0]) {
                                  //echo "match";
                                } else {
                                  //echo "mismatch";
                                  $expected = $codes[$participating_org_ref][2];
                                  $found = $participating_org[0];
                                  if ($participating_org_ref == NULL) { $participating_org_ref = "empty string"; } //no-ref given
                                  $rows .= '<tr><td>'. $participating_org_ref . '</td><td>' . $expected . '</td><td>' . $found . '</td><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
                                }
                                
                            }
                          }                        
                      }
                  } 
              } else {
                  $bad_files[] = $file;
              }
              
          }// end if file is not a system file
      } //end while
      closedir($handle);
  }
  
  print('<div id="main-content"><h4>Checking participating-org/@ref against the code list for mismatches</h4>');
  if ($rows !=NULL) {
     //Print out a table of all the files that have a good file count
    print("
      <p class='table-title'>Table of mismatch &lt;participating-org&gt; code strings to found string.</p>
      <table id='table' class='sortable'>
        <thead>
          <tr>
            <th><h3>Partcipating Org Ref</h3></th>
            <th><h3>Expected</h3></th>
            <th><h3>Found</h3></th>
            <th><h3>File</h3></th>
          </tr>
        </thead>
        <tbody>
        ");
        echo $rows;
    print("</tbody>
        </table>");
  } else {
    print('<p class="tick">No mismatches found</p>');
  }
        
  if ($bad_files !=NULL) {
      theme_bad_files($bad_files,$url);
  }       

    
    print('<div class="notes"><p>Excluded codes:</p>
    <ul>');
    foreach ($exclude as $ex) {
          echo "<li>" . $ex ."</li>";
        }
    print('</ul>
    </div>');
  print('</div>'); 
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
	sorter.init("table");
  </script>
