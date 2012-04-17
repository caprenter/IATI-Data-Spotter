<?php
/* Uses xpath to get directly to the elements in the XML with the required attributes
 * We have a function that places each attribute found into an array.
 * We then run an array_count_values on that array to tell us how many of each we have found.
 * Then we output it to a file
*/

//libxml_use_internal_errors ( true );
include ('functions/xml_child_exists.php');


$dir = '../data/dfid/'; //needs trailing slash
//$dir = $_SERVER['argv'][1] ."/";
//$url = 'http://ec.europa.eu/europeaid/files/iati/'; //EU
//$url = 'http://projects.dfid.gov.uk/iati/NonIATI/';

 $xsd_url = "http://iatistandard.org/downloads/iati-activities-schema.xsd";



  $xml = simplexml_load_file ($xsd_url);
  $namespaces = $xml->getNameSpaces(true);

  $names = $xml->xpath('//xsd:element');
  //$names = $xml->children();
  print_r($names);
  //$names = $names->xpath("child::*");
  foreach($names as $name) {
    //echo (string)$name[0];
    echo (string) $name[0]->attributes()->name . PHP_EOL;
    
  }
  die;
?>
<?php
function XMLToArrayFlat($xml, &$return, $path='', $root=false)
{
    $children = array();
    if ($xml instanceof SimpleXMLElement) {
        $children = $xml->children();
        if ($root){ // we're at root
            $path .= '/'.$xml->getName();
        }
    }
    if ( count($children) == 0 ){
        $return[$path] = (string)$xml;
        return;
    }
    $seen=array();
    foreach ($children as $child => $value) {
        $childname = ($child instanceof SimpleXMLElement)?$child->getName():$child;
        if ( !isset($seen[$childname])){
            $seen[$childname]=0;
        }
        $seen[$childname]++;
        XMLToArrayFlat($value, $return, $path.'/'.$child.'['.$seen[$childname].']');
    }
}
?>

Use like this:

<?php
$xml = simplexml_load_file("../data/dfid/ZW");
$xmlarray = array(); // this will hold the flattened data
XMLToArrayFlat($xml, $xmlarray, '', true);
print_r($xmlarray);
?> 
