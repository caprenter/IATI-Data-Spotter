<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';

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
              echo "Sum of negative transactions: " . number_format($transactions[1]) .'<br/>';
              echo "Sum of positive transactions: " . number_format($transactions[2]) .'<br/>';
              echo "Difference: " . number_format($transactions[2] + $transactions[1]) .'<br/>';
              
          if ($transactions[1] !=NULL && $transactions[2] !=NULL) {
                print("<p>
                    <a href=\"#\" onclick=\"toggle_visibility('foo_" . strtoupper($type) . "');\">Show table:</a>
                  </p>");
          print("</div>"); //end transactions-wrap
             print("<div id=\"foo_" . strtoupper($type) . "\" style=\"display:none;\">");
            theme_transaction_table($transactions[0], $type); //prints a table
            //echo $expenditure_transactions[1] .'<br/>';
            //echo $expenditure_transactions[2] .'<br/>';
            //echo  $expenditure_transactions[2] + $expenditure_transactions[1] .'<br/>';
            print("</div>");
          } else {
            print("</div>"); //end transactions-wrap
          }
  
        }


    print("</div>");//main content
}              

function theme_transaction_table ($transactions,$type) {

  global $transaction_types;
  //Print out a table of all the files that have a good file count
  print("
    <table id='table" . $type . "' class='sortable'>
      <thead>
        <tr>
          <th><h3>Id</h3></th>
          <th><h3>Value</h3></th>
          <th><h3>Date</h3></th>
        </tr>
      </thead>
      <tbody>
      ");
    //arsort($count);
    //$remaining_files = count($count);
    echo 'Table below shows ' . $transaction_types[$type] . ' transactions.';
  foreach ($transactions as $transaction) {
    print('
      <tr>
        <td>' . $transaction[0] . '</td>
        <td>' . $transaction[1][0] . '</td>
        <td>' . $transaction[1][1] . '</a></td>
      </tr>
    ');
  }
  print("</tbody>
      </table>");
}


function get_transactions ($dir,$type) {
  //echo $type;
  $result = array();
  $positive = $negative = 0;
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = simplexml_load_file($dir . $file)) {
            foreach ($xml as $activity) {
                $id = (string)$activity->{'iati-identifier'};
                foreach ($activity->{'transaction'} as $transaction) {
                    if ($code = $transaction->{'transaction-type'}->attributes()->code == $type) {;
                      //echo 'yes';
                      $value = $transaction->value . '<br/>';
                      if ($value > 0) {
                        $positive +=$value;
                      } else {
                        $negative +=$value; 
                      }
                       $date = $transaction->value->attributes()->{'value-date'} . '<br/>';
                      array_push($result, array($id,array($value,$date)));
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
  return array($result, $negative, $positive);
}

function get_filesize ($dir) {
  global $server_path_to_files; //set in variables/server_vars.php
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
           //$path_to_file  = $server_path_to_files .  substr($dir,3) . $file;
           $path_to_file  = $server_path_to_files .  $dir . $file;
           $filesize = filesize($path_to_file);
           //$filesize = filesize($file);
           $filesize = format_bytes($filesize);
           $results[$file] = $filesize;
          
        }
    }
  }
  return $results;
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
