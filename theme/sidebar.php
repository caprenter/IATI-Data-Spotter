<div id="sidebar-left">
  <?php
  $files = array();
  $directory = 'processes';
  $path = htmlentities($_SERVER['REQUEST_URI']);
  //echo $path;
  /* Already defined in..
   * $menu // site_vars.php set in header.php
   * $myinputs['group'] //functions/init.php
   * $avalaible_groups] //functions/init.php
  */
  
  if (in_array($myinputs['group'],array_keys($available_groups))) {
    
    //print("<h3>Select Test</h3>");
    foreach ($menus as $menu) {
      print('<div class="menu_block">');
        $title = $menu;
        switch ($title) {
          case "participating_org":
          $title = "participating org";
          break;
          case "date_time":
          $title = "Date &amp; Time";
          break;
        }
        //$title = ucwords(str_replace("_"," ",$menu)); //format the menu title string, replaces _ with &
        //$title = str_replace(" "," &amp; ",$title);
        $title = ucwords($title);
        print('<h3 class="menu-title">' . $title . '</h3>'); 
        echo '<ul class="menu">';
        //Each menu block is defined in site_vars in the form $title_menu
        $menu_array = ${$menu . '_menu'}; //each menu_array provides ['link'] and ['title'] for our menu items
        //print_r($menu_array);
        foreach ($menu_array  as $item) {
          //Check to see if this is the 'active' link and add css class if it is
          if (strstr($path,$item["link"])) {
            echo '<li class="active">';
          } else {
            echo '<li>';
          }
          echo '<a href="' . $item["link"] . '?group=' . $myinputs['group'] . '">' . $item['title'] . '</a></li>';
        }


        if ($item["link"] == "missing_elements") {
          require_once ("variables/elements_list.php");
          echo '<span class="elements">Looking for:</span><ul class="elements">';
           foreach ($elements as $element) {
             echo "<li>&lt;" . $element .  "&gt;</li>";
            }
          echo '</ul>';
        }
        echo "</ul>";
      echo "</div>";
   } 
  }
  ?>
</div>
