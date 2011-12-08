<?php

if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  require_once 'functions/xml_child_exists.php';
  require_once 'functions/validator_link.php';
  require_once 'functions/bad_files_table.php';
  
  $found_elements = array();
  
  print('<div id="main-content">
          <h4>Checking currency for:</h4>
          <ul class="elements">
            <li>@default-currency</li>
            <li>&lt;transaction&gt;</li>
            <li>&lt;budget&gt;</li>
            <li>&lt;planned-disbursementt&gt;</li>
          </ul>');
      $currencies = currency_check($dir); //returns an array with all transactions that have non-integer values
      if ($currencies["unique"] != NULL) {
          echo "<h4>Results</h4>";
          echo "<p>Default currencies found:</p>";
          echo "<ul class=\"elements\">";
          foreach ($currencies["unique"] as $currency) {
            echo "<li>" . $currency . "</li>";
          }
          echo "</ul><br/>";
      } 
      if ($currencies["without"] !=NULL) {
        print_r($currencies["without"]);
        echo "<h4>Activites without a default currency</h4>";
        theme_missing_currency_table($currencies["without"],1);
        //die;
      } else {
        echo '<p class="tick">No activites found without a default currency</p>';
      }
      if ($currencies["transactions"] !=NULL) {
        echo "<h4>Transactions with a different currency OR duplicate currency</h4>";
        //print_r($currencies["transactions"]);
        theme_activity_table($currencies["transactions"],2);
        //die;
      } else {
        if (in_array("transaction",$found_elements)) {
          echo '<p class="tick">All transaction currencies match the default</p>';
        } else {
          echo '<p class="check">No transactions found</p>';
        }
      }
      if ($currencies["budgets"] !=NULL) {
        //print_r($currencies["budgets"]);
        echo "<h4>Budgets with a different currency OR duplicate currency</h4>";
        theme_activity_table($currencies["budgets"],3);
        //die;
      } else {
        if (in_array("budget",$found_elements)) {
          echo '<p class="tick">All budget currencies match the default</p>';
        } else {
          echo '<p class="check">No budgets found</p>';
        }
      }
      if ($currencies["disbursement"] !=NULL) {
        //print_r($currencies["disbursement"]);
        echo "<h4>Planned Disbursements with a different currency OR duplicate currency</h4>";
        theme_activity_table($currencies["disbursement"],4);
        //die;
      } else {
        if (in_array("planned-disbursement",$found_elements)) {
          echo '<p class="tick">All planned-disbursement currencies match the default</p>';
        } else {
          echo '<p class="check">No planned-disbursements found</p>';
        }
      } 
      //print_r($found_elements);
      //Print a table of failing files
      if ($currencies["bad-files"] !=NULL) {
        theme_bad_files($currencies["bad-files"],$url);
      }
  print("</div>");//main content
}              


function currency_check ($dir) {
  //echo $type;
  $result = array();
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          if ($xml = @simplexml_load_file($dir . $file)) {
            if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files
              foreach ($xml as $activity) {
                if (count($activity->children()) > 0 ) { //skips registry record entries
                  $id = (string)$activity->{'iati-identifier'};
                  //<iati-activity last-updated-datetime="2011-10-06T00:00:00.0000000-07:00" default-currency="USD" xml:lang="en">
                  $default_currency = (string)$activity->attributes()->{'default-currency'};
                  
                  
                  
                  
                  if ($default_currency !=NULL) {
                    $currencies[] = $default_currency;
                    $transaction = check_currency_against_default ($file,$activity,'transaction',$default_currency);//returns false or array
                    //print_r($transaction);
                    if ($transaction) {
                      $transactions[] = $transaction;
                    }
                    $budget = check_currency_against_default ($file,$activity,'budget',$default_currency);//returns false or array
                    if ($budget) {
                      $budgets[] = $budget;
                    }
                    $planned_disbursement = check_currency_against_default ($file,$activity,'planned-disbursement',$default_currency); //returns false or array
                    //print_r($planned_disbursement);
                    if ($planned_disbursement) {
                      $planned_disbursements[] = $planned_disbursement;
                      //echo "yes";
                      //print_r($planned_disbursements);
                    }
                  } else {
                    echo xml_child_exists($xml, "//iati-organisation");
                    print_r($activity); die;
                    $activities_without_deafult[] = array("id"=>$id,"file"=>$file);
                  }
                }
               }
            }
            //$results[$file] = $count;
          } else {
            $bad_files[] = $file;
          }
        }
    }
  }
  if(isset($currencies)) {
    $unique_currencies = array_unique($currencies);
  }
  if(!isset($activities_without_deafult)) {
    $activities_without_deafult = NULL;
  }
  if(!isset($transactions)) {
    $transactions = NULL;
  }
  if(!isset($budgets)) {
    $budgets = NULL;
  }
  //print_r($planned_disbursements);
  if(!isset($planned_disbursements)) {
    $planned_disbursements = NULL;
  }
  if(!isset($bad_files)) {
    $bad_files = NULL;
  }
  
  return array( "unique" => $unique_currencies,
                "without" => $activities_without_deafult,
                "transactions" => $transactions,
                "budgets" => $budgets,
                "disbursement" => $planned_disbursements,
                "bad-files" => $bad_files
              );
}

function check_currency_against_default ($file,$activity,$element,$default_currency) {
  global $found_elements;
  $id = (string)$activity->{'iati-identifier'};
  foreach ($activity->{$element} as $element_with_value) {
      $currency = (string)$element_with_value->value->attributes()->currency;
      if ($element_with_value !=NULL) { 
        //echo "yeah";
        $found_elements[] = $element; 
      }
      if ($currency !=NULL && $currency != $default_currency) {
          //We've fond an ovride in the currency value
          $result[] = array("type"=>"overide","id"=>$id,"file"=>$file,"currency"=>$currency,"default"=>$default_currency);
      } elseif ($currency !=NULL && $currency == $default_currency) {
          //We've found a duplicate of the defualt
          $result[] = array("type"=>"duplicate","id"=>$id,"file"=>$file,"currency"=>$currency,"default"=>$default_currency);
      }
    }
  if(isset($result)) {
  return $result;
  } else {
    return FALSE;
  }
}

function theme_missing_currency_table ($activities,$table_id) {
  global $url;
  print("
            <table id='table" . $table_id . "' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>Id</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Validate</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            $i=0;
             foreach ($activities as $activity) {
                $i++; 
                print('
                  <tr>  
                    <td>' . $i . '</td>
                    <td><a href="' . validator_link($url,$activity["file"],$activity["id"]) .'">' . $activity["id"] . '</a></td>
                    <td><a href="' . $url . $activity["file"] .'">' . $activity["file"] .'</a></td>
                    <td><a href="' . validator_link($url,$activity["file"]) . '">Validator</a></td>
                  </tr>
                ');
            }

            print("</tbody>
                </table>");
}

function theme_activity_table ($activities,$table_id) {
  global $url;
  print("
            <table id='table" . $table_id . "' class='sortable'>
              <thead>
                <tr>
                  <th><h3>#</h3></th>
                  <th><h3>Type</h3></th>
                  <th><h3>Id</h3></th>
                  <th><h3>found/default</h3></th>
                  <th><h3>File</h3></th>
                  <th><h3>Validate</h3></th>
                </tr>
              </thead>
              <tbody>
              ");
            $i=0;
            foreach ($activities as $activity_set) {
             // if ($activity !=NULL) {
               //print_r($activity);die;
               foreach ($activity_set as $activity) {
                  $i++; 
                  print('
                    <tr>  
                      <td>' . $i . '</td>
                      <td>' . $activity["type"] . '</td>
                      <td><a href="' . validator_link($url,$activity["file"],$activity["id"]) .'">' . $activity["id"] . '</a></td>
                      <td>' . $activity["currency"] . '/' . $activity["default"] .'</td>
                      <td><a href="' . $url . $activity["file"] .'">' . $activity["file"] .'</a></td>
                      <td><a href="' . validator_link($url,$activity["file"]) . '">Validator</a></td>
                    </tr>
                  ');
              }
            }
            print("</tbody>
                </table>");
}
?>
<script type="text/javascript" src="javascript/tinytable/script.js"></script>
<?php
for ($j=0;$j<5;$j++) {
  ?>
	<script type="text/javascript">
  var sorter<?php echo $j; ?>  = new TINY.table.sorter("sorter<?php echo $j; ?>");
	sorter<?php echo $j; ?>.head = "head";
	sorter<?php echo $j; ?>.asc = "asc";
	sorter<?php echo $j; ?>.desc = "desc";
	sorter<?php echo $j; ?>.even = "evenrow";
	sorter<?php echo $j; ?>.odd = "oddrow";
	sorter<?php echo $j; ?>.evensel = "evenselected";
	sorter<?php echo $j; ?>.oddsel = "oddselected";
	sorter<?php echo $j; ?>.paginate = true;
	sorter<?php echo $j; ?>.currentid = "currentpage";
	sorter<?php echo $j; ?>.limitid = "pagelimit";
	sorter<?php echo $j; ?>.init("table<?php echo $j; ?>");
  </script>

<?php 
}
?>
