<?php
// Create connectio
$login = "login {\"protocol\":1,\"client\":\"test\",\"clientver\":0.2}".chr(4); 

$sock = socket_create(AF_INET, SOCK_STREAM, 0);
socket_connect($sock, "api.vndb.org", 19534);
socket_send($sock, $login, strlen($login)+1, 0);
socket_recv($sock, $rsp, 10000, 0);

header( "Content-type: text/xml");
 
 echo "<?xml version='1.0' encoding='UTF-16'?>
 <rss version='2.0'>
 <channel>
 <title>VNDB Releases | RSS</title>
 <link>https://vndb.org/r?fil=released-1;o=d;s=released/</link>
 <description>Homemade RSS</description>
 <language>en-us</language>";

$pages = 2;

for ($i = 1; $i <= $pages; $i++) {
   $date=date_create();
   date_add($date,date_interval_create_from_date_string("2 month"));
   $date=date_format($date,"Y-m-d");
   $get =  'get release basic,details,vn (released>="2020-07-07" and released<="'.date("Y-m-d").'" and released!="tba" and languages="en" and platforms="win" and type="complete") {"sort":"released","results":10,"reverse":true,"page":'.$i.'}'.chr(4);
   socket_send($sock, $get, strlen($get)+1, 0);
   socket_recv($sock, $rsp, 10000, 0);

   $rsp = trim(explode(' ', $rsp, 2)[1], chr(4));
   $json_map = json_decode($rsp, true);

   foreach($json_map["items"] as $item) {
      $title=$item["title"];
      $link="https://vndb.org/v".$item["vn"][0]["id"];
      $description=$link;
   
      echo "<item>
      <title>".str_replace("&", "and", $title)."</title>
      <link>$link</link>
      <description>$description</description>
      <pubDate>".$item["released"]."</pubDate>
      </item>";
   }
}
 echo "</channel></rss>";
 
?>