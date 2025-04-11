<?php
/**
 * PHP library for calculating Sunrise, Sunset and Solar transit and twilight
 * Algorithm source: Jean Meesus: Astronomical Algorithms, Willman-Bell Inc 1998
 *
 * @author  Jiri Jozif <jiri.jozif@gmail.com>
 * @license MIT
 * @version 1.0.3
 *
 */
declare(strict_types = 1);

namespace JiriJozif\DateSunInfo;

class DateSunInfo
{
    const ANGLE_TO_SEC = 3600 / 15; // convert angle in degree to seconds
    // type of calculation
    const RISET = 0b0001;
    const TWILIGHT = 0b0010;
    const ALL = 0b0100;

    // default values
    private static float $altitude = -0.833; // refraction (34') + radius Sun circle (16')
    private static float $altAdd = 0.0;
    private static bool $topographic_azimuth = true; // default topogtaphic north = 0 degree (example timedate.com)
    /**
    * @var array<float, string[]>
    */
    private static array $twilight = [
         -6.0 => ['civil_twilight_begin', 'civil_twilight_end'],
        -12.0 => ['nautical_twilight_begin', 'nautical_twilight_end'],
        -18.0 => ['astronomical_twilight_begin', 'astronomical_twilight_end'],
    ];

    /**
     * Basic function: completely replace PHP function date_sun_info() with accurate output
     * @param int $timestamp Unix timestamp
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param bool $asObject Flag for type of output, 'false' is array, 'true' is object
     * @return array<string, mixed>|object
     */
    static public function get(int $timestamp, float $latitude, float $longitude, bool $asObject = false): array|object
    {
        $output = self::exec(self::RISET + self::TWILIGHT, $timestamp, $latitude, $longitude, $asObject);

        return $asObject ? (object) $output : $output;
    }

    /**
     * Short output: only Sunrise, Sunset and Solar transit
     * @param int $timestamp Unix timestamp
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param bool $asObject Flag for type of output, 'false' is array, 'true' is object
     * @return array<string, mixed>|object
     */
    static public function getRiset(int $timestamp, float $latitude, float $longitude, bool $asObject = false): array|object
    {
        $output = self::exec(self::RISET, $timestamp, $latitude, $longitude, $asObject);

        return $asObject ? (object) $output : $output;
    }

    /**
     * Full output: Sunrise, Sunset, Solar transit, Twilight and Azimuths
     * @param int $timestamp Unix timestamp
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param bool $asObject Flag for type of output, 'false' is array, 'true' is object
     * @return array<string, mixed>|object
     */
    static public function getAll(int $timestamp, float $latitude, float $longitude, bool $asObject = false): array|object
    {
        $output = self::exec(self::RISET + self::TWILIGHT + self::ALL, $timestamp, $latitude, $longitude, $asObject);

        return $asObject ? (object) $output : $output;
    }

    /**
     * Calculate the Sun's position for the given timestamp
     * If latitude and longitude are provided, adds horizontal coordinates
     * @param int $timestamp Unix timestamp
     * @param float|null $latitude Latitude of the observer in degree
     * @param float|null $longitude Longitude of the observer in degree
     * @param bool $asObject Flag for type of output, 'false' is array, 'true' is object
     * @return array<string, float>|object
     */
    static public function getPosition(int $timestamp, ?float $latitude = null, ?float $longitude = null, ?bool $asObject = false): array|object
    {
        $output = self::getSunPosition(self::getJulianDate($timestamp));

        // + horizontal coordinate
        if (!is_null($latitude) && !is_null($longitude)) {
            $output += self::equatorial2horizontalCoordinate($timestamp, $latitude, $longitude, $output['RA'], $output['Dec']);
        }

        return $asObject ? (object) $output : $output;
    }

    /**
     * Set options for calculation
     * @param array<string, mixed> $options Array options
     *
     */
    static public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'height':
                    if (!is_numeric($value)) {
                        throw new \InvalidArgumentException("Option 'height' must be numeric, " . gettype($value) . " given.");
                    }
                    self::$altAdd = -0.032 * sqrt(floatval($value));// a=acos(R/(R+h)), acos(1-x)≈sqrt(2*x), x=h/(R+h)≈h/R => a≈sqrt(2/R)*sqrt(h) *180/pi
                    break;
                case 'altitude':
                    if (!is_numeric($value)) {
                        throw new \InvalidArgumentException("Option 'altitude' must be numeric, " . gettype($value) . " given.");
                    }
                    self::$altitude = floatval($value);
                    break;
                case 'topographic_azimuth':
                    self::$topographic_azimuth = boolval($value);
                    break;
                case 'twilight':
                    if (!is_array($value)) {
                        throw new \InvalidArgumentException("Option 'twilight' must be array, " . gettype($value) . " given.");
                    }
                    foreach ($value as $alt => $name) {
                        if (!is_numeric($alt)) {
                            throw new \InvalidArgumentException("Option 'twilight'->'altitude' must be numeric, " . gettype($alt) . " given.");
                        }
                        if (!is_array($name)) {
                            throw new \InvalidArgumentException("Option 'twilight'->'name' must be array, " . gettype($name) . " given.");
                        }
                        if (!is_string($name[0]) || !is_string($name[1])) {
                            throw new \InvalidArgumentException("Option 'twilight'->'name[0,1]' must be string");
                        }
                        self::$twilight[floatval($alt)] = [strval($name[0]), strval($name[1])];
                    }
                    break;
                default:
                    throw new \RuntimeException("Bad option '{$name}'");
            }
        }
    }

    /** Returns the time in hh:mm:ss format,
     *  or "**:**:**" if the Sun is continuously above the horizon,
     *  or "--:--:--" if the Sun is continuously below the horizon
     * @param int|bool $timestamp Unix timestamp or false or true
     * @return string Formatted time
     *
     */
    static public function hh_mm_ss(int|bool $timestamp):string {
        return match($timestamp) {
            true => '**:**:**',
            false => '--:--:--',
            default => date('H:i:s', $timestamp)
        };
    }

    /** Returns the rounded time (to the nearest minute) in hh:mm format,
     * or "**:**" if the Sun is continuously above the horizon,
     * or "--:--" if the Sun is continuously below the horizon
     * @param int|bool $timestamp Unix timestamp or false or true
     * @return string Formatted time
     *
     */
    static public function hh_mm(int|bool $timestamp):string {
        if ($timestamp === true) {
            return '**:**';
        }
        elseif ($timestamp === false) {
            return '--:--';
        }
        $t = intval(round($timestamp / 60) * 60);
        if (date("j", $timestamp) === date("j", $t)) { // check if timestamp is this same day
            $timestamp = $t;
        }
        return date('H:i', $timestamp);
    }

    /**
     * Perform all requested calculations (rise/set, twilight, transit, azimuths)
     *
     * @param int $compute Type of calculation
     * @param int $timestamp Unix timestamp
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param bool $asObject Flag for type of output, 'false' is array, 'true' is object
     * @return array<string, mixed>
     */
    static private function exec(int $compute, int $timestamp, float $latitude, float $longitude, bool $asObject): array
    {
        $output = [];

        // calculate position for 12 hour local time
        $t12 = strtotime(date('Y-m-d', $timestamp) . ' 12:00:00') ?: throw new \RuntimeException('Failed to parse date');
        /** @var array<string, float> $sunEphem12 */
        $sunEphem12 = self::getPosition($t12, null, null, false);
        // calculate Local Sidereal Time at 12 hour local time
        $LST12 = self::getLST(self::getJulianDate($t12), $longitude);
        // difference between Sun's Right Ascension and Local Sidereal Time
        $dTransit = 3600 * self::fixAngle180($sunEphem12['RA'] - $LST12) / 15.0;
        // timestamp of transit
        $tTransit = intval($t12 + $dTransit);

        if ($compute & self::RISET) {
            $p = self::getRisetPosition('-', self::$altitude + self::$altAdd, $tTransit, $latitude, $longitude, $sunEphem12);
            $output['sunrise'] = $p['timestamp'];
            if ($compute & self::ALL) {
                $output['sunrise_azimuth'] = $p['Az'];
            }
            $p = self::getRisetPosition('+', self::$altitude + self::$altAdd, $tTransit, $latitude, $longitude, $sunEphem12);
            $output['sunset'] = $p['timestamp'];
            if ($compute & self::ALL) {
                $output['sunset_azimuth'] = $p['Az'];
            }
            // at the moment of transit the Local Sidereal Time is equal to the Sun's Right Ascension
            $output['transit'] = $tTransit;
            if ($compute & self::ALL) {
                /** @var array<string, float> $sunEphemTransit */
                $sunEphemTransit = self::getPosition($output['transit'], null, null, false);
                $output['transit_height'] = 90.0 - abs($sunEphemTransit['Dec'] - $latitude);
                $output['lowest_height'] = abs($sunEphemTransit['Dec'] + $latitude) - 90.0;
            }
        }

        if ($compute & self::TWILIGHT) {
            foreach (self::$twilight as $alt => $names) {
                $p = self::getRisetPosition('-', $alt + self::$altAdd, $tTransit, $latitude, $longitude, $sunEphem12);
                $output[$names[0]] = $p['timestamp'];
                $p = self::getRisetPosition('+', $alt + self::$altAdd, $tTransit, $latitude, $longitude, $sunEphem12);
                $output[$names[1]] = $p['timestamp'];
            }
        }

        return $output;
    }

    /**
     * Calculate Sun position
     *
     * @param float $jd Julian day
     * @return array<string, float>
     */
    static private function getSunPosition(float $jd): array
    {
        // Jean Meeus: Astronomical Algorithms, Solar Coordinates
        $T = ($jd - 2451545.0) / 36525;
        // mean anomaly
        $M = self::fixAngle360(357.52911 + 35999.05029 * $T);
        // mean longitude
        $L0 = self::fixAngle360(280.46646 + 36000.76983 * $T);
        // excentricity
        $e = 0.016708634 - 0.000042037 * $T;

        // equation of the center
        $C = (1.914602 - 0.004817 * $T - 0.000014 * $T * $T) * self::dsin($M)
        + (0.019993 - 0.000101 * $T) * self::dsin(2 * $M)
        + 0.000289 * self::dsin(3 * $M);

        $L = self::fixAngle360($L0 + $C);
        // distance in AU
        $R = (1.000001018 * (1 - $e * $e)) / (1 + $e * self::dcos($M));

        // obliquity of ecliptic
        $epsilon = 23.439292 - 0.000013 * $T;

        $X = self::dcos($L);
        $Y = self::dcos($epsilon) * self::dsin($L);
        $Z = self::dsin($epsilon) * self::dsin($L);

        $RA = self::fixAngle360(self::datan2($Y, $X));
        $Dec = self::dasin($Z);

        return [
            "RA" => $RA,
            "Dec" => $Dec,
            "Lon" => $L,
            "R" => $R
        ];
    }

    /**
     * Calculate Sun position for sunrise or sunset
     *
     * @param string $oper Type operation, '-' is rise and '+' is set
     * @param float $alt Altitude
     * @param int $transit Timestamp of Sun transit
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param array<string, float> $sunEphem
     * @return array<string, mixed>
     */
    static private function getRisetPosition(string $oper, float $alt, int $transit, float $latitude, float $longitude, array $sunEphem): array {
        $i = 0; // iteration counter
        do {
            $cosLHA = (self::dsin($alt) - self::dsin($latitude) * self::dsin($sunEphem['Dec'])) / (self::dcos($latitude) * self::dcos($sunEphem['Dec']));
            if ($cosLHA < -1) {
                return ['timestamp' => true, 'Az' => true];
            }
            elseif ($cosLHA > 1) {
                return ['timestamp' => false, 'Az' => false];
            }
            $LHA = intval(self::dacos($cosLHA) * self::ANGLE_TO_SEC);
            $t = intval($oper === '-' ? $transit - $LHA : $transit + $LHA);
            /** @var array<string, float> $sunEphem */
            $sunEphem = self::getPosition($t, $latitude, $longitude, false);
        } while (abs($sunEphem['h'] - $alt) > 0.05 && ++$i < 3);

        if (date('j', $transit) !== date('j', $t)) {// check if timestamp is this same day
            return ['timestamp' => true, 'Az' => true];
        }

        return ['timestamp' => $t, 'Az' => $sunEphem['Az']];
    }

    /**
     * Calculate horizont coordinate from equatorial coordinate
     *
     * @param int $timestamp Timestamp of Sun
     * @param float $latitude Latitude of the observer in degree
     * @param float $longitude Longitude of the observer in degree
     * @param float $RA Right Ascension of Sun
     * @param float $Dec Declination of Sun
     * @return array{
     *      h: float,
     *      Az: float
     * }
     */
    static private function equatorial2horizontalCoordinate(int $timestamp, float $latitude, float $longitude, float $RA, float $Dec): array
    {
        $LST = self::getLST(self::getJulianDate($timestamp), $longitude);
        $LHA = self::fixAngle360($LST - $RA);
        $h = self::dasin(self::dsin($latitude) * self::dsin($Dec) + self::dcos($latitude) * self::dcos($Dec) * self::dcos($LHA));

        if (self::dcos($h) < 1e-9) {
            $sinAz = 0.0;
            $cosAz = 0.0;
        }
        else {
            $sinAz = self::dcos($Dec) * self::dsin($LHA) / self::dcos($h);
            $cosAz = (self::dsin($latitude) * self::dcos($Dec) * self::dcos($LHA) - self::dcos($latitude) * self::dsin($Dec)) / self::dcos($h);
        }
        $Az = self::fixAngle360(self::datan2($sinAz, $cosAz));

        return [
            'h' => $h,
            'Az' => self::convertAzimuth($Az - 180.0)
        ];
    }

    /**
     * Convert azimuth angle (topographic or astronomical)
     *
     * @param float $x angle
     * @return float converted angle
     */
    static private function convertAzimuth(float $x): float
    {
        return self::fixAngle360(self::$topographic_azimuth ? $x : $x + 180.0);
    }

    /**
     * Calculate Julian date from timestamp
     *
     * @param int $t timestamp
     * @return float Julian date
     */
    static private function getJulianDate(int $t):float
    {
        $jd = gregoriantojd(intval(gmdate("n", $t)), intval(gmdate("j", $t)), intval(gmdate("Y", $t))) - 0.5;
        $jd += floatVal(gmdate("H", $t)) / 24.0 + floatVal(gmdate("i", $t)) / 1440.0 + floatVal(gmdate("s", $t)) / 86400.0;

        return($jd);
    }

    /**
     * Calculate Local Sidereal Time (LST) in degree
     *
     * @param float $jd Julian date
     * @return float LST
     */
    static private function getLST(float $jd, float $longitude): float
    {
        $mjd = $jd - 2451545.0;
        $lst = 280.46061837 + 360.98564736629 * $mjd;

        return fmod($lst + $longitude, 360);
    }
    /**
     * Calculates the sine of the angle specified in degrees
     *
     * @param float $x angle
     * @return float sine
     */
    static private function dsin(float $x): float
    {
        return sin(deg2rad($x));
    }

    /**
     * Calculates the cosine of the angle specified in degrees
     *
     * @param float $x angle
     * @return float cosine
     */
    static private function dcos(float $x): float
    {
        return cos(deg2rad($x));
    }

    /**
     * Calculates the inverse cosine
     *
     * @param float $x number
     * @return float angle specified in degrees
     */
    static private function dacos(float $x): float
    {
        return rad2deg(acos($x));
    }

    /**
     * Calculates the inverse sine
     *
     * @param float $x number
     * @return float angle specified in degrees
     */
    static private function dasin(float $x): float
    {
        return rad2deg(asin($x));
    }

    /**
     * Calculates the inverse tangent
     *
     * @param float $x number
     * @param float $y number
     * @return float angle specified in degrees
     */
    static private function datan2(float $x, float $y)
    {
        return rad2deg(atan2($x, $y));
    }
    /**
     * Converts the angle to a range of 0 to +360 degrees
     *
     * @param float $x angle
     * @return float angle
     */
    static private function fixAngle360(float $x): float
    {
        $x360 = fmod($x, 360.0);
        if ($x360 < 0) {
            $x360 += 360.0;
        }

        return $x360;
    }
    /**
     * Converts the angle to a range of -180 to +180 degrees
     *
     * @param float $x angle
     * @return float angle
     */
    static private function fixAngle180(float $x): float
    {
        $x180 = fmod($x, 360.0);
        if ($x180 > 180.0) {
            $x180 -= 360.0;
        } elseif ($x180 <= -180.0) {
            $x180 += 360.0;
        }
        return $x180;
    }

}
