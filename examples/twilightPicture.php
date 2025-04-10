<?php
if (!extension_loaded('gd')) {
    die("This example requires GD library!");
}

require_once(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use JiriJozif\DateSunInfo\DateSunInfo;

define('WIDTH', 960);
define('Y_ZOOM', 2);
define('FONT_SIZE', 3);

date_default_timezone_set("Europe/London");
$latitude = 51.48;
$longitude = 0.0;
$year = date('Y');

////////////////////////////////////////////////////////////////////////
$days = 365 + date('L', strtotime("{$year}-01-01"));
$img = imageCreateTrueColor(WIDTH, $days * Y_ZOOM);
$color = [
    'pen' => 0x00000,
    'sun' => 0xc8c800,
    'day' => 0xffffff,
    'civil_twilight' => 0xe0ffff,
    'nautical_twilight' => 0xc8f0ff,
    'astronomical_twilight' => 0xb4dcf5,
    'night' => 0x87ceeb,
];

// graph
imageFill($img, 0, 0, $color['night']);
$y = 0;
for ($month = 1; $month <= 12; $month++) {
    $maxDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($day = 1; $day <= $maxDays; $day++) {
        $dsi = DateSunInfo::get(strtotime("{$year}-{$month}-{$day}"), $latitude, $longitude, true);
        drawTwilight($img, $y, $dsi->astronomical_twilight_begin, $dsi->astronomical_twilight_end, $color['astronomical_twilight']);
        drawTwilight($img, $y, $dsi->nautical_twilight_begin, $dsi->nautical_twilight_end, $color['nautical_twilight']);
        drawTwilight($img, $y, $dsi->civil_twilight_begin, $dsi->civil_twilight_end, $color['civil_twilight']);
        drawTwilight($img, $y, $dsi->sunrise, $dsi->sunset, $color['day']);
        drawSun($img, $y, $dsi->transit, $color['sun']); // Solar Noon
        $y += 1;
    }
}

// coordinates
imageSetStyle($img, [$color['pen'], IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT]);
imageString($img, FONT_SIZE, 2, 2, "0h", $color['pen']);
for ($hour = 1; $hour <= 23; $hour += 1) {
    $x = intval(WIDTH * $hour / 24);
    imageLine($img, $x, 0, $x, imagesy($img), IMG_COLOR_STYLED);
    imageString($img, FONT_SIZE, $x + 2, 2, "{$hour}h", $color['pen']);
}
$y = 0;
for ($month = 1; $month <= 12; $month++) {
    imageLine($img, 0, $y, imagesx($img), $y, IMG_COLOR_STYLED);
    imageString($img, FONT_SIZE, 2, $y + 12 * Y_ZOOM, Date("M", strtotime("{$year}-{$month}-01")), $color['pen']);
    $y += Y_ZOOM * cal_days_in_month(CAL_GREGORIAN, $month, $year);

}
$text = iconv("UTF-8", "ISO-8859-2//TRANSLIT", date_default_timezone_get() . "  lat:{$latitude}°  lon:{$longitude}°  year:{$year}");
imageString($img, FONT_SIZE, 2, imagesy($img) - 15, $text, $color['pen']);

Header('Content-type: image/png');
imagePNG($img);
imageDestroy($img);

////////////////////////////////////////////////////////////////////////
function drawSun(GdImage $img, int $y, int $timestamp, $color): void {
    $x = timestamp2x($timestamp);
    $y0 = intval($y * Y_ZOOM);
    $y1 = intval(($y + 1) * Y_ZOOM);
    imageFilledRectangle($img, $x, $y0, $x, $y1, $color);
}

function drawTwilight(GdImage $img, int $y, int|bool $begin, int|bool $end, $color): void {
    if ($begin === true || $end === true) {
        $x0 = 0;
        $x1 = WIDTH;
    }
    elseif ($begin === false || $end === false) {
        return;
    }
    else {
        $x0 = timestamp2x($begin);
        $x1 = timestamp2x($end);
    }
    $y0 = intval($y * Y_ZOOM);
    $y1 = intval(($y + 1) * Y_ZOOM);
    if ($x0 < $x1) {
        imageFilledRectangle($img, $x0, $y0, $x1, $y1, $color);
    }
    else {
        imageFilledRectangle($img, 0, $y0, $x1, $y1, $color);
        imageFilledRectangle($img, $x0, $y0, WIDTH, $y1, $color);
    }
}

function timestamp2x(int $timestamp): int {
    $x = 3600 * intval(date("H", $timestamp)) + 60 * intval(date("i", $timestamp)) + intval(date("s", $timestamp));
    return intval(WIDTH * $x / 86400);
}
