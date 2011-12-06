<?php
//Thanks to: http://forums.devshed.com/xml-programming-19/validating-xml-against-xsd-with-php-430794.html
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $myinputs['group'] . '.php'; //sanitized $_GET['group']
  require_once 'functions/validator_link.php';
  
    // Enable user error handling
    libxml_use_internal_errors(true);

    //$xsd = "http://iatistandard.org/downloads/iati-activities-schema.xsd";
    //$invalid = FALSE;

    if ($handle = opendir($dir)) {
          /* This is the correct way to loop over the directory. */
          while (false !== ($file = readdir($handle))) {
              if ($file != "." && $file != "..") { //ignore these system files
                  $xml = new DOMDocument();
                  $xml->load($dir . $file);
                  
                  if ($xml->getElementsByTagName("iati-organisation")->length == 0) {
                    $xsd = "http://iatistandard.org/downloads/iati-activities-schema.xsd";
                    if ($myinputs['org'] == "1") { //sanitized $_GET['orgs']
                      continue;
                    }
                  } else {
                    $xsd = "http://iatistandard.org/downloads/iati-organisations-schema.xsd";
                  }

                  
                  if ($xml->schemaValidate($xsd)) {
                      $good_files[] = $file; 
                      //print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
                      //if (isset($_GET['errors']) && $_GET['errors'] == "all") {
                      //  libxml_display_all_errors();
                      //} else {
                      //  libxml_display_just_files();
                      //}
                      $invalid = TRUE;
                  } else {
                      $invalid_files[] = $file;
                  }
                  
                  
              }// end if file is not a system file
          } //end while
          closedir($handle);
    }

  print('<div id="main-content">
            <h4>Validation');
              if ($myinputs['org'] == "1") { 
                echo " - Organisation Files"; 
              } else { 
                echo " - Activity Files"; 
              }
            print('</h4>');
    if (isset($invalid_files)) {
        print("<br/>Files with validation problems: ");
        print("<span class=\"smaller\">[View:  <a id=\"p1\" href=\"?group=" . $myinputs['group'] . "&amp;org=1\">Organisation files only</a>]</span><br/>");
        if (isset($myinputs['org']) && $myinputs['org'] == "1") {
          print("<script type=\"text/javascript\">
                    document.getElementById(\"p1\").innerHTML=\"All files\";
                    document.getElementById(\"p1\").href=\"?group=" . $myinputs['group'] . "\";

                  </script>"
                );
        }
        print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>#</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validate</h3></th>
                    </tr>
                  </thead>
                  <tbody>");
        $i=0;
        foreach($invalid_files as $file) {
            $i++;
            echo '<tr>';
            echo '<td>'. $i . '</td>';
            echo '<td><a href="' . $url . $file .'">' . $file .'</a></td>';
            echo '<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
        }
          print("</tbody></table>");

    /**/
    } elseif (isset($good_files)){
        print("<br/>These files validate against <a href=\"" . $xsd . "\">" . $xsd . "</a><br/>");
        print("<span class=\"smaller\">[View:  <a id=\"p1\" href=\"?group=" . $myinputs['group'] . "&amp;org=1\">Organisation files only</a>]</span><br/>");
        if (isset($myinputs['org']) && $myinputs['org'] == "1") {
          print("<script type=\"text/javascript\">
                    document.getElementById(\"p1\").innerHTML=\"All files\";
                    document.getElementById(\"p1\").href=\"?group=" . $myinputs['group'] . "\";

                  </script>"
                );
        }
        print("<table id='table1' class='sortable'>
                  <thead>
                    <tr>
                      <th><h3>#</h3></th>
                      <th><h3>File</h3></th>
                      <th><h3>Validate</h3></th>
                    </tr>
                  </thead>
                  <tbody>");
        $i=0;
        foreach($good_files as $file) {
            $i++;
            echo '<tr>';
            echo '<td>'. $i . '</td>';
            echo '<td><a href="' . $url . $file .'">' . $file .'</a></td>';
            echo '<td><a href="' . validator_link($url,$file) . '">Validator</a></td></tr>';
        }
        print("</tbody></table>");
    } else {
        print("<br/>No files found. ");
        print("<span class=\"smaller\">[View:  <a id=\"p1\" href=\"?group=" . $myinputs['group'] . "&amp;org=1\">Organisation files only</a>]</span><br/>");
        if (isset($myinputs['org']) && $myinputs['org'] == "1") {
          print("<script type=\"text/javascript\">
                    document.getElementById(\"p1\").innerHTML=\"All files\";
                    document.getElementById(\"p1\").href=\"?group=" . $myinputs['group'] . "\";

                  </script>"
                );
        }
    }
  print('</div>');
}


function libxml_display_error($error) {
  print_r($error);
    $return = "<br/>\n";
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<b>Warning $error->code</b>: ";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<b>Error $error->code</b>: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<b>Fatal Error $error->code</b>: ";
            break;
    }
    $return .= trim($error->message);
    if ($error->file) {
        $return .=    " in <b>$error->file</b>";
    }
    $return .= " on line <b>$error->line</b>\n";

    return $return;
}

function libxml_display_all_errors() {
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        print libxml_display_error($error);
    }
    libxml_clear_errors();
}

function libxml_display_just_files() {
  $errors = libxml_get_errors();
  //print_r($errors);
    foreach ($errors as $error) {
        if ($error->file) {
          $files[] = $error->file;
        }
    }
    $files = array_unique($files);
    libxml_clear_errors();
    return $files;
}
?>
