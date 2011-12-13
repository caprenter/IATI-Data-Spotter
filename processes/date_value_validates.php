<?php
/*
 *      date_value_validates.php
 *      
 *      Copyright 2011 David Carpenter <caprenter@gmail.com>
 *      
 *      This file is part of IATI-Data-Spotter.
 *      
 *      IATI-Data-Spotter is free software: you can redistribute it and/or modify
 *      it under the terms of the GNU Affero General Public License as published by
 *      the Free Software Foundation, either version 3 of the License, or
 *      (at your option) any later version.
 *      
 *      IATI-Data-Spotter is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU Affero General Public License for more details.
 *      
 *      You should have received a copy of the GNU Affero General Public License
 *      along with IATI-Data-Spotter.  If not, see <http://www.gnu.org/licenses/>.
 *      
 */

/**
 * 
 * Checks all @value-date attributes in the document to see if they are in a valid
 * date format.
 * 
 * @package IATI-Data-Spotter
 * @author David Carpenter caprenter@gmail.com
 * @license GNU Affero General Public License v3
 */

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
            <h4>Checking for @value-date</h4>
            <p>Found on value.</p>'
          );
      $dates = $results["dates"];
      if ($dates != NULL) {
          //print_r($dates);
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

                      <th><h3>@value-date</h3></th>
                      <th><h3>0/1</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validate</h3></th>
                    </tr>
                  </thead>
                  <tbody>");
        $i=0;
        foreach ($dates as $id=>$values) {
          //echo $id;
          //print_r($values);

          $file = $values["file"]; //echo $file; //die;
          foreach ($values["attributes"] as $value) {
              $valid_date = valid_date((string)$value->attributes()->{'value-date'}); //check to see if date is valid TRUE/FALSE
              if (!$valid_date || $showall == 1) { //echo rows if date is invalid or instructed to show all.
                    $i++;
                    echo '<tr>';
                    echo '<td>'. $i . '</td>';
                    echo '<td><a href="' . validator_link($url,$file,$id) .'">' . $id . '</a></td>';
                   // echo '<td>' . (string)$value->attributes()->type  .'</td>';
                    echo '<td>' . (string)$value->attributes()->{'value-date'} . '</td>';
                    echo '<td>' . ($valid_date ? $tick:$cross) . '</td>';
                    echo '<td><a href="' . $url . $file .'">' . $file .'</a></td>';
                    echo '<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
              }
          }
        }
          print("</tbody>
            </table>");
      } else {
        print('<p class="check">No @value-date attributes found</p>');
      }
      if ($results["bad-files"] !=NULL) {
        theme_bad_files($bad_files,$url);
      }
     

    print('</div>');
}

/**
 * Parses each XML file in a directory looking for @value-date attributes.
 * If found it tries to validate them, and returns id, file and date for display.
 * 
 * name: check_date_attributes
 * @param string $dir A directory containing files in IATI xml format
 * @return mixed An array of dates and optionally, files that failed to parse
 */

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
                      if (count($activity->children()) > 0 ) { //skips registry record entries
                        $id = (string)$activity->{'iati-identifier'};
                        $dates[$id] = array("file"=>$file, 
                                            "attributes"=>$activity->xpath("*/*[@value-date]")
                                            );
                       // $dates[$id] = array_merge($dates[$id],$activity->xpath("*[@value-date]"));
                        //print_r($dates); die;
                      }
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
/**
 * Some simple checks to validate a given string as an iso date format
 * 
 * We only perform 2 checks at the moment. One is a simple string length.
 * The other is to see if strtotime returns TRUE
 * 
 * name: valid_date
 * @param string $string Hopefully a string in a yyyy-mm-dd format
 * @return bool True if a valid date, FALSE if not
 */

function valid_date ($string) {
  if (strtotime($string) && strlen($string) == 10) {
    ///echo strtotime($string);
    //echo ($string) .":" .strtotime($string) .":".strlen($string). "---";
    return TRUE;    
  } else {
    return FALSE;
  }
}
?>
