<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Sun Today: Complete Solar Timeline</title>
<style>
html {
    background-color: white;
}
h1 {
    text-align: center;
}
table {
    margin: 0 auto;
    border-collapse: collapse;
}
table caption {
    font-size: 1.67rem;
}
table td {
    border: solid 1px black;
    padding: 0.1rem 0.5rem;
}
table td:first-child {
    text-align: right;
}
table td.info {
    border: none;
    text-align: center;
    font-size: 0.8rem;
}
</style>
</head>
<body>
<h1>Sun Today: Complete Solar Timeline</h1>
<table>
<?php
require_once(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use JiriJozif\DateSunInfo\DateSunInfo;

date_default_timezone_set("Europe/London");
$latitude = 51.48;
$longitude = 0.0;
$timestamp = time();
echo "<caption>", date('D j.F Y', $timestamp), "</caption>\n";
echo "<tr><td colspan='2' class='info'>Timezone: ", date_default_timezone_get(), "</td></tr>\n";
echo "<tr><td colspan='2' class='info'>Latitude: {$latitude}°, Longitude: {$longitude}°</td></tr>\n";
$dsi = DateSunInfo::getAll($timestamp, $latitude, $longitude, true);
echo "<tr><td>Astronomical twilight begin</td><td>", DateSunInfo::hh_mm($dsi->astronomical_twilight_begin), "</td></tr>\n";
echo "<tr><td>Nautical twilight begin</td><td>", DateSunInfo::hh_mm($dsi->nautical_twilight_begin), "</td></tr>\n";
echo "<tr><td>Civil twilight begin</td><td>", DateSunInfo::hh_mm($dsi->civil_twilight_begin), "</td></tr>\n";
echo "<tr><td>Sunrise (Azimuth)</td><td>", DateSunInfo::hh_mm($dsi->sunrise), getAz($dsi->sunrise_azimuth), "</td></tr>\n" ;
echo "<tr><td><b>Sun's transit</b> (Height)</td><td><b>", DateSunInfo::hh_mm_ss($dsi->transit), "</b> (", number_format($dsi->transit_height, 1), "°)</td></tr>\n";
echo "<tr><td>Sunset (Azimuth)</td><td>", DateSunInfo::hh_mm($dsi->sunset), getAz($dsi->sunset_azimuth), "</td></tr>\n" ;
echo "<tr><td>Civil twilight end</td><td>", DateSunInfo::hh_mm($dsi->civil_twilight_end), "</td></tr>\n";
echo "<tr><td>Nautical twilight end</td><td>", DateSunInfo::hh_mm($dsi->nautical_twilight_end), "</td></tr>\n";
echo "<tr><td>Astronomical twilight end</td><td>", DateSunInfo::hh_mm($dsi->astronomical_twilight_end), "</td></tr>\n";

////////////////////////////////////////////////////////////////////////
function getAz(float|bool $az): string {
    if ($az === false) {
        return '';
    }
    elseif ($az === true) {
        return '';
    }
    else {
        return " (" .  number_format($az) . "°)";
    }
}

?>
<tr><td colspan='2' class='info'>'**:**' = Sun continuously above horizon or twilight limit</td></tr>
<tr><td colspan='2' class='info'>'--:--' = Sun continuously below horizon or twilight limit</td></tr>
</table>
</body>
</html>
