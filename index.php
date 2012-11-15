<!DOCTYPE HTML>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>IATI data Batch Tests</title>
    <link rel="stylesheet" href="theme/css/main.css" />
  </head>
  <body id="home">
    <div id="header">
      <div id="site-notice">This is a test environment. The data may not be current or live - please refer to <a href="http://iatiregistry.org/">http://iatiregistry.org/</a> for live data</div>
      <div id="nav"><a href="index.php">Home</a></div>
    </div>
    <div id="page-wrapper">
    <?php
        require_once("variables/available_groups.php");
       print("<h2>Select Data Group</h2><ul>");
       asort($available_groups);
        foreach ($available_groups as $key => $value) { 
          echo '<li><a href="statistics.php?group=' . $key .'">' . $value . '</a> (' . $key . ')</li>';
        }
        print("</ul>");
    ?>
</body>
</html>
