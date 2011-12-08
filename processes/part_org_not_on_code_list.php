<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/bad_files_table.php';
  require_once 'functions/validator_link.php';
  
  $participating_org_ref_count = 0;
  $exclude = array("GB","EU");
  $bad_codes = array();
  $bad_files = array();
  $no_refs  = array();

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
              if ($xml = @simplexml_load_file($dir . $file)) {;
              //print_r($xml); //debug
                  foreach ($xml as $activity) {
                      //CHECK: Participating Org code is on the code list
                      foreach ($activity->{'participating-org'} as $participating_org) {
                          $participating_org_ref = (string)$participating_org->attributes()->ref;
                          if ($participating_org_ref !=NULL) {
                            if (!array_key_exists($participating_org_ref, $codes) && !in_array($participating_org_ref,$exclude)) {
                                $bad_codes[] = array("ref"=>$participating_org_ref,"file"=>$file,"text"=>(string)$participating_org);
                                //echo $url . $file . PHP_EOL;
                                //$participating_org_ref_count ++;
                                //continue 3;
                            }
                          } else {
                            //empty ref
                            $no_refs[] = array("ref"=>$participating_org_ref,"file"=>$file,"text"=>(string)$participating_org);
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
  //print_r($bad_codes);die;
  print('<div id="main-content"><h4>Checking participating-org/@ref against the code list for refs not on the list</h4>');
    if ($bad_codes != NULL) {
        foreach ($bad_codes as $key=>$value) {
          $affected_files[] = $value["file"];
        }
        foreach ($bad_codes as $key=>$value) {
          $codes[] = $value["ref"];
        }

        print("
            <p class='table-title'>Table of &lt;participating-org&gt; codes not on code lists.</p>
            <p>" . count(array_unique($codes)) . " different codes were reported " . count($bad_codes). " times from " . count(array_unique($affected_files)) . " affected files.</p>
            <table id='table' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>Code</h3></th>
                  <th><h3>Text</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Valiate</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
              //$codes =array_keys($bad_codes);
              //$codes = array_unique($codes);
              //sort($codes);
              $i=0;
              foreach ($bad_codes as $key=>$value) {
                $i++;
                echo '<tr>';
                echo '<td>' . $i .'</td>';
                echo '<td>' . $value["ref"] .'</td>';
                echo '<td>' . $value["text"] . '</td>';
                echo '<td><a href="' . $url . $value["file"] .'">' . $value["file"] .'</a></td>';
                echo '<td><a href="' . validator_link($url,$value["file"]) . '">Validator</a></td>';
                echo '</tr>';
              }
          print("</tbody>
              </table>");
    } else {
      echo "<p class=\"tick\">No participating-org refs found that are not on the code list</p>";
    }
    
    if ($no_refs != NULL) {
        foreach ($no_refs as $key=>$value) {
          $affected_files[] = $value["file"];
        }
        //foreach ($no_refs as $key=>$value) {
         // $codes[] = $value["ref"];
        //}

        print("
            <p class='table-title'>Table of &lt;participating-org&gt; where reference is not specified.</p>
            <p>Reported " . count($no_refs). " times from " . count(array_unique($affected_files)) . " affected files.</p>
            <table id='table' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>Text</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Valiate</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
              //$codes =array_keys($bad_codes);
              //$codes = array_unique($codes);
              //sort($codes);
              $i=0;
              foreach ($no_refs as $key=>$value) {
                $i++;
                echo '<tr>';
                echo '<td>' . $i .'</td>';
                echo '<td>' . $value["text"] . '</td>';
                echo '<td><a href="' . $url . $value["file"] .'">' . $value["file"] .'</a></td>';
                echo '<td><a href="' . validator_link($url,$value["file"]) . '">Validator</a></td>';
                echo '</tr>';
              }
          print("</tbody>
              </table>");
    } else {
      echo "<p class=\"tick\">No mismatch participating-org refs found</p>";
    }
    //echo $participating_org_ref_count;
    
    //$bad_codes = array_unique($bad_codes);
    //sort($bad_codes);
    //print_r($bad_codes). PHP_EOL;
    //print_r(array_unique($bad_files)). PHP_EOL;
    //echo count(array_unique($bad_files)). PHP_EOL;
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
