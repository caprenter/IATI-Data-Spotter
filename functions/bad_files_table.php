<?php
/*
*   Theme function that prints a table listing files that have errored
*   
*   @see identifier_format.php, activity_list.php
*   @param array $bad_files An array of filenames of files that failed for some reason
*   @param string $url A fixed variable of the URL root that the file can be found on 
*/
function theme_bad_files($bad_files,$url) {
  $rows = "";
  //Print a table of failing files
  if ($bad_files != NULL) {
    foreach ($bad_files as $file) {
      $rows .= '<tr><td><a href="' .$url . urlencode($file) . '">' . $file . '</a></td></tr>';
    }

    print("
        <table id='fail-table' class='sortable'>
          <thead>
            <tr>
              <th><h3>These files could not be parsed:</h3></th>
            </tr>
          </thead>
          <tbody>
            $rows
          </tbody>
        </table>"
       );
  }
}
?>
