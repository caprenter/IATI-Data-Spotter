<div id="sidebar-left">
<?php
$files = array();
$directory = 'processes';
$path = $_SERVER['REQUEST_URI'];
//echo $path;

if (in_array($myinputs['group'],array_keys($available_groups))) {
  $files = get_list_of_files ($directory); //included in /functions/init.php
  sort($files);
  print("<h3>Select Test</h3>");
   echo "<ul>";
  foreach ($files as $file) {
    $filename = preg_replace('/.php/', '', $file);
    $readable_name = preg_replace('/_/', ' ', $filename);
    if (strstr($path,$pages[$filename])) {
       echo '<li class="active">';
    } else {
      echo '<li>';
    }
    echo '<a href="' . $pages[$filename] . '?group=' . $myinputs['group'] . '">' . ucwords($readable_name) . '</a></li>';
    if ($filename == "missing_elements") {
      require_once ("variables/elements_list.php");
      echo '<span class="elements">Looking for:</span><ul class="elements">';
       foreach ($elements as $element) {
         echo "<li>&lt;" . $element .  "&gt;</li>";
        }
      echo '</ul>';
    }
      
    
  }
       
            echo "</ul>";
 }  
?>
</div>
