<?php

namespace Drupal\pubcid_cookie_management\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PubcidCookieManagementController {

  protected $imageFactory;
  public function onRespond()
  {

    \Drupal::service('page_cache_kill_switch')->trigger();

    $config = \Drupal::config('pubcid_cookie_management.settings');

    //If the cookie name is empty, then there is nothing to do
    if (empty($config->get('cookie_name'))) {
      return $this->imageResponse();
    }

    //Get the setting values
    $cookie_name = $config->get('cookie_name');
    $max_age = $config->get('max_age');
    $cookie_path = $config->get('cookie_path');
    $consent_func = $config->get('consent_function');
    $gen_func = $config->get('generate_function');

    // Obtain site domain if defined
    if (defined(COOKIE_DOMAIN)) {
      $cookie_domain = COOKIE_DOMAIN;
    } else {
      $cookie_domain = "";
    }

    //Initialize cookie value
    $value = NULL;

    // See if the cookie exists already
    if (isset($_COOKIE[$cookie_name])) {
      $value = $_COOKIE[$cookie_name];
    }

    // Check consent.  There is consent when
    // 1. Consent function is empty
    // 2. Consent function returns true
    if (!empty($consent_func) && is_callable($consent_func) && !call_user_func($consent_func)) {
      // Delete old cookie if there is no consent
      if (isset($value)) {
        setcookie($cookie_name, '', time() - 3600, $cookie_path, $cookie_domain);
      }
      return $this->imageResponse();
    }

    // If the cookie doesn't exist, and there is a generate function,
    // then call the function to get a value.
    if (!isset($value)) {
      if (!empty($gen_func) && is_callable($gen_func)) {
        $value = call_user_func($gen_func);
      }
    }

    // Update the cookie
    if (isset($value)) {
      setcookie($cookie_name, $value, time() + $max_age * 86400, $cookie_path, $cookie_domain);
    }

    return $this->imageResponse();

  }

  /**
   * Function for the pixel response
   * @return array|BinaryFileResponse
   */
  public function imageResponse() {

    $this->imageFactory = \Drupal::service('image.factory');

    $success = file_exists(drupal_get_path('module', 'pubcid_cookie_management').'/image-test.gif');
    if($success) {
      $image = $this->imageFactory->get((drupal_get_path('module', 'pubcid_cookie_management').'/image-test.gif'));

      $headers = [
        'Content-Type' => 'image/gif',
      ];

      $uri = $image->getSource();
      if ($uri) {
        return new BinaryFileResponse($uri, 200, $headers);

      }
    } else {
      return array();
    }
  }

}

/**
 * Generate pubcid cookie value
 * @return uuid
 */
function pubcid_value_generation() {
  $uuid_service = \Drupal::service('uuid');
  return $uuid_service->generate();
}
