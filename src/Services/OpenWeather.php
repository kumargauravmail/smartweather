<?php

namespace Drupal\smartweather\Services;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class OpenWeather
 * @package Drupal\smartweather\Services
 */
class OpenWeather {

  /**
   * @var ClientInterface
   */
  private $http_client;

  /**
   * @var CacheBackendInterface
   */
  private $cache;

  public function __construct(ClientInterface $client, CacheBackendInterface $cache) {
    $this->http_client = $client;
    $this->cache = $cache;
  }

  public function get_weather($api_key, $latitude, $longitude, $units = 'metric') {
    if ($latitude == "" || $longitude == "" || $api_key == "") {
      // if latitude and longitude are not calculated from IP address or not available
      // if OpenWeather API key is not added from admin section
      $weather_data = array("error_data" => t("Either values for OpenWeather API Key, Latitude / Longitude not available or can not be fetched through IP address."));
    }
    else {
      // get data from cache if exist
      $cid = 'openweatherdata:' . $latitude . $longitude;
      $cached_data = $this->cache->get($cid);

      // make a request to openweathermap.org to get data if not cached
      if (!$cached_data) {
        // Use http client to get location details from openweathermap.org
        // One Call API:: https://openweathermap.org/api/one-call-api#data
        $openweather_endpoint = "https://api.openweathermap.org/data/2.5/onecall?" . 'lat=' . $latitude . '&lon=' . $longitude . '&units=' . $units . '&appid=' . $api_key . '&exclude=minutely,hourly,alerts' . '&mode=json';

        $options['http_errors'] = FALSE;
        $response = $this->http_client->get($openweather_endpoint, $options);

        // if http error from getting weather details
        if ($response->getStatusCode() != 200) {
          $weather_data = array("error_data" => t("Weather details can not be fetched from OpenWeathermap.org."));
        } else {
          $weather_data = json_decode($response->getBody()->getContents(), TRUE);
          $this->cache->set($cid, $weather_data, \Drupal::time()->getRequestTime() + (3600));
        }
      }
      else {
        $weather_data = $cached_data->data;
      }
    }

    return $weather_data;
  }

}
