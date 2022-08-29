CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

SmartWeather is a module which provides current weather details and forecast
upto 7 days. The module is smart enough to detect website visitor location and
get the weather details from OpenWeatherMap.org.

 * The module basically automatically detects the visitor IP address and get 
   the location details. On the basis of location detected the module then gets
   the weather details from OpenWeatherMap.org.

 * The location details are fetched from GeoPlugin.com, which offers 120
   requests per minute for free. If your website visitors are close to 120 
   request per minute then you can choose to opt for geoplugin.com premium plan.
   Please refer to FAQ- https://www.geoplugin.com/faq

 * GeoPlugin.com includes GeoLite data created by MaxMind, available from
   <a href="http://www.maxmind.com">http://www.maxmind.com</a>.

 * Weather data is fetched from https://openweathermap.org/ with a limitation of
   60 calls/minute and a total of 1,000,000 calls/month.
   If your website visitors are more than the above mentioned limit then you
   may need to buy a premium subscription.
   Please refer to pricing at- https://openweathermap.org/price

REQUIREMENTS
------------

OpenWeatherMap.org- need to create a free API key by registering 
at OpenWeatherMap.org.

RECOMMENDED MODULES
-------------------
 * None

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   
CONFIGURATION
-------------

 * Configure the SmartWeatherat (/admin/config/smartweather/settings).
 
MAINTAINERS
-----------

Current maintainer:
 * Gaurav Kumar - https://www.drupal.org/u/gauravkumar
