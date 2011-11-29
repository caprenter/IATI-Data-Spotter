<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
    //Include variables for each group. Use group name for the argument
    //e.g. php detect_html.php dfid
    require_once 'variables/' .  $_GET['group'] . '.php';
    require_once 'functions/xml_child_exists.php';
    require_once 'variables/elements_list.php';
    
    $cross = '0 <img src="theme/images/cross.png" alt="cross"/>';
    $tick = '1 <img src="theme/images/tick.png" alt="tick"/>';
    $cross = 'x';
    $tick = '&#10003;';
    
    $results = check_budget_elements ($dir);
    print('<div id="main-content">');
        if($results['start'] || $results['end'] || $results['value'] || $results['value-date'] || $results['fails']) {
          print('<h4>Elements</h4>');
          echo count($results['start']) . " budget" . (count($results['start']) == 1 ? '' : 's')  . " missing &lt;period-start&gt;";
          echo '<br/>';
          echo count($results['end']). " budget" . (count($results['end']) == 1 ? '' : 's')  . " missing &lt;period-end&gt;";
          echo '<br/>';
          echo count($results['value']). " budget" . (count($results['value']) == 1 ? '' : 's') . " missing &lt;value&gt;";
          echo '<br/>';
          echo count($results['value-date']). " budget" . (count($results['value-date']) == 1 ? '' : 's')  . " missing @value-date";
          echo '<br/>';
          echo count($results['fails']) . " activit" . (count($results['fails']) == 1 ? 'y' : 'ies') . " experiencing one or more problems";
        }
        if (!empty($results['fails'])){
          print('<p class="table-title check">Table of elements with problems</p>');
          print('<table id="table1" class="sortable">
              <thead>
                <tr>
                  <th><h3>Id</h3></th>
                  <th><h3>Start</h3></th>
                  <th><h3>End</h3></th>
                  <th><h3>Value</h3></th>
                  <th><h3>@value-date</h3></th>
                  <th><h3>File</h3></th>
                  <th class="nosort"><h3>Validator</h3></th>
                </tr>
              </thead>
              <tbody>'
              );
              
              foreach ($results['fails'] as $key => $value) {
                //print_r($value); die;
                echo '<tr>';
                echo '<td><a href="' . validator_link($url,$value['file'],$key) . '">' . $key . '</a></td>';
                echo '<td>' . (in_array("start",$value)? $cross:$tick) . '</td>';
                echo '<td>' . (in_array("end",$value)? $cross:$tick) . '</td>';
                echo '<td>' . (in_array("value",$value)? $cross:$tick) . '</td>';
                echo '<td>' . (in_array("value-date",$value)? $cross:$tick) . '</td>';
                
                echo '<td><a href="' . $url . $value['file'] . '">' . $url . $value['file'] . '</a></td>';
                echo '<td><a href="' . validator_link($url,$value['file']) . '">Validator</a></td>';
                echo '</tr>';

            }
              print('</tbody>
              </table>');
          }
        //die;
         if ($results['no-budgets']) {
            echo "<h4>Files with no budgets</h4>";
            print("
            <table id='table' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Validator</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            $files = array_unique($results['no-budgets']);
            $i=0;
            foreach ($files as $file) {
              $i++;
              print('
              <tr>
                  <td>' . $i . '</td>
                  <td><a href="' .$url . rawurlencode($file) . '">' . $file . '</a></td>
                  <td><a href="' . validator_link($url,$file) . '">Validator</a></td>
              </tr>'
              );
            }
              print("</tbody>
            </table>");
          }

    print('</div>');
}


function check_budget_elements ($dir) {
    //global $dir;
    //$missing= array();
    $files = array();
    //$rows = '';
    $fails = array();
    $fails_value = $fails_value_date = $fails_date = $fails_type = array();
    if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
            if ($xml = simplexml_load_file($dir . $file)) {
              if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
                if(xml_child_exists($xml, "//budget")) { //ignore org files
                    foreach ($xml as $activity) {
                        $id = (string)$activity->{'iati-identifier'};
                        foreach ($activity->{'budget'} as $budget) {
                            if (!xml_child_exists($budget, ".//period-start")) {
                                array_push ($fails_type, array($id,$file));
                                $fails[$id][] = "start";
                                $fails[$id]['file'] = $file;
                            }
                            if(!xml_child_exists($budget, ".//period-end")) {
                               array_push ($fails_date, array($id,$file));
                               $fails[$id][] = "end";
                                $fails[$id]['file'] = $file;
                               //$fails[$id] = "budget-date";
                            }
                            if(!xml_child_exists($budget, ".//value")) {
                               array_push ($fails_value, array($id,$file));
                               $fails[$id][] = "value";
                                $fails[$id]['file'] = $file;
                               //$fails[$id] = "value";
                            }
                            if (!$budget->xpath(".//value[@value-date]")) {
                               array_push ($fails_value_date, array($id,$file));
                               $fails[$id][] = "value-date";
                                $fails[$id]['file'] = $file;
                               //$fails[$id] = "value-date";
                            }       
                         } 
                      }
                  } else { //no budgets found
                    $files_with_no_budgets[] = $file;
                  }
                } //end organisation file check
              } else { //simpleXML failed to load a file
                    //echo $file . ' empty';
              }
                  
            }// end if file is not a system file
            
          } //end while
          closedir($handle);
          
    }
    if (!isset($files_with_no_budgets)) {
      $files_with_no_budgets = NULL;
    }
    return array("start" => $fails_type,
                  "end" => $fails_date,
                  "value" => $fails_value,
                  "value-date" => $fails_value_date,
                  "fails" => $fails,
                  "no-budgets" => $files_with_no_budgets
                  );

}



function validator_link($url,$file,$id = NULL) {
  if ($id !=NULL) {
    $link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?mode=view&type=activity&id=';
    $link .=$id;
    $link .= '&source=' . urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
  } else {
    $link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?type=activitySet&source=';
    $link .= urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
    $link .= '&mode=download';
  }
  return $link;
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
	sorter.init("table1");
  </script>
