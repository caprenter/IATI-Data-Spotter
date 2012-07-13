<!DOCTYPE html>
<?php
  require_once("settings.php");
?>
<html>
  <head>
    <meta charset="utf-8">
    <title>IATI Element Finder</title>
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
          foreach ($elements as $element) {
             echo '<a href="index.php?element=' . $element .'">' . $element . '</a><br/>';
          }
        ?>
        </div>
      </aside>
      <div class="main" role="main">
        <?php
        
        //Validate the element
        if (isset($_GET['element'])) {
          //this is passed from the main set list page. We need to post it back
          $element = filter_var($_GET['element'], FILTER_SANITIZE_STRING);
          if (!in_array($element,$elements)) {
            unset($element);
          }
        }
        
        if (!isset($element)) {
          $element = "activity-date"; 
        }
        
        echo "<h1>" . $element . "</h1>";
          $filename = preg_replace("/\//","_",$element);
          //echo $filename; die;
          $output_file = "data/" . $filename . ".php";

            if ($data = file_get_contents($output_file)) {;
              $data = unserialize($data);
              //print_r($data);
            }
        ?>
        <table id="table" class="sortable">
          <caption>Table showing which providers report on: <?php echo $element; ?>
          </caption>
          <thead>
            <tr>
              <th scope="col"><h3>Provider</h3></th>
              <th scope="col"><h3>No.Files Published</h3></th>
              <th scope="col"><h3>Org. Files</h3></th>
              <th scope="col"><h3>Activity Files</h3></th>
              <th scope="col"><h3>Activity Files with Element</h3></th>
              <th scope="col"><h3>Failed to Parse</h3></th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i=0;
              foreach ($data as $record) {
                $i++;
                if ($i%2) {
                    $class="even";
                  } else {
                    $class="odd";
                  }
                  
                  //Get a human readable provider name - use the lookup in settings.php
                  if (isset($providers[$record["provider"]])) {
                    $provider_name = $providers[$record["provider"]];
                  } else {
                      $provider_name = $record["provider"];
                  }
                
                //Print the rows of the table
                echo '<tr class="' . $class . '">';
                  echo "<td>" . $provider_name . "</td>";
                  echo "<td>" . $record["data"]["all_files"] . "</td>";
                  echo "<td>" . $record["data"]["org_files"] . "</td>";
                  echo "<td>" . $record["data"]["activity_files"] . "</td>";
                  echo "<td>" . $record["data"]["activity_files_with_element"] . "</td>";
                  echo "<td>" . $record["data"]["failed_to_parse"] . "</td>";
                echo "</tr>";
              }
            ?>
          </tbody>
        </table>
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
