<?php

namespace Drupal\smartweather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SmartWeatherSettingsForm
 * @package Drupal\smartweather\Form
 */
class SmartWeatherSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return [
      'smartweather.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartweather_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('smartweather.settings');

    $form['openweather_api_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Openweather API key'),
      '#description' => $this->t('Openweather API key. Get it from openweathermap.org'),
      '#default_value' => $config->get('openweather_api_key'),
      '#required' => TRUE,
      '#maxlength' => 150,
    );

    $form['openweather_degrees'] = array(
      '#type' => 'select',
      '#title' => $this->t('Weather Unit Celsius or Fahrenheit'),
      '#description' => $this->t('Select unit of weather measurement'),
      '#default_value' => $config->get('openweather_degrees'),
      '#required' => TRUE,
      '#options' => [
        'metric' => t('Celsius'),
        'imperial' => t('Fahrenheit'),
      ],
    );

    $forcast_days = array(
      '' => t('No Forecast'),
      '1' => t('1 Days'), '2' => t('2 Days'), '3' => t('3 Days'), '4' => t('4 Days'),
      '5' => t('5 Days'), '6' => t('6 Days'), '7' => t('7 Days')
    );

    $form['openweather_forecast_days'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select number of weather Forecast days to display'),
      '#description' => $this->t('Select number of Forecast Days to display. By default weather for current date is displayed, even if this value is selected or not.'),
      '#default_value' => $config->get('openweather_forecast_days'),
      '#options' => $forcast_days,
    );

    $form['openweather_default_lat'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Openweather default location Latitude'),
      '#description' => $this->t('Specify Latitude only if you DO NOT require module/block to display website visitor location specific weather.'),
      '#default_value' => $config->get('openweather_default_lat'),
      '#maxlength' => 50,
    );

    $form['openweather_default_long'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Openweather default location Longitude'),
      '#description' => $this->t('Specify Longitude only if you DO NOT require module/block to display website visitor location specific weather.'),
      '#default_value' => $config->get('openweather_default_long'),
      '#maxlength' => 50,
    );

    $form['openweather_default_ip'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default public IP address for testing on local development system'),
      '#description' => $this->t('Specify default public IP address for testing on local development system as local development system like on apache may return 127.0.0.1 as IP by default, for which no weather data is available. '),
      '#default_value' => $config->get('openweather_default_ip'),
      '#maxlength' => 50,
    );

    $form['submit'] = array(
     '#type' => 'submit',
     '#value' => t('Save'),
   );

  return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $openweather_api_key = trim($form_state->getValue('openweather_api_key'));
    $openweather_default_lat = trim($form_state->getValue('openweather_default_lat'));
    $openweather_default_long = trim($form_state->getValue('openweather_default_long'));
    $openweather_default_ip = trim($form_state->getValue('openweather_default_ip'));

    if($openweather_api_key == "") {
      $form_state->setErrorByName('openweather_api_key', $this->t('Openweather API key can not be empty'));
    }

    // make sure both default latitude and longitude values are entered
    if(($openweather_default_lat == "" && $openweather_default_long != "") ||  ($openweather_default_lat != "" && $openweather_default_long == "")) {
      $form_state->setError($form['openweather_default_lat'], $this->t('Specify both Latitude and Longitude if you DO NOT want system to display website visitor specific weather details.'));
      $form_state->setError($form['openweather_default_long'], '');
    }

    // make sure to specify only one lat/long OR default IP address
    if($openweather_default_lat != "" && $openweather_default_long != "" && $openweather_default_ip != "") {
      $form_state->setError($form['openweather_default_lat'], $this->t('Specify either (Latitude and Longitude) OR default IP address.'));
      $form_state->setError($form['openweather_default_long'], '');
      $form_state->setError($form['openweather_default_ip'], '');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('smartweather.settings')
      ->set('openweather_api_key', trim($form_state->getValue('openweather_api_key')))
      ->set('openweather_degrees', trim($form_state->getValue('openweather_degrees')))
      ->set('openweather_forecast_days', trim($form_state->getValue('openweather_forecast_days')))
      ->set('openweather_default_lat', trim($form_state->getValue('openweather_default_lat')))
      ->set('openweather_default_long', trim($form_state->getValue('openweather_default_long')))
      ->set('openweather_default_ip', trim($form_state->getValue('openweather_default_ip')))
      ->save();
  }

}
