<?php
  require_once("settings.php");
  //Sanitize GET variables
  //If passed we want to serve only files that failed to parse
  if (isset($_GET['fail'])) {
    //this is passed from the main set list page. We need to post it back
    $fail = filter_var($_GET['fail'], FILTER_SANITIZE_NUMBER_INT);
    if (intval($fail) != 1) {
      unset($fail);
    }
  }
  //This is the element want information on
  if (isset($_GET['element'])) {
    //this is passed from the main set list page. We need to post it back
    $element = filter_var($_GET['element'], FILTER_SANITIZE_STRING);
    //Check it is in the list of avaiable elements
    if (!in_array($element,$elements)) {
      unset($element);
    }
  }
  //This is the provider
  if (isset($_GET['provider'])) {
    //this is passed from the main set list page. We need to post it back
    $provider = filter_var($_GET['provider'], FILTER_SANITIZE_STRING);
    //Check it's on the list
    if (!in_array($provider,array_keys($providers))) {
      unset($provider);
    }
  }
  if (!$provider || !$element ) {
     if ($element) {
       header('Location: index.php?element=' . $element);
     } else {
       header('Location: index.php');
     }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>IATI Element Finder - Files</title>
    <link rel="stylesheet" href="javascript/tinytable/style.css" />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    
    <header>
      <?php
        require_once("header.php");
      ?>
    </header>
    <div id="page">
      <aside>
        <div class="sidebar">
        <h3>Element</h3>
        <?php 
          sort($elements);
          foreach ($elements as $element_link) {
             echo '<a href="index.php?element=' . $element_link .'">' . $element_link . '</a><br/>';
          }
        ?>
        </div>
      </aside>
      <div class="main" role="main">
        <?php
        
          echo "<h1>" . $element . "</h1>";
          echo "<h2>" . $providers[$provider] . "</h2>";
          $filename = preg_replace("/\//","_",$element);
          //echo $filename; die;
          $output_file = "data/" . $filename . ".php";

          if ($data = file_get_contents($output_file)) {
            $data = unserialize($data);
            //Also grab some additonal data to help us make links to those files
            if ($file_metadata = file_get_contents("mapped_files_to_urls.php")) {
              $file_metadata = unserialize($file_metadata);
              //print_r($file_metadata);
            }
            //print_r($data);
          }
          
          foreach ($data as $record) {   
            //Find the files data for this provider and this element
            if ($record["provider"] == $provider) {
              if (isset($fail)) {
                $files = $record["data"]["failed_to_parse"];
                echo "Failed to parse: " . count($files) . " file";
                if (count($files) != 1) {
                    echo "s";
                }                
              } else {
                $files = $record["data"]["files_with_element"];
                echo "<p>" . count($files) . " file";
                  if (count($files) != 1) {
                    echo "s";
                  }
                echo " with this element:</p>";
              }
              echo '<ul class="files">';
              foreach($files as $file) {
                //Grab some metadata
                foreach ($file_metadata as $meta) {
                  if ($meta["group"] == $provider) {
                    //echo "provider_match";
                    if ($meta["file"] == $file) {
                      $url = $meta["url"]; 
                       //echo "file_match";
                      $ckan_name = $meta["name"];
                    } //end if meta match
                  }
                } //end foreach meta file
                if (isset($url)) {
                    echo '<li>' . $file . ' [<a href="' . $url . '">xml</a>] [<a href="http://iatiregistry.org/dataset/' . $ckan_name . '">registry</a>] [<a href="http://tools.aidinfolabs.org/showmydata/index.php?url=' . $url . '">preview</a>]</li>';
                    unset($url);
                } else {
                  echo '<li>' . $file . '</li>';
                }
              }
              echo '</ul>';                  
            } 
          }
        ?>
      </div>
      
      <footer>
        <?php
          require_once("footer.php");
        ?>
      </footer>
    </div><!--page-->
    <script type="text/javascript" src="javascript/tinytable/script.js"></script>
    <script type="text/javascript">
    var sorter = new TINY.table.sorter("sorter");
    sorter.head = "head";
    sorter.asc = "asc";
    sorter.desc = "desc";
    sorter.even = "evenrow";
    sorter.odd = "oddrow";
    sorter.evensel = "evenselected";
    sorter.oddsel = "oddselected";
    sorter.paginate = true;
    sorter.currentid = "currentpage";
    sorter.limitid = "pagelimit";
    sorter.init("table");
    </script>
  </body>
</html>
