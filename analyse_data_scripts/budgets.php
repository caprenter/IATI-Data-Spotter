<?php
//Call this file like:
//php Bill_transaction_table.php dfid
error_reporting(0);
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_budgets.csv';
//echo $output_file;die;

///*************How do we deal with original and revised budgets in the maths**********///


//Get an array of transactions per activity
$data = get_budget_data($dir);


$fh = fopen($output_file, 'w') or die("can't open file");
  fwrite($fh,",");
  foreach ($data["hierarchies"] as $hierarchy) {
    echo "done it";
      fwrite($fh,"Hierarchy " . $hierarchy . ",,");
  }
  fwrite($fh,"\n");
  fwrite($fh,",");
  //fwrite($fh,"No.Activities with budgets,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Elements,Activities,");
  }
  fwrite($fh,"\n");
  fwrite($fh,"All budgets,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,$data["no_budgets"][$hierarchy] . ",");
      fwrite($fh,$data["activities_with"][$hierarchy] . ",");
  }
  fwrite($fh,"\n");
  /*fwrite($fh,"-ve transactions,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,$data["no_negative_transactions"][$hierarchy] . ",");
      fwrite($fh,count(array_unique($data["activities_with_negative_transactions"][$hierarchy])) . ",");
  }
  fwrite($fh,"\n");
    fwrite($fh,'"-ve transactions < -1,000,000",');
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,$data["no_negative_transactions_million"][$hierarchy] . ",");
      fwrite($fh,count(array_unique($data["activities_with_negative_transactions_million"][$hierarchy])) . ",");
  }
  */fwrite($fh,"\n");
  fwrite($fh,"\n");

  //Budget Types
  $all_budget_types = array();
  foreach ($data["hierarchies"] as $hierarchy) {
    //print_r($data["activities_with_transaction_type"][$hierarchy]);die;
    foreach ($data["activities_with_budget_type"][$hierarchy] as $budget_type=>$activities) {
      $types_found[] = $budget_type;
      $all_budget_types = array_merge($all_budget_types,$types_found);
    }
  }
  $all_budget_types = array_unique($all_budget_types);
  
  //Write this to the csv
  fwrite($fh,"Budget Types\n");
 
  foreach ($data["hierarchies"] as $hierarchy) {
     fwrite($fh,",Hierarchy " . $hierarchy . ",");
  }
  fwrite($fh,"\n");
  fwrite($fh,"Types,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Count,Activities,");
  }
  fwrite($fh,"\n");
  foreach ($all_budget_types as $types) {
    fwrite($fh,$types . ",");
    foreach ($data["hierarchies"] as $hierarchy) {
      if (isset($data["activities_with_budget_type"][$hierarchy][$types])) {
         fwrite($fh,count($data["activities_with_budget_type"][$hierarchy][$types]) ."," . count(array_unique($data["activities_with_budget_type"][$hierarchy][$types])));
      } else {
        fwrite($fh,",,");
      }
    }
    fwrite($fh,"\n");
  }
  
  fwrite($fh,"\n");
  fwrite($fh,"\n");
  //die;
  //Currencies
  $all_currencies = array();
  foreach ($data["currencies"] as $hierarcy=>$currencies) {
    $currencies_found = array_unique($currencies);
    $all_currencies = array_merge($all_currencies,$currencies_found);
  }
  //print_r($all_currencies);die;
  fwrite($fh,"Currencies\n");
  //print_r(array_unique($data["currencies"]));die;
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,",Hierarchy " . $hierarchy . ",");
  }
  fwrite($fh,"\n");
  fwrite($fh,"Types,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Count,Activities,");
  }
  fwrite($fh,"\n");
  foreach ($all_currencies as $currency) {
    fwrite($fh,$currency . ",");
    foreach ($data["hierarchies"] as $hierarchy) {
      $currencies = array_count_values($data["currencies"][$hierarchy]);
      if (isset($currencies[$currency])) {
        fwrite($fh,$currencies[$currency] ."," . count(array_unique($data["activities_with_this_currency"][$hierarchy][$currency])));
      } else {
        fwrite($fh,",,");
      }
    }
    fwrite($fh,"\n");
  }
 //die;
  fwrite($fh,"\n");
  
//BudgetsTable
foreach ($data["hierarchies"] as $hierarchy) {
  //Loop through each activity, and process the transactions
  foreach ($all_currencies as $currency) {
  $i=0;
  $commitments = array();
  $total_commitments = 0;
  $first_commitment_value = array();
  $additional_commitment_value = array();
  $total_spending_additional = $total_spending = array();
  $years = array();
    
    //Loop over all the activities with transactions
    foreach ($data["all_transactions"][$hierarchy][$currency] as $id=>$activity) {
    //foreach ($data["all_transactions"][2] as $id=>$activity) {
      $i++;
      //echo $id;
      //print_r($activity); die;
        $commitments = array();
        $spending = array();
        $interests = array();
        $loans = array();
      //Loop through all transactions for this activity. We need to plot disbursement+expenditure against budgets
      foreach ($activity as $transaction) {
        switch ($transaction["type"]) {
          //case 'C':
          //  $commitments[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
          //  break;
          case 'D':
          case 'E':
            $spending[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
            //$years[] = date("Y",strtotime($transaction["date"]));
            break;
          //case 'IR':
          //  $interests[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
           // break;
         // case 'LR':
          //  $loans[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
          //  break;
            
          default:
          break;
        }
      }
      
      //Work out the total spent each year
      if ($spending !=NULL) {
        foreach ($spending as $spend) {
          $date = $spend["date"];
          $year = date("Y",strtotime($date));
          //$years[] = $year;
          //echo $year . PHP_EOL;
          $total_spending[$year] += $spend["value"];
          //$total_spending[$first_commitment_year] += $spend["value"];
        }
      }
    }
    
    //Loop over all the activities with budgets
    foreach ($data["all_budgets"][$hierarchy][$currency] as $id=>$activity) {
      $budgeted = array(); //reset for each activity we loop over
      //Loop through all budgtes for this activity to  We need to plot disbursement+expenditure against budgets
      foreach ($activity as $budget) {
        switch ($budget["type"]) {
          //case 'C':
          //  $commitments[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
          //  break;
          case 'Original':
          case 'Revised':
            //Use start or end?
            //Start
            //$budgeted[] = array("date"=>$budget["start"],"value"=>$budget["value"]);
            //End
            $budgeted[] = array("date"=>$budget["end"],"value"=>$budget["value"]);
            $years[] = date("Y",strtotime($budget["end"]));
            break;
          //case 'IR':
          //  $interests[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
           // break;
         // case 'LR':
          //  $loans[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
          //  break;
            
          default:
          break;
        }
      }
      
      //Work out the total budgeted for each year
      if ($budgeted !=NULL) {
        //print_r($budgeted);die;
        foreach ($budgeted as $budget) {
          $date = $budget["date"];
          $year = date("Y",strtotime($date));
          //$years[] = $year;
          //echo $year . PHP_EOL;
          $total_budgeted[$year] += $budget["value"];
          //$total_spending[$first_commitment_year] += $spend["value"];
        }
      }
    }
      //print_r($years);
    //Find out all the year values to use for our row values in the table
    if (isset($years) && $years !=NULL ) {
      $years = array_unique($years);
      //print_r($years); die;
      sort($years);
      $all_years = $years;
    }
    //print_r($all_years);die;
    
    
    
  
    


    //Write it all to a table

    fwrite($fh,"\n");
    fwrite($fh,"Currency (" . $currency . ")\n");
    fwrite($fh,"Hierarchy " . $hierarchy . "\n");
    fwrite($fh,"Year,Budget,Disbursements+Expenditure,Difference\n");
    foreach ($all_years as $year) {
      $budgeted = $spend = 0;
      if(isset($total_budgeted[$year])) {
        $budgeted = number_format($total_budgeted[$year]);
      }
      if(isset($total_spending[$year])) {
        $spend = number_format($total_spending[$year]);
      }

      $difference = (int)preg_replace("/,/","", $budgeted) - (int)preg_replace("/,/","",$spend);
      $difference = number_format($difference);
      fwrite($fh, '"' . $year . '","'); 
      fwrite($fh,$budgeted . '","'); 
      fwrite($fh,$spend . '","');

      fwrite($fh,$difference . '"' . "\n");

      echo '"' . $year . '","' . $budgeted . '","' . $spend . '","' . $difference . '"' . PHP_EOL;
   }
  } //foreach currency

} 
  
  
  
  
  
  
  
  
  fwrite($fh,"\n");



function get_budget_data($dir) {
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";
    $activities_with = $activities_without = array();
    //$no_negative_transactions = array();
    //$no_negative_transactions_million = array();
    //$activities_with_negative_transactions = array();
    //$activities_with_negative_transactions_million = array();
    $no_budgets = array(); 
    $activities_with_this_currency = array();
    $activities_with_budget_type = array();
    
    
    //$no_budget_types = 0;
    //$total_participating_orgs = 0;
    $fails = 0;
    $types=array();
    $roles=array();
    $this_activity_fails = FALSE;
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
             if ($xml = simplexml_load_file($dir . $file)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $activities = $xml->xpath('//iati-activity');
                    //print_r($attributes); die;
                    foreach ($activities as $activity) {
                        $id = (string)$activity->{'iati-identifier'};
                        $default_currency= (string)$activity->attributes()->{'default-currency'};
                        
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                        if ($hierarchy && $hierarchy !=NULL) {
                          $hierarchy = (string)$activity->attributes()->hierarchy;
                        } else {
                          $hierarchy = 0;
                        }
                        $found_hierarchies[] = $hierarchy; 
                        $no_activities[$hierarchy]++;
                        $activity_count++;
                        
                        $budgets = $activity->budget;


                        if (isset($budgets) && count($budgets) > 0) { //something not quite right here
                          //Count the number of activities that have this element at least once
                          $activities_with[$hierarchy]++;
                          //print_r($our_node);
                          //echo $activities_with;
                          //$no_participating_orgs = 0; //set/rest this counter
                          //Loop through each of the elements
                          foreach ($budgets as $budget) {
                            //print_r($budget);
                            //Counts number of elements of this type in this activity
                            $no_budgets[$hierarchy]++;
                            $budget_start = (string)$budget->{'period-start'}->attributes()->{'iso-date'};
                            $budget_end = (string)$budget->{'period-end'}->attributes()->{'iso-date'};
                            $budget_type = (string)$budget->attributes()->{'type'};
                            //echo $budget_type;
                            if ($budget_type == NULL) {
                              $budget_type = "Missing: Assumed Original";
                            }
                            
                            $activities_with_budget_type[$hierarchy][$budget_type][] = $id;
                            
                            
                            
                            $currency = (string)$budget->{'value'}->attributes()->{'currency'};
                            
                            if ($default_currency !=NULL && $currency !=NULL) {
                              if ($default_currency != $currency) {
                                echo "currency missmatch:" . $default_currency . "|" . $currency . ",";
                                //die;
                              }
                            }
                            
                            if ($currency != NULL) {
                              $currencies[$hierarchy][] = $currency;
                              $activities_with_this_currency[$hierarchy][$currency][]= $id;
                            } elseif ($default_currency !=NULL) {
                              $currencies[$hierarchy][] = $default_currency;
                              $activities_with_this_currency[$hierarchy][$default_currency][]= $id;
                              $currency = $default_currency;
                              
                            } else {
                              $currencies[$hierarchy][] = "non-declared";
                              $activities_with_this_currency[$hierarchy]["non-declared"][]= $id;
                              $currency = "non-declared";
                            }
                            
                            
                            $value = (int)$budget->{'value'};
                            
                        /*    if ($value < 0) {
                              //echo $value;
                                $no_negative_transactions[$hierarchy]++;
                                $activities_with_negative_transactions[$hierarchy][] = $id;
                                if ($value < -1000000) {
                                  $no_negative_transactions_million[$hierarchy]++;
                                  $activities_with_negative_transactions_million[$hierarchy][] = $id;
                                }                                  
                            }
                        */ //echo $budget_date;die;

                            $all_budgets[$hierarchy][$currency][$id][] = array( "start"=>$budget_start,
                                                                                "end" => $budget_end,
                                                                                "type"=>$budget_type,
                                                                                "value"=>$value);
                                                                                
                            
                              //Checks on fails????
                          } //end foreach budget
                            
                        } else {//end if budget exists
                          $activities_without++;
                          $files[(string)$activity->{'iati-identifier'}] = $file;
                        }
                      
                        //Now we also need some transaction info
                        $transactions = $activity->transaction;


                        if (isset($transactions) && count($transactions) > 0) { //something not quite right here
                          //Count the number of activities that have this element at least once
                          //$activities_with[$hierarchy]++;
                          //print_r($our_node);
                          //echo $activities_with;
                          //$no_participating_orgs = 0; //set/rest this counter
                          //Loop through each of the elements
                          foreach ($transactions as $transaction) {
                            //print_r($transaction);
                            //Counts number of elements of this type in this activity
                            //$no_transactions[$hierarchy]++;
                            $transaction_date = (string)$transaction->{'transaction-date'}->attributes()->{'iso-date'};
                            $transaction_type = (string)$transaction->{'transaction-type'}->attributes()->{'code'};
                            
                            //$activities_with_transaction_type[$hierarchy][$transaction_type][] = $id;
                                                        
                            $currency = (string)$transaction->{'value'}->attributes()->{'currency'};
                            
                            if ($default_currency !=NULL && $currency !=NULL) {
                              if ($default_currency != $currency) {
                                echo "currency missmatch:" . $default_currency . "|" . $currency . ",";
                                //die;
                              }
                            }
                            
                            if ($currency != NULL) {
                              //$currencies[$hierarchy][] = $currency;
                              //$activities_with_this_currency[$hierarchy][$currency][]= $id;
                            } elseif ($default_currency !=NULL) {
                              //$currencies[$hierarchy][] = $default_currency;
                              //$activities_with_this_currency[$hierarchy][$default_currency][]= $id;
                              $currency = $default_currency;
                              
                            } else {
                              //$currencies[$hierarchy][] = "non-declared";
                              //$activities_with_this_currency[$hierarchy]["non-declared"][]= $id;
                              $currency = "non-declared";
                            }
                            
                            
                            $value = (int)$transaction->{'value'};
                            

                            $all_transactions[$hierarchy][$currency][$id][] = array("date"=>$transaction_date,
                                                                        "type"=>$transaction_type,
                                                                        "value"=>$value);
                              //Checks on fails????
                          } //end foreach transaction
                        }
                        

                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  //print_r($activities_with) . PHP_EOL;
  //print_r($no_budgets) . PHP_EOL;
  //echo $fails . PHP_EOL;
  //echo $activities_without . PHP_EOL;

  $found_hierarchies = array_unique($found_hierarchies);
  sort($found_hierarchies);
  //print_r($found_hierarchies);
  //print_r($files);
  //print_r($all_transactions);
  //print_r($no_negative_transactions);
  //die;
  //print_r($activities_with_this_currency);die;
  //print_r($activities_with_budget_type);
  
  
  return array("activities_with" => $activities_with,
          "all_budgets" => $all_budgets,
          "all_transactions" => $all_transactions,
          "hierarchies"=>$found_hierarchies, 
          "no_budgets" => $no_budgets,
          //"no_negative_transactions" => $no_negative_transactions,
          //"no_negative_transactions_million" => $no_negative_transactions_million,
          //"activities_with_negative_transactions" => $activities_with_negative_transactions,
          //"activities_with_negative_transactions_million" => $activities_with_negative_transactions_million,
          "currencies" => $currencies,
          "activities_with_this_currency" => $activities_with_this_currency,
          "activities_with_budget_type" => $activities_with_budget_type,
          );
  //if (isset($types)) {
  //  return $types;
  //} else {
  //  return FALSE;
  //}
}

function sort_by_Date( $a, $b ) {
    return strtotime($a["date"]) - strtotime($b["date"]);
}

?>
