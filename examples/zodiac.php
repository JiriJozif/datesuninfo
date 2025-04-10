<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Zodiac sign</title>
</head>
<body>
<?php
require_once(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use JiriJozif\DateSunInfo\DateSunInfo;

date_default_timezone_set("UTC");

echo "The Sun is currently in the zodiac sign of ", getZodiacSign(time());

////////////////////////////////////////////////////////////////////////
function getZodiacSign(int $timestamp): string {
    $zodiac = [
         0 => 'Aries ♈︎',
         1 => 'Taurus ♉︎',
         2 => 'Gemini ♊︎',
         3 => 'Cancer ♋︎',
         4 => 'Leo ♌︎',
         5 => 'Virgo ♍︎',
         6 => 'Libra ♎︎',
         7 => 'Scorpio ♏︎',
         8 => 'Sagittarius ♐︎',
         9 => 'Capricorn ♑︎',
        10 => 'Aquarius ♒︎',
        11 => 'Pisces ♓︎'
    ];

    $dsi = DateSunInfo::getPosition($timestamp);
    $sign = intval($dsi['Lon'] / 30.0);

    return $zodiac[$sign];
}
?>
</body>
</html>
