<?php
//Call this file like:
//php Bill_transaction_table.php dfid
error_reporting(0);
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . $corpus . '_transactions.csv';
//echo $output_file;die;


//Get an array of transactions per activity
$data = get_transaction_data ($dir);


$fh = fopen($output_file, 'w') or die("can't open file");
  fwrite($fh,",");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,");
  }
  fwrite($fh,"\n");
  fwrite($fh,",");
  //fwrite($fh,"No.Activities with transactions,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Elements,Activities,");
  }
  fwrite($fh,"\n");
  fwrite($fh,"All transactions,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,$data["no_transactions"][$hierarchy] . ",");
      fwrite($fh,$data["activities_with"][$hierarchy] . ",");
  }
  fwrite($fh,"\n");
  fwrite($fh,"-ve transactions,");
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
  fwrite($fh,"\n");
  fwrite($fh,"\n");
  
  //Transaction Types
  $all_transaction_types = array();
  foreach ($data["hierarchies"] as $hierarchy) {
    //print_r($data["activities_with_transaction_type"][$hierarchy]);die;
    foreach ($data["activities_with_transaction_type"][$hierarchy] as $transaction_type=>$activities) {
      $types_found[] = $transaction_type;
      $all_transaction_types = array_merge($all_transaction_types,$types_found);
    }
  }
  $all_transaction_types = array_unique($all_transaction_types);
  
  //Write this to the csv
  fwrite($fh,"Transaction Types\n");
 
  foreach ($data["hierarchies"] as $hierarchy) {
     fwrite($fh,",Hierarchy " . $hierarchy . ",");
  }
  fwrite($fh,"\n");
  fwrite($fh,"Types,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Count,Activities,");
  }
  fwrite($fh,"\n");
  foreach ($all_transaction_types as $types) {
    fwrite($fh,$types . ",");
    foreach ($data["hierarchies"] as $hierarchy) {
      if (isset($data["activities_with_transaction_type"][$hierarchy][$types])) {
         fwrite($fh,count($data["activities_with_transaction_type"][$hierarchy][$types]) ."," . count(array_unique($data["activities_with_transaction_type"][$hierarchy][$types])) . ",");
      } else {
        fwrite($fh,",,");
      }
    }
    fwrite($fh,"\n");
  }
  
  fwrite($fh,"\n");
  fwrite($fh,"\n");
  //Currencies
  $all_currencies = array();
  foreach ($data["currencies"] as $hierarcy=>$currencies) {
    $currencies_found = array_unique($currencies);
    $all_currencies = array_merge($all_currencies,$currencies_found);
  }
  $all_currencies = array_unique($all_currencies);
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
        fwrite($fh,$currencies[$currency] ."," . count(array_unique($data["activities_with_this_currency"][$hierarchy][$currency])) . ",");
      } else {
        fwrite($fh,",,");
      }
    }
    fwrite($fh,"\n");
  }
  //die;
  fwrite($fh,"\n");
  
//Transaction Table
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
  
    foreach ($data["all_transactions"][$hierarchy][$currency] as $id=>$activity) {
    //foreach ($data["all_transactions"][2] as $id=>$activity) {
      $i++;
      //echo $id;
      //print_r($activity); die;
        $commitments = array();
        $spending = array();
        $interests = array();
        $loans = array();
      //Loop through all transactions for this activity
      foreach ($activity as $transaction) {
        switch ($transaction["type"]) {
          case 'C':
            $commitments[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
            break;
          case 'D':
          case 'E':
            $spending[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
            break;
          case 'IR':
            $interests[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
            break;
          case 'LR':
            $loans[] = array("date"=>$transaction["date"],"value"=>$transaction["value"]);
            break;
            //IF	Incoming Funds	Funds received from an external funding source (eg a donor).
            //R	Reimbursement
            
          default:
          break;
        }
      }

      //First commitment goes into commitments for the year
      //Additional commitments get grouped by year.
      usort($commitments, "sort_by_date");
      
      //usort($spending, "sort_by_date");

    
      /*$commitments = array(array("date"=>"2012-04-02Z", "value"=>1234),
                        array("date"=>"2012-03-02Z", "value"=>1234)
                        );
    */
      //print_r($commitments);die;
      $j=0;
      if (isset($first_commitment_year)) {
        unset($first_commitment_year);
      }

      if ($commitments != NULL) {
        foreach ($commitments as $commitment) {
          $total_commitments += $commitment["value"];
          if ($j==0) {
            //First commitment gives us the year to report eveything against
            $date = $commitment["date"];
            $year = date("Y",strtotime($date));
            $first_commitment_value[$year] += $commitment["value"];
            $first_commitment_year = $year;
            //echo $first_commitment_year;
            //echo $commitment["value"];
            $j++;
          } else {
            $additional_commitment_value[$first_commitment_year] += $commitment["value"];
          }
        }
      }
      
      //Now place disbursements in the year of first commit.
      //Check that the year is set...if not disbursements go in the year of transaction date.
      //Need to save the years we've assigned if not using first_commitment_year
     
      if (isset($first_commitment_year)) {
        foreach ($spending as $spend) {
          $total_spending[$first_commitment_year] += $spend["value"];
        }
      } else {
        //echo "no commitment found(expend).";
        //echo $id .PHP_EOL;
        if ($spending !=NULL) {
          foreach ($spending as $spend) {
            $date = $spend["date"];
            $year = date("Y",strtotime($date));
            $years[] = $year;
            echo $year . PHP_EOL;
            $total_spending_additional[$year] += $spend["value"];
            //$total_spending[$first_commitment_year] += $spend["value"];
          }
        }
      }
      
      //Now place loans in the year of first commit.
      //Check that the year is set...if not loans go in the year of transaction date.
      if (isset($first_commitment_year)) {
        foreach ($loans as $loan) {
          $total_loans[$first_commitment_year] += $loan["value"];
        }
      } else {
        //echo "no commitment found(loans).";
        if ($loans !=NULL) {
          foreach ($loans as $loan) {
            $date = $loan["date"];
            $year = date("Y",strtotime($date));
            $years[] = $year;
            echo $year . PHP_EOL;
            $total_loans[$year] += $loan["value"];
            //$total_spending[$first_commitment_year] += $spend["value"];
          }
        }
      }
      
      //Now place interest in the year of first commit.
      //Check that the year is set...if not interest goes in the year of transaction date.
      if (isset($first_commitment_year)) {
        foreach ($interests as $interest) {
          $total_interest[$first_commitment_year] += $interest["value"];
        }
      } else {
        //echo "no commitment found(interest).";
        if ($interests !=NULL) {
          foreach ($interests as $interest) {
            $date = $interest["date"];
            $year = date("Y",strtotime($date));
            $years[] = $year;
            //echo $year . PHP_EOL;
            $total_interest[$year] += $interest["value"];
            //$total_spending[$first_commitment_year] += $spend["value"];
          }
        }
      }
      
        //print_r($total_spending);
    } //foreach activity
    //print_r($total_interest);die;
    //print_r($years); die;
    //$all_years = array_merge(array_keys($total_spending),array_keys($first_commitment_value));
    $all_years = array_keys($first_commitment_value);
    if (isset($years) && $years !=NULL ) {
      $years = array_unique($years);
      //print_r($years); die;
      $all_years = array_merge($years, $all_years);
    }
    //print_r($all_years);die;

    if ($additional_commitment_value !=NULL) {
      $all_years = array_merge($all_years,array_keys($additional_commitment_value));
    }
    //$all_years = sort($all_years);
    $all_years = array_unique($all_years);
    sort($all_years);
    print_r($all_years);

    fwrite($fh,"\n");
    fwrite($fh,"Currency (" . $currency . ")\n");
    fwrite($fh,"Hierarchy " . $hierarchy . "\n");
    fwrite($fh,"Year,Commitment,Additional Commitments,Disbursements+Expenditure,Loans,Commitment Outstanding (B+C-(D-E)),Additional Dis+Exp,Interest\n");
    foreach ($all_years as $year) {
      $commitment = $ad_commitment = $spend =  $spend_additional_value = $loan_value =  $interest_value = 0;
      if(isset($first_commitment_value[$year])) {
        $commitment  = number_format($first_commitment_value[$year]);
      } 
      if(isset($additional_commitment_value[$year])) {
        $ad_commitment  = number_format($additional_commitment_value[$year]);
      }
      if(isset($total_spending[$year])) {
        $spend = number_format($total_spending[$year]);
      }
      if(isset($total_spending_additional[$year])) {
        $spend_additional_value = number_format($total_spending_additional[$year]);
      }
      if(isset($total_loans[$year])) {
        $loan_value = number_format($total_loans[$year]);
      }
      if(isset($total_interest[$year])) {
        $interest_value = number_format($total_interest[$year]);
      }
      $difference = (int)preg_replace("/,/","", $commitment) + (int)preg_replace("/,/","",$ad_commitment) - ((int)preg_replace("/,/","",$spend) - (int)preg_replace("/,/","",$loan_value));
      $difference = number_format($difference);
      fwrite($fh, '"' . $year . '","'); 
      fwrite($fh,$commitment . '","'); 
      fwrite($fh,$ad_commitment . '","');
      fwrite($fh,$spend . '","');
      
      
      //loan
      fwrite($fh,$loan_value . '","');
      fwrite($fh,$difference . '","');
      //additional
      fwrite($fh,$spend_additional_value . '","');
      //interest
      fwrite($fh,$interest_value . '"' . "\n");
      echo '"' . $year . '","' . $commitment . '","' . $ad_commitment . '","' . $spend . '","' . $difference . '"' . PHP_EOL;
   }
  } //foreach currency

} 
  
  
  
  
  
  
  
  
  fwrite($fh,"\n");



function get_transaction_data($dir) {
  
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";
    $activities_with = $activities_without = array();
    $no_negative_transactions = array();
    $no_negative_transactions_million = array();
    $activities_with_negative_transactions = array();
    $activities_with_negative_transactions_million = array();
    $no_transactions = array(); 
    $activities_with_this_currency = array();
    $activities_with_transaction_type = array();
    
    
    $no_transaction_types = 0;
    $total_participating_orgs = 0;
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
                        if (in_array($id, array("44000-P008034",
                                                "44000-P008037",
                                                "44000-P008045",
                                                "44000-P008046",
                                                "44000-P008047",
                                                "44000-P008048",
                                                "44000-P008050",
                                                "44000-P008051",
                                                "44000-P008055",
                                                "44000-P008058",
                                                "44000-P008059",
                                                "44000-P008062"))) {
                                                  echo $file . PHP_EOL;
                                                }
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
                        
                        $transactions = $activity->transaction;
                        if (count($transactions) == 0) {
                          echo $id;
                          //die;
                        }

                        if (isset($transactions) && count($transactions) > 0) { //something not quite right here
                          //Count the number of activities that have this element at least once
                          $activities_with[$hierarchy]++;
                          //print_r($our_node);
                          //echo $activities_with;
                          //$no_participating_orgs = 0; //set/rest this counter
                          //Loop through each of the elements
                          foreach ($transactions as $transaction) {
                            //print_r($transaction);
                            //Counts number of elements of this type in this activity
                            $no_transactions[$hierarchy]++;
                            $transaction_date = (string)$transaction->{'transaction-date'}->attributes()->{'iso-date'};
                            $transaction_type = (string)$transaction->{'transaction-type'}->attributes()->{'code'};
                            
                            if ($transaction_type == NULL) {
                              $transaction_type = "Missing";
                              echo "missing";
                            }
                            if ($transaction_type !="D") {
                              echo $id;
                              //die;
                            }
                            $activities_with_transaction_type[$hierarchy][$transaction_type][] = $id;
                            
                            
                            
                            $currency = (string)$transaction->{'value'}->attributes()->{'currency'};
                            
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
                            
                            
                            $value = (int)$transaction->{'value'};
                            
                            if ($value < 0) {
                              //echo $value;
                                $no_negative_transactions[$hierarchy]++;
                                $activities_with_negative_transactions[$hierarchy][] = $id;
                                if ($value < -1000000) {
                                  $no_negative_transactions_million[$hierarchy]++;
                                  $activities_with_negative_transactions_million[$hierarchy][] = $id;
                                }                                  
                            }
                            //echo $transaction_date;die;

                            $all_transactions[$hierarchy][$currency][$id][] = array("date"=>$transaction_date,
                                                                        "type"=>$transaction_type,
                                                                        "value"=>$value);
                              //Checks on fails????
                          } //end foreach transaction
                            
                        } else {//end if transaction exists
                          $activities_without++;
                          $files[(string)$activity->{'iati-identifier'}] = $file;
                        }

                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  //print_r($activities_with) . PHP_EOL;
  //print_r($no_transactions) . PHP_EOL;
  //echo $fails . PHP_EOL;
  //echo $activities_without . PHP_EOL;

  $found_hierarchies = array_unique($found_hierarchies);
  sort($found_hierarchies);
  //print_r($files);
  //print_r($all_transactions);
  //print_r($no_negative_transactions);
  //die;
  //print_r($activities_with_this_currency);die;
  //print_r($activities_with_transaction_type);
  
  
  return array("activities_with" => $activities_with,
          "all_transactions" => $all_transactions,
          "hierarchies"=>$found_hierarchies, 
          "no_transactions" => $no_transactions,
          "no_negative_transactions" => $no_negative_transactions,
          "no_negative_transactions_million" => $no_negative_transactions_million,
          "activities_with_negative_transactions" => $activities_with_negative_transactions,
          "activities_with_negative_transactions_million" => $activities_with_negative_transactions_million,
          "currencies" => $currencies,
          "activities_with_this_currency" => $activities_with_this_currency,
          "activities_with_transaction_type" => $activities_with_transaction_type,
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
