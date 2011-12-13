<?php
/*
 *      validator_link.php
 *      
 *      Copyright 2011 David Carpenter <caprenter@gmail.com>
 *      
 *      This file is part of IATI-Data-Spotter.
 *      
 *      IATI-Data-Spotter is free software: you can redistribute it and/or modify
 *      it under the terms of the GNU Affero General Public License as published by
 *      the Free Software Foundation, either version 3 of the License, or
 *      (at your option) any later version.
 *      
 *      IATI-Data-Spotter is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU Affero General Public License for more details.
 *      
 *      You should have received a copy of the GNU Affero General Public License
 *      along with IATI-Data-Spotter.  If not, see <http://www.gnu.org/licenses/>.
 *      
 */
/**
 * Creates a link to the Validator service by Chris Wallace for IATI xml files
 * 
 * The function takers our parameters and formats the URL string so it can be
 * processed by the validator
 * 
 * name: validator_link
 * @package IATI-Data-Spotter
 * 
 * @param string $url The base URL of the file to validate
 * @param string $file The filename of the file to validate. When combined with $url this is the path to file on the web
 * @param string $id An activity id. Optional. If supplied the link returned is to an individual activity
 * 
 * @return string $link A URL to the validator service
 */

http://84.45.72.169:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?type=activitySet&src=http%3A%2F%2Fsiteresources.worldbank.org%2FINTSOPE%2FResources%2F5929468-1305310586289%2FWB_NI.xml
  function validator_link($url,$file,$id = NULL) {
    if ($id !=NULL) {
      $link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?mode=view&type=activity&id=';
      //$link ='http://109.104.101.243:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?corpus=vstore&mode=view&type=activity&id=';
      //$link ='http://84.45.72.169:8080/exist/rest/db/apps/iati-api/xquery/validate.xq
      $link ='http://84.45.72.169:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?corpus=vstore&mode=view&type=activity&id=';
      
      $link .=$id;
      //$link .= '&source=' . urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
      //$link .= '&src=' . urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
    } else {
      //$link ='http://webapps.kitwallace.me/exist/rest/db/apps/iati/xquery/validate.xq?type=activitySet&source=';
      //$link ='http://109.104.101.243:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?type=activitySet&src=';
      $link ='http://84.45.72.169:8080/exist/rest/db/apps/iati-api/xquery/validate.xq?mode=download&type=activitySet&src=';
      $link .= urlencode($url) . urlencode(preg_replace("/ /", "%20", $file));
      //$link .= '&mode=download';
    }
    return $link;
  }
?>
