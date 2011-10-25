<?php
// Turn off all error reporting
error_reporting(0);
ini_set("memory_limit","64M");
include('functions/init.php');
include ('variables/site_vars.php');

//Breadcrumb
$directory = 'processes';
$path = $_SERVER['REQUEST_URI'];
$files = get_list_of_files ($directory); //included in /functions/init.php
sort($files);
foreach ($files as $file) {
    $filename = preg_replace('/.php/', '', $file);
    $readable_name = preg_replace('/_/', ' ', $filename);
    if (strstr($path,$pages[$filename])) { //$pages is set in variables/site_vars.php
      $breadcrumb = " - " . ucwords($readable_name);
    }
}
?>
<html>
  <head>
    <title><?php echo $title ?> <?php echo $breadcrumb ?></title>
    <link rel="stylesheet" href="theme/css/main.css" />
    <link rel="stylesheet" href="javascript/tinytable/style.css" />
  </head>

<body>
  <div id="header">
    <div id="nav"><a href="index.php">Home</a></div>
  </div>
  <div id="page-wrapper">
  <?php
    print ('<h4>' . $available_groups[$myinputs['group']] .  $breadcrumb . '</h4>');
    include('theme/sidebar.php');
  ?>
</body>
