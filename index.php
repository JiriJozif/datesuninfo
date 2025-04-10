<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>DateSunInfo</title>
</head>
<body>
<h1>Compare the results between the PHP function date_sun_info and the DateSunInfo library</h1>
<?php
require_once(dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use JiriJozif\DateSunInfo\DateSunInfo;

date_default_timezone_set("UTC");
$latitude = 51.48;
$longitude = 0.0;
$timestamp = time();

echo "<h3>PHP function date_sun_info:</h3><pre>";
$sun_info = date_sun_info($timestamp, $latitude, $longitude);
foreach ($sun_info as $key => $val) {
    echo "{$key}: " . date("H:i:s", $val) . "\n";
}
echo "</pre>";

echo "<h3>PHP library DateSunInfo:</h3><pre>";
$dsi = DateSunInfo::get($timestamp, $latitude, $longitude);
foreach ($dsi as $key => $val) {
    echo "{$key}: " . date("H:i:s", $val) . "\n";
}
echo "</pre>";
?>
<h3>More examples:</h3>
<ul>
<li><a href='examples/today.php'>Sun Today: Complete Solar Timeline</a></li>
<li><a href='examples/table.php'>Sunrise, Sun’s Transit and Sunset Over the Year</a></li>
<li><a href='examples/zodiac.php'>Zodiac sign</a></li>
<li><a href='examples/twilightPicture.php'>Diagram Showing Civil, Nautical and Astronomical Twilight</a> (This example requires GD library)</li>
<li><a href='examples/analemma.php'>Diagram showing the position of the Sun in the sky at 12:00</a> (This example requires GD library)</li>
</ul>
</body>
</html>

