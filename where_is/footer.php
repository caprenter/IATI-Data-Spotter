<div id="footer">
  <h3>How it works</h3>
  <p>We run a script that looks in every published IATI file for every IATI element once a day.<br/>
  We store that data and display it here.</p>
  <p>Script last ran:
    <?php
      $dir    = 'data/';
      $files = scandir($dir);
      $banned_folders = array(); //Set up a list of directories to ignore here
      $directories = array(); //An array to store our resluts
      $filetime  = 0;
      //print_r($files); //die;

      foreach($files as $file) {
        if ($file != "." && $file != "..") { //ignore system directories and files
            if (filemtime($dir . $file) > $filetime) {
              $filetime = filemtime($dir . $file);
            }
        }
      }
      echo date("l jS F Y",$filetime) . " at " . date("H:i:s",$filetime);
    ?>
  </p>
  <p>Get the code: <a href="https://github.com/caprenter/IATI-Data-Spotter">https://github.com/caprenter/IATI-Data-Spotter</a></p>
</div>
