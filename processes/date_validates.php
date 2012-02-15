<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
    //Include variables for each group. Use group name for the argument
    //e.g. php detect_html.php dfid
    require_once 'variables/' .  $_GET['group'] . '.php';
    require_once 'functions/xml_child_exists.php';
    require_once 'functions/validator_link.php';
    require_once 'functions/bad_files_table.php';
    
    if ((int)$myinputs['showall'] == "1") { //sanitized $_GET['orgs']
      $showall = 1;
    } else {
      $showall = 0;
    }
    
    $cross = '0 <img src="theme/images/cross.png" alt="cross"/>';
    $tick = '1 <img src="theme/images/tick.png" alt="tick"/>';
    $cross = 'x';
    $tick = '&#10003;';
    
    $results = check_date_attributes ($dir);
    //print_r($results);
    print('<div id="main-content">
            <h4>Checking for @iso-date</h4>
            <p>Found on activity-date, transaction-date, period-start, period-end</p>'
          );
      $dates = $results["dates"];
      if ($dates != NULL) {
        if ($showall == 1) {
         print('<h4>All Activities</h4>
                <span class="smaller">[<a href="?group=' . $myinputs['group'] . '">Show failing activities only</a></span>]');
       } else {
         print('<h4>Activities with invalid dates</h4>
                <span class="smaller">[<a href="?group=' . $myinputs['group'] . '&amp;showall=1">Show all activities</a></span>]');
       }
         print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>#</h3></th>
                      <th><h3>Id</h3></th>
                      <th><h3>iso-date</h3></th>
                      <th><h3>0/1</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validate</h3></th>
                    </tr>
                  </thead>
                  <tbody>");
        $i=0;
        $file = "3";
        foreach ($dates as $id=>$values) {
          foreach ($values["iso-dates"] as $value) {
              //$i++;
              $valid_date = valid_date((string)$value->attributes()->{'iso-date'}); //check to see if date is valid TRUE/FALSE
              if (!$valid_date || $showall == 1) { //echo rows if date is invalid or instructed to show all.
              $i++;
                echo '<tr>';
                echo '<td>'. $i . '</td>';
                echo '<td><a href="' . validator_link($url,$values["file"],$id) .'">' . $id . '</a></td>';
                echo '<td>' . (string)$value->attributes()->{'iso-date'} . '</td>';                echo '<td>' . ($valid_date ? $tick:$cross) . '</td>';
                echo '<td><a href="' . $url . $values["file"] .'">' . $values["file"] .'</a></td>';
                echo '<td><a href="' . validator_link($url,$values["file"]) . '">Validator</a></td></tr>';
              }

          }
        }
      

    
          print("</tbody>
        </table>");
      } else {
        print('<p class="check">No @iso-date attributes found</p>');
      }
        
      if ($results["bad-files"] !=NULL) {
        theme_bad_files($bad_files,$url);
      }

    print('</div>');
}


function check_date_attributes ($dir) {
    //global $dir;
    //$missing= array();
    $files = array();
    //$rows = '';
    $fails = array();
    $fails_value = $fails_value_date = $fails_date = $fails_type = $fails_iso_start = $fails_iso_end = array();
    if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
            if ($xml = @simplexml_load_file($dir . $file)) {
              if(!xml_child_exists($xml, "//iati-organisation")) { //ignore org files

                    foreach ($xml as $activity) {
                        $id = (string)$activity->{'iati-identifier'};
                        $iso_dates = $activity->xpath(".//*[@iso-date]");
                        if ($iso_dates) {
                          $dates[$id] = array("iso-dates" => $iso_dates, "file" => $file);
                        }
                       // $dates[$id] = array_merge($dates[$id],$activity->xpath("*[@value-date]"));
                        //print_r($dates); die;
                    }

                } //end organisation file check
              } else {
                  $bad_files[] = $file;
              }
                  
            }// end if file is not a system file
            
          } //end while
          closedir($handle);
          
    }
    if (!isset($dates)) {
      $dates = NULL;
    }
    if(!isset($bad_files)) {
      $bad_files = NULL;
    }
    return array("dates" => $dates,
                  "bad-files" => $bad_files,
                  );

}

function valid_date ($string) {
  if (strtotime($string)) {
    $length = strlen($string);
    switch ($length) {
      case 10:
       return TRUE;
       break;
      case 11:
       if (substr($string,-1) == "Z") {
         return TRUE;
       }
       break;
       case 16:
        if ((strstr($string, '-') || strstr($string, '+')) && strstr($string, ':')) {
          $this_timezone = substr($string,-5);
          echo $this_timezone;
          $this_timezone = explode(":",$this_timezone);
          print_r($this_timezone);
          if ($this_timezone[0] <= 14) {
            return TRUE;
          } else {
            return FALSE;
          }
        }
        break;
      default:
        return FALSE;
        break;
      }
    ///echo strtotime($string);
    //echo ($string) .":" .strtotime($string) .":".strlen($string). "---";  
  } else {
    return FALSE;
  }
}
?>
