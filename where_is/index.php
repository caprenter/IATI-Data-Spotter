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
          $supplied_element = filter_var($_GET['element'], FILTER_SANITIZE_STRING);
          if (!in_array($supplied_element,$elements)) { //$elemnts comes from settings.php
            unset($supplied_element);
          }
        }
        
        //Default to activity-date if no valid element supplied
        if (!isset($supplied_element)) {
          $element = "activity-date"; 
        } else {
          $element = $supplied_element;
        }
        
        echo "<h1>" . $element . "</h1>";
        //Filenames are element XML paths replaced with underscores. e.g. budget/value becomes budget_value.php
        $filename = preg_replace("/\//","_",$element);
        //echo $filename; die;
        $output_file = "data/" . $filename . ".php";
        
        //Grab the data for this element
        if ($data = file_get_contents($output_file)) {
            $data = unserialize($data);
            //print_r($data);
        }
        
        //Count providers reporting
        $no_reporting = 0;
        $no_providers = count($data);
        foreach ($data as $record) {
          if (intval($record["data"]["activity_files_with_element"]) > 0) {
            $no_reporting++;
          }
        }
        if ($no_reporting == 0) {
          $no_reporting = "none";
        }
        ?>
        <table id="table" class="sortable">
          <caption>Table showing <?php echo $no_providers; ?> data provider<?php if($no_providers != 1){ echo "s"; } ?> of which <?php echo $no_reporting ;?> report<?php if($no_reporting == 1){ echo "s"; } ?> <strong><?php echo $element; ?></strong>
          </caption>
          <thead>
            <tr>              
              <th scope="col"><h3>Provider</h3></th>
              <th scope="col"><h3>Activity Files with Element</h3></th>
              <th scope="col"><h3>No.Files Published</h3></th>
              <th scope="col"><h3>Activity Files</h3></th>
              <th scope="col"><h3>Org. Files</h3></th>
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
                  if (intval($record["data"]["activity_files_with_element"]) > 0 ) {
                    echo '<td><a href="files.php?element=' . $element .'&amp;provider=' . $record["provider"] .'">' . $record["data"]["activity_files_with_element"] . '</a></td>';
                  } else {
                    echo '<td>' . $record["data"]["activity_files_with_element"] . '</td>';
                  }
                  echo "<td>" . $record["data"]["all_files"] . "</td>";
                  echo "<td>" . $record["data"]["activity_files"] . "</td>";
                  echo "<td>" . $record["data"]["org_files"] . "</td>";
                  if (intval(count($record["data"]["failed_to_parse"])) > 0 ) {
                    echo '<td><a href="files.php?fail=1&amp;element=' . $element .'&amp;provider=' . $record["provider"] .'">' . count($record["data"]["failed_to_parse"]) . '</a></td>';
                  } else {
                    echo '<td>' . count($record["data"]["failed_to_parse"]) . '</td>';
                  }
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
