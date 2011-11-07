<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';

  //Now look for a transaction type variable. See variables/transaction_types.php for the array
 // if ((strlen($myinputs['transaction'])<=2) && in_array(strtolower($myinputs['transaction']),array_keys($transaction_types))) {
 //     $type = strtoupper($myinputs['transaction']);
    
      print('<div id="main-content">');
        //Transactions
        $transaction_types_keys = array_keys($transaction_types); //See variables/transaction_types.php for the array
        
        foreach ($transaction_types_keys as $type) {
          $transactions = get_transactions ($dir, strtoupper($type)); //returns an array with all transactions and a sum of all +ve and -ve transactions
          echo "<div class=\"transactions-wrap\">";
              echo "<h4>" . $transaction_types[$type] . "</h4>";
              if ($transactions !=NULL) {
              theme_transaction_table_by_year ($transactions,$type);
              } else {
                echo "No transactions of this type found.";
              }
          echo "</div>";
        }


    print("</div>");//main content
}              



function theme_transaction_table_by_year ($transactions,$type) {

  //global $transaction_types;
  //Print out a table of all the files that have a good file count
  print("
    <table id='table" . $type . "' class='sortable'>
      <thead>
        <tr>
          <th><h3>Year</h3></th>
          <th><h3>Sum of negative transactions</h3></th>
          <th><h3>Sum of positive transactions</h3></th>
          <th><h3>Difference</h3></th>
        </tr>
      </thead>
      <tbody>
      ");
    //arsort($count);
    //$remaining_files = count($count);
    //echo 'Table below shows ' . $transaction_types[$type] . ' transactions.';
    
    foreach ($transactions as $year => $value) {
      print('
        <tr>
          <td>' . $year  . '</td>
          <td>' . number_format($transactions[$year]['negative']) . '</td>
          <td>' . number_format($transactions[$year]['positive']) . '</td>
          <td>' . number_format($transactions[$year]['negative'] + $transactions[$year]['positive']) . '</td>
        </tr>
'           );
    }
              

  print("</tbody>
      </table>");
}


function get_transactions ($dir,$type) {
  //echo $type;
  $result = array();
  $positive = $negative = $money = array();
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
              foreach ($xml as $activity) {
                  $id = (string)$activity->{'iati-identifier'};
                  foreach ($activity->{'transaction'} as $transaction) {
                      if ($code = $transaction->{'transaction-type'}->attributes()->code == $type) {
                        if(xml_child_exists($transaction, ".//transaction-date")) {
                          $date = $transaction->{'transaction-date'}->attributes()->{'iso-date'};
                          $year = date("Y",strtotime($date));
                          //$type = $transaction->{'transaction-date'}->attributes()->type;
                          
                          //array_push($transactions, array("date"=>$year,"id"=>$id, "file"=>$file));
                        } else {
                          $year = 'Missing';
                          
                          //echo $year . $id . $file;
                          //die;
                        }
                        //echo $year;
                        //echo 'yes';
                        $value = $transaction->value;
                        //echo $value;
                        if ($value > 0) {
                          $money[$year]['positive'] +=$value;
                        } else {
                          $money[$year]['negative'] +=$value; 
                        }
                        
                        //array_push($result, array($id,array($value,$year)));
          
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
  //ksort($positive);
  ///print_r($positive);
  //ksort($negative);
  //print_r($negative);
  ksort($money);
  //print_r($money);
  //die;
  //return array($result, $negative, $positive);
  return $money;
}


?>


<script type="text/javascript" src="javascript/tinytable/script.js"></script>
<?php
foreach ($transaction_types_keys as $type) {
  ?>
	<script type="text/javascript">
  var sorter<?php echo $type; ?>  = new TINY.table.sorter("sorter<?php echo $type; ?>");
	sorter<?php echo $type; ?>.head = "head";
	sorter<?php echo $type; ?>.asc = "asc";
	sorter<?php echo $type; ?>.desc = "desc";
	sorter<?php echo $type; ?>.even = "evenrow";
	sorter<?php echo $type; ?>.odd = "oddrow";
	sorter<?php echo $type; ?>.evensel = "evenselected";
	sorter<?php echo $type; ?>.oddsel = "oddselected";
	sorter<?php echo $type; ?>.paginate = true;
	sorter<?php echo $type; ?>.currentid = "currentpage";
	sorter<?php echo $type; ?>.limitid = "pagelimit";
	sorter<?php echo $type; ?>.init("table<?php echo $type; ?>");
  </script>

<?php 
}
?>

<?php include ("javascript/toggle.js"); ?>
