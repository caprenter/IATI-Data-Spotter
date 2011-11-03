<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';

  $flag = FALSE;
  $files = array();
  //$dir = './test/DFID/';
  if ($handle = opendir($dir)) {
      //echo "Directory handle: $handle\n";
      //echo "Files:\n";

      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
              //echo $file . PHP_EOL;
              //$content = file($dir . $file); //Puts whole file into an array -not good for big files
              $content = file_get_contents($dir . $file, NULL, NULL, 0, 50); //just reads first 50 chars - faster!
              //First line: $content;
              if (strstr($content, '<!DOCTYPE') || strstr($content, '<html')) {
                   array_push($files,$file);
                    $flag = TRUE;
              }
          }
      } 
       print('<div id="main-content">
                <h4>Missing URLs</h4>
                <p>This is a basic test to see that if when we drag our files down to the server we get any HTML pages by accident</p>'
                );
          if ($flag) {
            print('<p class="cross">These files contain HTML markup on line one which may indicate the URL takes you to a page not found</p>');
            foreach($files as $file) {
                echo '<a href="' .$url . $file . '">' . $file . '</a><br/>';
              }
          } else {
                    print('<p class="tick">Passed</p><p>No HTML markup found at the begining of any files</p>');
          }
          print('</div>');       
  }
}
?>
