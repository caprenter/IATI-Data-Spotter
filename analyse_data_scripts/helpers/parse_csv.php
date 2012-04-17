<?php
$row = 1;

$lists = array("The_IATI_Standard_Organisation_Identifier_(Bilateral).csv",
                "The_IATI_Standard_Organisation_Identifier_(INGO).csv",
                "The_IATI_Standard_Organisation_Identifier_(Multilateral).csv"
                );
foreach ($lists as $list) {
  if (($handle = fopen("helpers/code_lists/" . $list, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",","'")) !== FALSE) {
          $num = count($data);
          //echo "<p> $num fields in line $row: <br /></p>\n";
          $row++;
          for ($c=0; $c < $num; $c++) {
              //echo $data[$c] . "<br />\n";
          }
          $codes[$data[0]] = array($data[1],$data[2],$data[3]);
      }
      fclose($handle);
  }
}
//print_r($codes);
?>
