<?php
$xml = simplexml_load_file("sample.xml");

/*
echo $xml->getName() . " ID: " . $xml->getName() . "<br />";

foreach($xml->children() as $child)
  {
  echo $child->getName() . ": " . $child . "<br />";
  }

*/

echo "<pre>";
print_r($xml->children());
echo "</pre>";
?>


<h1>Sensor ID: <?php echo ($xml->children()->id); ?></h1>
<h2>Tag ID: <?php echo ($xml->children()->observation->tag->id); ?></h2>
<p>DateTime: <?php echo ($xml->children()->observation->datetime); ?></p>
<p>Command: <?php echo ($xml->children()->observation->command); ?></p>