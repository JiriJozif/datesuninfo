<?php
namespace JiriJozif\DateSunInfo\Tests;

use PHPUnit\Framework\TestCase;
use JiriJozif\DateSunInfo\DateSunInfo;

class DateSunInfoTest extends TestCase
{
    const EPS_MINUTE = 0.0167; // error in angular minute
    const EPS_AU = 0.001; // error in distance in AU

    public function testGreenwichCalculate()
    {
        date_default_timezone_set("UTC");
        $dsi = DateSunInfo::getAll(strtotime('2025-03-20'), 51.48, 0.0);

        $this->assertEquals(1742450537, $dsi["sunrise"]);
        $this->assertEqualsWithDelta(89.011, $dsi["sunrise_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1742494412, $dsi["sunset"]);
        $this->assertEqualsWithDelta(271.310, $dsi["sunset_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1742472444, $dsi["transit"]);
        $this->assertEqualsWithDelta(38.576, $dsi["transit_height"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(-38.464, $dsi["lowest_height"], self::EPS_MINUTE);
        $this->assertEquals(1742448542, $dsi["civil_twilight_begin"]);
        $this->assertEquals(1742496413, $dsi["civil_twilight_end"]);
        $this->assertEquals(1742446185, $dsi["nautical_twilight_begin"]);
        $this->assertEquals(1742498780, $dsi["nautical_twilight_end"]);
        $this->assertEquals(1742443731, $dsi["astronomical_twilight_begin"]);
        $this->assertEquals(1742501248, $dsi["astronomical_twilight_end"]);
    }

    public function testNordkappCalculate()
    {
        date_default_timezone_set("Europe/Oslo");
        $dsi = DateSunInfo::getAll(strtotime('2025-06-21'), 71.17, 25.78);

        $this->assertEquals(true, $dsi["sunrise"]);
        $this->assertEquals(true, $dsi["sunrise_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(true, $dsi["sunset"]);
        $this->assertEquals(true, $dsi["sunset_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1750501125, $dsi["transit"]);
        $this->assertEqualsWithDelta(42.269, $dsi["transit_height"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(4.609, $dsi["lowest_height"], self::EPS_MINUTE);
        $this->assertEquals(true, $dsi["civil_twilight_begin"]);
        $this->assertEquals(true, $dsi["civil_twilight_end"]);
        $this->assertEquals(true, $dsi["nautical_twilight_begin"]);
        $this->assertEquals(true, $dsi["nautical_twilight_end"]);
        $this->assertEquals(true, $dsi["astronomical_twilight_begin"]);
        $this->assertEquals(true, $dsi["astronomical_twilight_end"]);
    }

    public function testSydneyCalculate()
    {
        date_default_timezone_set("Australia/Sydney");
        $dsi = DateSunInfo::getAll(strtotime('2025-12-21'), -33.87, 151.21);

        $this->assertEquals(1766256053, $dsi["sunrise"]);
        $this->assertEqualsWithDelta(119.247, $dsi["sunrise_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1766307923, $dsi["sunset"]);
        $this->assertEqualsWithDelta(240.759, $dsi["sunset_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1766281988, $dsi["transit"]);
        $this->assertEqualsWithDelta(79.568, $dsi["transit_height"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(-32.692, $dsi["lowest_height"], self::EPS_MINUTE);
        $this->assertEquals(1766254304, $dsi["civil_twilight_begin"]);
        $this->assertEquals(1766309672, $dsi["civil_twilight_end"]);
        $this->assertEquals(1766252150, $dsi["nautical_twilight_begin"]);
        $this->assertEquals(1766311826, $dsi["nautical_twilight_end"]);
        $this->assertEquals(1766249791, $dsi["astronomical_twilight_begin"]);
        $this->assertEquals(1766314185, $dsi["astronomical_twilight_end"]);
    }

    public function testGreenwichOptionsCalculate()
    {
        date_default_timezone_set("UTC");
        DateSunInfo::setOptions([
            'height' => 500,
            'twilight' => [
                -15 => ['amateur_astronomical_twilight_begin', 'amateur_astronomical_twilight_end'] //https://www.stjarnhimlen.se/comp/riset.html
            ]
        ]);
        $dsi = DateSunInfo::getAll(strtotime('2025-03-20'), 51.48, 0.0);
        $this->assertEquals(1742450261, $dsi["sunrise"]);
        $this->assertEqualsWithDelta(88.111, $dsi["sunrise_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1742494688, $dsi["sunset"]);
        $this->assertEqualsWithDelta(272.212, $dsi["sunset_azimuth"], self::EPS_MINUTE);
        $this->assertEquals(1742472444, $dsi["transit"]);
        $this->assertEqualsWithDelta(38.576, $dsi["transit_height"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(-38.464, $dsi["lowest_height"], self::EPS_MINUTE);
        $this->assertEquals(1742448264, $dsi["civil_twilight_begin"]);
        $this->assertEquals(1742496692, $dsi["civil_twilight_end"]);
        $this->assertEquals(1742445898, $dsi["nautical_twilight_begin"]);
        $this->assertEquals(1742499068, $dsi["nautical_twilight_end"]);
        $this->assertEquals(1742443429, $dsi["astronomical_twilight_begin"]);
        $this->assertEquals(1742501553, $dsi["astronomical_twilight_end"]);
        $this->assertEquals(1742444681, $dsi["amateur_astronomical_twilight_begin"]);
        $this->assertEquals(1742500292, $dsi["amateur_astronomical_twilight_end"]);
    }

    public function testSunParisPositionCalculate()
    {
        date_default_timezone_set("UTC");
        $dsi = DateSunInfo::getPosition(strtotime('2022-10-25 11:00:00'), 48.86, 2.35); // observer in Paris

        // https://eclipse.gsfc.nasa.gov/SEplot/SEplot2001/SE2022Oct25P.GIF
        $this->assertEqualsWithDelta(209.835, $dsi["RA"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(-12.171, $dsi["Dec"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(212.009, $dsi["Lon"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(0.994, $dsi["R"], self::EPS_AU);
        $this->assertEqualsWithDelta(28.489, $dsi["h"], self::EPS_MINUTE);
        $this->assertEqualsWithDelta(170.352, $dsi["Az"], self::EPS_MINUTE);
    }
}
