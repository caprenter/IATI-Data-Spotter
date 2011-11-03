<?php
if (in_array($myinputs['group'],array_keys($available_groups))) {
  //Include variables for each group. Use group name for the argument
  //e.g. php detect_html.php dfid
  require_once 'variables/' .  $_GET['group'] . '.php';

    $i=0; //count no.of fails
    $files = array(); //store failoed files

    if ($handle = opendir($dir)) {
        //echo "Directory handle: $handle\n";
        //echo "Files:\n";

        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") { //ignore these system files
                //echo $file . PHP_EOL;
                $i++;
                //$content = file($dir . $file); //Puts whole file into an array -not good for big files
                $content[0] = file_get_contents($dir . $file, NULL, NULL, 0, 50); //just reads first 50 chars - faster!
                
                //First line: $content[0];
                //echo htmlspecialchars($content[0]) . '<br/>';
                //Look for xml tag
                if (strstr($content[0], '<?xml version="1.0" encoding="UTF-16"?>')) {
                    continue;
                    //echo $i . " " . $file  . ' dirty html' .PHP_EOL;
                    
                } elseif (strstr($content[0], '<?xml version="1.0" encoding="UTF-8"?>')) {
                    continue;
                } else {
                    array_push($files,$file);
                    //echo $i . " " . $file  . ' dirty html' .PHP_EOL; {
                  //echo "investigate: " .$file;
                }
            }
        }        
    }
    print('<div id="main-content">');
      theme_xml_header_check ($files,$i,$url);
    print('</div>');
  

}


function theme_xml_header_check ($files,$i,$url) {
    print("<h4>Files should begin with a correct XML declaration</h4>
          <p>Either: &lt;?xml version=\"1.0\" encoding=\"UTF-8\"?&gt; or &lt;?xml version=\"1.0\" encoding=\"UTF-16\"?&gt;</p>
          <p class='fail'>" . count($files) . " out of " . $i . " files failed this test.</p>
          <p>
            <a href=\"#\" onclick=\"toggle_visibility('foo');\">Show files:</a>
          </p>
          <div id=\"foo\" style=\"display:none;\">");
            foreach($files as $file) {
              echo '<a href="' .$url . $file . '">' . $file . '</a><br/>';
            }
  print('</div>');
}
?>
<?php include ("javascript/toggle.js");
