<?php

include_once('simple_html_dom.php');

//$target_url = "http://www.goggle.com/";
//$target_url = "http://systemone/";
$target_url = "http://tinyhacker.com/";
getlinks($target_url);

$levels = 2;
$currentLevel = 0;

function getlinks($target_url)
{

  $html = new simple_html_dom();
  $html->load_file($target_url);

  global $currentLevel;
  $currentLevel++;
  if($currentLevel > 1) return;

  foreach($html->find('a') as $link){

    if(strpos($link->href,'http') !== false)
    {
      $linkAddress = $link->href;
    }
    else
    {
      $linkAddress = $target_url.$link->href;
    }
    echo "<a href='".$linkAddress."' target='_blank'>".$linkAddress."</a><br />";
    //getlinks($linkAddress);
  }
  return;
}

// foreach($html->find('img') as $link){
// echo "<img src='".$link->src."'><br />";
// }
?>