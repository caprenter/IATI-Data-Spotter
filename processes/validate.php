<?php
//Thanks to: http://forums.devshed.com/xml-programming-19/validating-xml-against-xsd-with-php-430794.html
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';
  
    // Enable user error handling
    libxml_use_internal_errors(true);

    $xsd = "http://iatistandard.org/downloads/iati-activities-schema.xsd";
    $invalid = FALSE;

    if ($handle = opendir($dir)) {
          /* This is the correct way to loop over the directory. */
          while (false !== ($file = readdir($handle))) {
              if ($file != "." && $file != "..") { //ignore these system files
                  $xml = new DOMDocument();
                  $xml->load($dir . $file);
                  
                  if (!$xml->schemaValidate($xsd)) {
                      $files[] = $file; 
                      //print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
                      //if (isset($_GET['errors']) && $_GET['errors'] == "all") {
                      //  libxml_display_all_errors();
                      //} else {
                      //  libxml_display_just_files();
                      //}
                      $invalid = TRUE;
                  }
                  
                  
              }// end if file is not a system file
          } //end while
          closedir($handle);
    }


    if ($invalid) {
        print("<br/>Files with validation problems:<br/>");
        foreach($files as $file) {
          echo $file . "<br/>";
        }

    } else {
        print("<br/>All files validate against $xsd");
    } 

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
