<?php
      //bring in the $available_groups array
      require_once("variables/available_groups.php");
      require_once("variables/transaction_types.php");
      require_once("functions/get_list_of_files.php");
      
      //Filter GET vars
      $args = array(
        'group'   => FILTER_SANITIZE_ENCODED,
        'transaction'   => FILTER_SANITIZE_ENCODED,
        'org' => FILTER_SANITIZE_ENCODED,
        'showall' => FILTER_SANITIZE_ENCODED
      );
      $myinputs = filter_input_array(INPUT_GET, $args);
?>
