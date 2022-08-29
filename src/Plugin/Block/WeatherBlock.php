<?php

namespace Drupal\smartweather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\smartweather\Services\ClientLocation;
use Drupal\smartweather\Services\OpenWeather;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Openweather Block' block.
 *
 * @Block(
 *   id = "openweather_block",
 *   admin_label = @Translation("Smart Weather Block"),
 *   category = @Translation("Openweather")
 * )
 */
class WeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var array|mixed
   */
  private $location_data;

  /**
   * @var mixed
   */
  private $latitude;

  /**
   * @var mixed
   */
  private $longitude;

  /**
   * @var
   */
  private $config;

  /**
   * @var ClientLocation
   */
  private $client_location;

  /**
   * @var mixed|string
   */
  private $error_message = "";

  /**
   * @var
   */
  private $weather_data;
  /**
   * @var OpenWeather
   */
  private $open_weather;

  /**
   * WeatherBlock constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param ClientLocation $client_location
   * @param OpenWeather $open_weather
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientLocation $client_location, OpenWeather $open_weather) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->config = \Drupal::config('smartweather.settings');

    // set the objects of services
    $this->client_location = $client_location;
    $this->open_weather = $open_weather;

    // get location details
    $this->get_lat_long();

    // Set the error to empty initially, this will be tracked in twig file
    $this->weather_data['error'] = "";
    // data value will be set in weather_details function
    $this->weather_data['data'] = "";

    // Show error message if any, on getting latitude and longitude by IP address
    if($this->error_message !== "") {
      $this->weather_data['error'] = $this->error_message;
    }
    else {
      // Now get the weather details, as we are able to get the location details
      $this->weather_details();

      // Show error message if any, on getting weather data
      if($this->error_message !== "") {
        $this->weather_data['error'] = $this->error_message;
      }
    }
  }

  /**
   * Get latitude, longitude
   * and set the variables
   */
  public function get_lat_long() {

    // get the configuration values set in admin section, if any
    $default_ip = trim($this->config->get('openweather_default_ip'));
    $default_lat = trim($this->config->get('openweather_default_lat'));
    $default_long = trim($this->config->get('openweather_default_long'));

    // if latitude and longitude are not empty
    if($default_lat && $default_long) {
      $this->latitude = $default_lat;
      $this->longitude = $default_long;
    }
    else {
      // pass the default IP address, if provided, in admin settings
      $this->location_data = $this->client_location->get_client_location($default_ip);

      // check if we received any error message
      if(array_key_exists('error_data', $this->location_data)) {
        $this->error_message = $this->location_data['error_data'];
      }
      else {
        $this->latitude = $this->location_data['geoplugin_latitude'];
        $this->longitude = $this->location_data['geoplugin_longitude'];
      }
    }
  }

  /**
   * Get weather details
   */
  public function weather_details() {

    // get the configuration values set in admin section
    $api_key = trim($this->config->get('openweather_api_key'));
    $units = trim($this->config->get('openweather_degrees'));
    $forecast_days = trim($this->config->get('openweather_forecast_days'));

    // get the weather data from the service
    $weather_data = $this->open_weather->get_weather($api_key, $this->latitude, $this->longitude, $units);

    // check if we received any error message
    if(array_key_exists('error_data', $weather_data)) {
      $this->error_message = $weather_data['error_data'];
    }
    else {
      // set the weather data
      $this->weather_data['data'] = $weather_data;
      $this->weather_data['unit'] = $units;
      $this->weather_data['forecast_days'] = $forecast_days;
    }
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return WeatherBlock|static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    //Get ClientLocation Service
    $client_location = $container->get('smartweather.clientlocation');
    $open_weather = $container->get('smartweather.openweather');

    //return the service to class constructor
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $client_location,
      $open_weather
    );
  }

  /**
   * @return array
   */
  public function build() {
    return array(
      '#theme' => 'openweathermap',
      '#weather_data' => $this->weather_data,
      '#attached' => [
        'library' => [
          'smartweather/weatherblock'
        ]
      ]
    );
  }

}
