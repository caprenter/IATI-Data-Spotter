<html>
  <head>
    <title>IATI data Batch Tests</title>
    <link rel="stylesheet" href="theme/css/main.css" />
  </head>
  <body id="home">
    <div id="header">
      <div id="nav"><a href="index.php">Home</a></div>
    </div>
    <div id="page-wrapper">
    <?php
        require_once("variables/available_groups.php");
       print("<h2>Select Data Group</h2><ul></ul>");
       asort($available_groups);
        foreach ($available_groups as $key => $value) { 
          echo '<li><a href="statistics.php?group=' . $key .'">' . $value . '</a> (' . $key . ')</li>';
        }
        print("</ul>");
    ?>
</body>
</html>
