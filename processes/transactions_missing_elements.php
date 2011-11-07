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
    
    $results = check_transaction_elements ($dir);
    print('<div id="main-content">');
    
      echo count($results['type']) . " transaction" . (count($results['type']) == 1 ? '' : 's')  . " missing &lt;transaction-type&gt;";
      echo '<br/>';
      echo count($results['date']). " transaction" . (count($results['date']) == 1 ? '' : 's')  . " missing &lt;transaction-date&gt;";
      echo '<br/>';
      echo count($results['value']). " transaction" . (count($results['value']) == 1 ? '' : 's') . " missing &lt;value&gt;";
      echo '<br/>';
      echo count($results['value-date']). " transaction" . (count($results['value-date']) == 1 ? '' : 's')  . " missing @value-date";
      echo '<br/>';
      echo count($results['fails']) . " element" . (count($results['fails']) == 1 ? '' : 's') . " experiencing one or more problems";
      
      print('<p class="table-title check">Table of elements with problems</p>');
      print('<table id="table1" class="sortable">
          <thead>
            <tr>
              <th><h3>Element</h3></th>
              <th><h3>Type</h3></th>
              <th><h3>Date</h3></th>
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
            echo '<td>' . (in_array("type",$value)? $cross:$tick) . '</td>';
            echo '<td>' . (in_array("date",$value)? $cross:$tick) . '</td>';
            echo '<td>' . (in_array("value",$value)? $cross:$tick) . '</td>';
            echo '<td>' . (in_array("value-date",$value)? $cross:$tick) . '</td>';
            
            echo '<td><a href="' . $url . $value['file'] . '">' . $url . $value['file'] . '</a></td>';
            echo '<td><a href="' . validator_link($url,$value['file']) . '">Validator</a></td>';
            echo '</tr>';

        }
          print('</tbody>
          </table>');
      
    //die;
    
   

    print('</div>');
}






function check_transaction_elements ($dir) {
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
                foreach ($xml as $activity) {
                    $id = (string)$activity->{'iati-identifier'};
                    foreach ($activity->{'transaction'} as $transaction) {
                        if (!xml_child_exists($transaction, ".//transaction-type")) {
                            array_push ($fails_type, array($id,$file));
                            $fails[$id][] = "type";
                            $fails[$id]['file'] = $file;
                        }
                        if(!xml_child_exists($transaction, ".//transaction-date")) {
                           array_push ($fails_date, array($id,$file));
                           $fails[$id][] = "date";
                            $fails[$id]['file'] = $file;
                           //$fails[$id] = "transaction-date";
                        }
                        if(!xml_child_exists($transaction, ".//value")) {
                           array_push ($fails_value, array($id,$file));
                           $fails[$id][] = "value";
                            $fails[$id]['file'] = $file;
                           //$fails[$id] = "value";
                        }
                        if (!$transaction->xpath(".//value[@value-date]")) {
                           array_push ($fails_value_date, array($id,$file));
                           $fails[$id][] = "value-date";
                            $fails[$id]['file'] = $file;
                           //$fails[$id] = "value-date";
                        }       
                     } 
                  }
                }
              } else { //simpleXML failed to load a file
                    //echo $file . ' empty';
              }
                  
            }// end if file is not a system file
            
          } //end while
          closedir($handle);
          
    }
    return array("type" => $fails_type,
                  "date" => $fails_date,
                  "value" => $fails_value,
                  "value-date" => $fails_value_date,
                  "fails" => $fails
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
