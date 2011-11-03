<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  $i=0;

  //Now look for a transaction type variable. See variables/transaction_types.php for the array
 // if ((strlen($myinputs['transaction'])<=2) && in_array(strtolower($myinputs['transaction']),array_keys($transaction_types))) {
 //     $type = strtoupper($myinputs['transaction']);
    
      print('<div id="main-content">');
      print('<h3>All transactions</h3>');
        //Transactions
        $transactions = get_last_transactions ($dir);
        
        if ($transactions !=NULL) {
            //Multisort the array
            // Obtain a list of columns
            foreach ($transactions as $key => $row) {
                $ids[$key]  = $row['id'];
                $dates[$key] = $row['date'];
            }

            // Sort the data with volume descending, edition ascending
            // Add $data as the last parameter, to sort by the common key
            array_multisort($dates, SORT_DESC, $ids, SORT_ASC, $transactions);

             print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>Id</h3></th>
                      <th><h3>Transaction Date</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validator</h3></th>
                    </tr>
                  </thead>
                  <tbody>");
              foreach ($transactions as $transaction) {
                $i++;
                //if ($i<20) {
                  echo '<tr><td><a href="' . validator_link($url,$transaction["file"],$transaction["id"]) .'">' . $transaction["id"] . '</a></td>';
                  echo "<td>" . date("Y-m-d",$transaction["date"]) . "</td>";
                  echo '<td><a href="' . $url . $transaction["file"] .'">' . $url . $transaction["file"] .'</a></td>';
                  echo '<td><a href="' . validator_link($url,$transaction["file"]) . '">Validator</td></tr>';
                //} else {
                //  continue;
               // }
              //return array($transactions);
              }
              print("</tbody></table>");
          } else {
            echo '<p class="cross">No &lt;transaction-date&gt; elements found</p>';
          }


    print("</div>");//main content
}              




function get_last_transactions ($dir) {
  //echo $type;
  $transactions = array();
  //$positive = $negative = 0;
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
                if(xml_child_exists($xml, "//transaction-date"))  { 
                foreach ($xml as $activity) {
                    $id = (string)$activity->{'iati-identifier'};
                    foreach ($activity->{'transaction'} as $transaction) {
                      if(xml_child_exists($transaction, ".//transaction-date")) {
                        $date = $transaction->{'transaction-date'}->attributes()->{'iso-date'};
                        $date = strtotime($date);
                        //$type = $transaction->{'transaction-date'}->attributes()->type;
                        
                        array_push($transactions, array("date"=>$date,"id"=>$id, "file"=>$file));
                      }
                        }
                         
                    }
                 }
              }

            //$results[$file] = $count;
          } else {
            //$results[$file] = 'FAIL';
          }
        }
    }
  }
  //print_r($transactions);
  return $transactions;
    

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


