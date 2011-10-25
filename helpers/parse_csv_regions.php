<?php
$row = 1;

$lists = array("Region.csv",
                );
foreach ($lists as $list) {
  if (($handle = fopen("helpers/code_lists/" . $list, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ',','"')) !== FALSE) {
          $num = count($data);
          //echo "<p> $num fields in line $row: <br /></p>\n";
          $row++;
          for ($c=0; $c < $num; $c++) {
              //echo $data[$c] . "<br />\n";
          }
          $codes[$data[0]] = $data[1];
      }
      fclose($handle);
  }
}
//print_r($codes);
?>
