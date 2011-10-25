<html>
  <head>
    <title>IATI data Batch Tests</title>
  </head>
<body>
  
    <?php
      $_SERVER['DOCUMENT_ROOT'] =  "http://localhost/Webs/aidinfo-2/";
      //bring in the $available_groups array
      require_once("variables/available_groups.php");
      
      //Filter GET vars
      $args = array(
        'group'   => FILTER_SANITIZE_ENCODED
      );
      $myinputs = filter_input_array(INPUT_GET, $args);
      if (in_array($myinputs['group'],array_keys($available_groups))) {
        print("<h2>Select Test</h2>");
        echo "<ul>";
        if ($handle = opendir('processes')) {
            //echo "Directory handle: $handle\n";
            //echo "Files:\n";

            /* This is the correct way to loop over the directory. */
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") { //ignore these system files
                    //echo $file . PHP_EOL;
                    //load the xml
                    print('<li><a href="' . $_SERVER['DOCUMENT_ROOT'] . 'processes/' . $file . 'process.php?group=' . $myinputs['group'] . '">'  . ucwords($file) .  '</a></li>');
                    
                }// end if file is not a system file
            } //end while
            closedir($handle);
            echo "</ul>";
        }
      } else {
        print("<h2>Select Data Group</h2><ul></ul>");
        foreach ($available_groups as $key => $value) { 
          echo '<li><a href="statistics.php?group=' . $key .'">' . $value . '</a> (' . $key . ')</li>';
        }
        print("</ul>");
      }
?>

</body>
</html>
