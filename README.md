![DateSunInfo](https://raw.githubusercontent.com/JiriJozif/datesuninfo/main/sun.png)

# PHP library for calculating Sunrise, Sunset and Sun transit

A more accurate replacement for the PHP function `date_sun_info()`, which has been returning inaccurate sunrise and sunset times from version 8.0 up to the present (April 2025). 
In addition, the library provides extra values and functions.

## Installation

This library is available for use with [Composer](https://packagist.org/packages/jiri.jozif/datesuninfo) — add it to your project by running:

```bash
$ composer require jiri.jozif/datesuninfo
```

## Usage

The replacement function is simple, instead of
```
$dsi = date_sun_info($timestamp, $latitude, $longitude);
```
you type
```
use JiriJozif\DateSunInfo\DateSunInfo;

$dsi = DateSunInfo::get($timestamp, $latitude, $longitude);
```
The class returns the same data as date_sun_info(), but with more accurate values.

Static class method DateSunInfo::get with up to three required parameters; the fourth is optional:
- **$timestamp** (int): The Unix timestamp.
- **$latitude** (float): The latitude of the location in degrees.
- **$longitude** (float): The longitude of the location in degrees.
- **$asObject** (bool, optional): The default output is an array. If the fourth argument asObject is set to `true`, the output will be an object.
Example:
```
$dsi = DateSunInfo::get($timestamp, $latitude, $longitude, true);
echo date('H:i', $dsi->sunrise);
```

You can then use the following methods:
-   `DateSunInfo::get(int $timestamp, float $latitude, float $longitude, ?bool $asObject)`: Returns the same output as the date_sun_info() function, but with more accurate values. 
-   `DateSunInfo::getRiset(int $timestamp, float $latitude, float $longitude, ?bool $asObject)`: Shorter output, returning only the sunset, sunrise, and sun transit times.
-   `DateSunInfo::getAll(int $timestamp, float $latitude, float $longitude, ?bool $asObject)`: Extended output, returning the azimuth of sunset and sunrise, as well as the height of the sun transit.
-   `DateSunInfo::getPosition(int $timestamp, ?float $latitude, ?float $longitude, ?bool $asObject)`: Returns the sun's coordinates for the given timestamp. If latitude and longitude are specified, it also returns the horizon coordinates.
-   `DateSunInfo::hh_mm(int $timestamp)`: Returns the rounded time (to the nearest minute) as a string in hh:mm format, or "\*\*:\*\*" if the sun is continuously above the horizon, or "--:--" if the sun is continuously below the horizon.
-   `DateSunInfo::hh_mm_ss(int $timestamp)`: Returns the time as a string in hh:mm:ss format, or "\*\*:\*\*:\*\*" if the sun is continuously above the horizon, or "--:--:--" if the sun is continuously below the horizon.

Return Values:
-   `sunrise`: The timestamp of the sunrise or `true` is Sun continuously above horizon or `false` if Sun continuously below horizon.
-   `sunset`: The timestamp of the sunset or `true` is Sun continuously above horizon or `false` if Sun continuously below horizon.
-   `transit`: The timestamp when the Sun is at its zenith, i.e. has reached its topmost point. 
-   `civil_twilight_begin`: The start of the civil dawn (The Sun is 6° below the horizon). It ends at sunrise. 
-   `civil_twilight_end`: The end of the civil dusk (The Sun is 6° below the horizon). It starts at sunset. 
-   `nautical_twilight_begin`: The start of the nautical dawn (The Sun is 12° below the horizon). It ends at civil_twilight_begin. 
-   `nautical_twilight_end`: The end of the nautical dusk (The Sun is 12° below the horizon). It starts at civil_twilight_end. 
-   `astronomical_twilight_begin`: The start of the astronomical dawn (The Sun is 18° below the horizon). It ends at nautical_twilight_begin. 
-   `astronomical_twilight_end`: The end of the astronomical dusk (The Sun is 18° below the horizon). It starts at nautical_twilight_end. 
Extended output from method DateSunInfo::getAll():
-   `sunrise_azimuth`: The azimuth of the sunrise in degree.
-   `sunset_azimuth`: The azimuth of the sunset in degree.
-   `transit_height`: The height of the transit in degree.
-   `lowest_height`: The lowest height of the Sun in the sky, in degrees, occurs on the opposite side as the transit.

### Example

```php
<?php
use JiriJozif\DateSunInfo\DateSunInfo;

$dsi = DateSunInfo::getRiset(time(), 51.48, 0.0); //Royal Observatory, Greenwich
echo "The Sun rises today at ", DateSunInfo::hh_mm($dsi['sunrise']), " and sets at ", DateSunInfo::hh_mm($dsi['sunset']);
```

### Options
You can modify the default values using the DateSunInfo::setOptions() method. For example, you can set the height of the observer above the environment or enable additional dawn and dusk calculations.
-   `DateSunInfo::setOptions(array $options)`: Modifies the default values, where options is:
    - `'height' => float $height`: Height of the observer above the environment in meters.
    - `'altitude' => float $altitude`: The default angle for finding sunrise or sunset is -0.833° below the horizon. This is the sum of the refraction at the horizon (34') and the apparent radius of the Sun (16'). It can be modified here.
    - `'topographic_azimuth' => bool $type_azimuth`: If `true`, the class uses the topographic azimuth (North = 0°, East = 90°, South = 180°, West = 270°). If `false`, the class uses the astronomical azimuth (North = 180°, East = 270°, South = 0°, West = 90°).
    - `'twilight' => [float $angle => [string $begin_name, string $end_name]]`: The definition of other twilights.
    
### Example

```php
<?php
use JiriJozif\DateSunInfo\DateSunInfo;

DateSunInfo::setOptions(
    'height' => 300, // Observer on Eiffel tower
    'topographic_azimuth' => false, // Enable astronomical azimuth
    'altitude' => -0.583, // Swedish national alamancs
    'twilight' => [
            10 => ['golden_hour_end', 'golden_hour_begin'], // For photographer
            -15 => ['amateur_astronomical_twilight_begin', 'amateur_astronomical_twilight_end'] // For amateur astronomer
        ]
);

```

## Help

If you have any questions, feel free to contact me at `jiri.jozif@gmail.com`.
