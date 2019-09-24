<?php

function get_xml_result($url) {
  // Initialize our cURL handle
  $ch = curl_init();

  // Set the URL
  curl_setopt($ch, CURLOPT_URL, $url);

  // Set the user agent
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

  // We don't want the HTTP header, we just want the XML
  curl_setopt($ch, CURLOPT_HEADER, 0);

  // This makes the curl_exec call return a string
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  // Follow any location headers
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

  // Set some sane timeouts
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt($ch, CURLOPT_TIMEOUT, 120);

  // Attempt to fetch the XML
  $result = curl_exec($ch);

  // Make sure we don't have whitespace at the beginning
  $result = trim($result);

  // Check to see if we were successful
  if (curl_errno($ch) != 0) {
    $result = false;
    // We're not using this, but this is how to get the error message:
    $error_message = curl_error($ch);
  }

  // Close our cURL handle
  curl_close($ch);

  return $result;
}

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

if (!function_exists('json_decode')) {
  function json_decode($content, $assoc=false) {
    require_once 'JSON.php';
    if ($assoc) {
      $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    }
    else {
      $json = new Services_JSON;
    }
    return $json->decode($content);
  }
}

//$request_body = file_get_contents('php://input');

if (PHP_VERSION>='5')
  require_once('domxml-php4-to-php5.php');

$TITLE = $_GET["title"];
$TITLE = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($TITLE));
$TITLE = html_entity_decode($TITLE,null,'UTF-8');
$client_identifier=$_GET["libhash"];
$base_url = 'http://' . $client_identifier . '.xml.search.serialssolutions.com/openurlxml?version=1.0&ctx_ver=Z39.88-2004&ctx_enc=info:ofi/enc:UTF-8&sid=sersol';
$citation_url_elements = Array();
$returnArray=array();

if($TITLE!="")
  $citation_url_elements['title']=$TITLE;

$url_elements = $citation_url_elements;

// Query String Values Assembler
$first_value = false;
$query_string = '';
foreach ($url_elements as $key => $value) {

  // If it's the first value, use a ? to start the query string
  // otherwise, use the &amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;.
  $seperator = $first_value ? "?" : "&";
  $first_value = false;

  // Add this key-value pair to the query string
  // using PHP's urlencode() function
  $query_string .=
    $seperator .
    urlencode($key) . "=" . urlencode($value);
}

// Add the base URL to the query string
$api_url = $base_url . $query_string;

$resp=get_xml_result($api_url);

if ($xmldomdoc = domxml_open_mem($resp)) {} else { echo "Failed";}

$count=0;
$title_container;
$issn_container;
$eissn_container;
$isbn_container;
$eisbn_container;
$tite="";
$issn="";
$eissn="";
$isbn="";
$eisbn="";
$result_container;
$result_container = $xmldomdoc->get_elements_by_tagname('result');
$linkgroup_container;
$citation_containter;

foreach ($result_container as $holding_container)
{
  $linkgroup_container=$holding_container->get_elements_by_tagname('linkGroup');

  if (count($linkgroup_container)==0)
    continue;

  $returnArray[$count]['format']=$holding_container->get_attribute('format');


  $citation_containter = $holding_container->get_elements_by_tagname('citation');

  if (count($citation_containter)>0)
  {
    foreach ($citation_containter as $container) {
      $title_container = array_pop($container->get_elements_by_tagname('source'));
      if (is_callable(array($title_container,'get_content'))) {
        $title= $title_container->get_content();
      }
    }
    foreach ($citation_containter as $container) {
      $issnContainer = array_pop($container->get_elements_by_tagname('issn'));
      if (is_callable(array($issnContainer,'get_content'))) {
        $issn= $issnContainer->get_content();
      }
    }
    foreach ($citation_containter as $container) {
      $eissnContainer = array_pop($container->get_elements_by_tagname('eissn'));
      if (is_callable(array($eissnContainer,'get_content'))) {
        $eissn= $eissnContainer->get_content();
      }
    }
    foreach ($citation_containter as $container) {
      $isbnContainer = array_pop($container->get_elements_by_tagname('isbn'));
      if (is_callable(array($isbnContainer,'get_content'))) {
        $isbn= $isbnContainer->get_content();
      }
    }
    foreach ($citation_containter as $container) {
      $eisbnContainer = array_pop($container->get_elements_by_tagname('eisbn'));
      if (is_callable(array($eisbnContainer,'get_content'))) {
        $eisbn= $eisbnContainer->get_content();
      }
    }

    if(strlen($title)>0)
    {
      $returnArray[$count]['title']=$title;
    }

    if(strlen($issn)>0)
    {
      $returnArray[$count]['pidentifer']=$issn;
    }
    else if(strlen($isbn)>0)
    {
      $returnArray[$count]['pidentifer']=$isbn;
    }
    else
    {
      $returnArray[$count]['pidentifer']='';
    }

    if(strlen($eissn)>0)
    {
      $returnArray[$count]['eidentifer']=$eissn;
    }
    else if(strlen($eisbn)>0)
    {
      $returnArray[$count]['eidentifer']=$eisbn;
    }
    else
    {
      $returnArray[$count]['eidentifer']='';
    }
  }

  $linkgroup_container = $holding_container->get_elements_by_tagname('linkGroup');

  if (count($linkgroup_container)>0)
  {
    $dbNameContainer;
    $dbURLContainer;
    $startDateContainer;
    $endDateContainer;
    $resourceUrl="";
    $startDate="";
    $endDate="";
    $dbName="";
    $holdingCount=0;

    foreach ($linkgroup_container as $container) {
      $dbNameContainer = array_pop($container->get_elements_by_tagname('databaseName'));
      $returnArray[$count]['holdings']['dbname'][$holdingCount]="";
      if (is_callable(array($dbNameContainer,'get_content'))) {
        $dbName = $dbNameContainer->get_content();
        $returnArray[$count]['holdings']['dbname'][$holdingCount]=$dbName;
      }

      $returnArray[$count]['holdings']['url'][$holdingCount]=$resourceUrl;
      $dbURLContainer = $container->get_elements_by_tagname('url');
      $arrlength=count($dbURLContainer);
      for($x=0;$x<$arrlength;$x++)
      {
        $attribute= $dbURLContainer[$x]->get_attribute('type');

        if($attribute=='journal'||$attribute=='book')
        {
          if (is_callable(array($dbURLContainer[$x],'get_content'))) {
            $resourceUrl = $dbURLContainer[$x]->get_content();
            $returnArray[$count]['holdings']['url'][$holdingCount]=$resourceUrl;
          }
        }
      }

      $returnArray[$count]['holdings']['startdate'][$holdingCount]="";
      $startDateContainer = $container->get_elements_by_tagname('startDate');
      $arrlength=count($startDateContainer);
      if($arrlength>0)
      {
        if (is_callable(array($startDateContainer[0],'get_content'))) {
          $startDate = $startDateContainer[0]->get_content();
          $returnArray[$count]['holdings']['startdate'][$holdingCount]=$startDate;
        }

      }

      $returnArray[$count]['holdings']['enddate'][$holdingCount]="";
      $endDateContainer = $container->get_elements_by_tagname('endDate');
      $arrlength=count($endDateContainer);
      if($arrlength>0)
      {
        if (is_callable(array($endDateContainer[0],'get_content'))) {
          $endDate = $endDateContainer[0]->get_content();
          $returnArray[$count]['holdings']['enddate'][$holdingCount]=$endDate;
        }
      }

      $dbName="";
      $resourceUrl="";
      $startDate="";
      $endDate="";
      $holdingCount++;
      $issn="";
      $eissn="";
      $isbn="";
      $eisbn="";
    }
  }
  $count++;
}

if(count($returnArray)>0)
{
  //$returnArray = array_values($returnArray);
  //echo $_REQUEST['callback'] . "(" . json_encode($returnArray) . ")";
  $callback = "";
  if(array_key_exists('callback', $_GET) == TRUE){
    $callback = $_GET['callback'];
    echo $_GET['callback'].'('.json_encode($returnArray).')';
  }
}

?>
