<?php
  function validator_link($url,$file,$id = NULL) {
    if ($id !=NULL) {
      $link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?mode=view&type=activity&id=';
      //$link ='http://109.104.101.243:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?corpus=vstore&mode=view&type=activity&id=';
      $link .=$id;
      $link .= '&source=' . urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
    } else {
      $link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?type=activitySet&source=';
      //$link ='http://109.104.101.243:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?type=activitySet&src=';
      $link .= urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
      $link .= '&mode=download';
    }
    return $link;
  }
?>
