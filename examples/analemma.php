<?php
if (!extension_loaded('gd')) {
    die("This example requires GD library!");
}

require_once(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use JiriJozif\DateSunInfo\DateSunInfo;

define('WIDTH', 600); // 10 degree
define('HEIGHT', 900); // 60 degree
define('FONT_SIZE', 5);

date_default_timezone_set("UTC");
$latitude = 51.48;
$longitude = 0.0;
$year = date('Y');

////////////////////////////////////////////////////////////////////////
$img = imageCreateTrueColor(WIDTH, HEIGHT);
$color = [
    'sky' => 0x707070,
    'sun' => 0xcfcf00,
    'sun2' => 0xffffff
];

// graph
imageFill($img, 0, 0, $color['sky']);
for ($month = 1; $month <= 12; $month++) {
    $maxDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($day = 1; $day <= $maxDays; $day++) {
        $timestamp = strtotime("{$year}-{$month}-{$day} 12:00:00");
        $dsi = DateSunInfo::getPosition($timestamp, $latitude, $longitude, true);
        drawMark($img, $dsi->Az, $dsi->h, $color['sun'], $day === 1, sprintf("01.%02d.", $month), $color['sun2']);
    }
}

Header('Content-type: image/png');
imagePNG($img);
imageDestroy($img);

////////////////////////////////////////////////////////////////////////
function drawMark(GdImage $img, float $azimuth, float $height, $color, $isFirst, $date, $color2): void {
    $x = intval(($azimuth - 175.2) / 10.0 * WIDTH);
    $y = HEIGHT - intval(($height - 10.0) / 60.0 * HEIGHT);
    if ($isFirst) {
        imageFilledEllipse($img, $x, $y, 5, 5, $color2);
        imageString($img, FONT_SIZE, $x, $y - 20, $date, $color2);
    }
    else {
        imageFilledEllipse($img, $x, $y, 5, 5, $color);
    }
}
