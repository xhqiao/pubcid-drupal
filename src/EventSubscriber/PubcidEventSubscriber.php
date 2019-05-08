<?php

/**
* @file
* Contains \Drupal\pubcid_cookie_management\EventSubscriber\PubcidEventSubscriber.
*/

namespace Drupal\pubcid_cookie_management\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event Subscriber PubcidEventSubscriber.
*/
class PubcidEventSubscriber implements EventSubscriberInterface {

/**
* Code that should be triggered on event specified
*/
public function onRespond(FilterResponseEvent $event) {


  \Drupal::service('page_cache_kill_switch')->trigger();

  $config = \Drupal::config('pubcid_cookie_management.settings');

  //If the cookie name is empty, then there is nothing to do
  if (empty($config->get('cookie_name'))) {
    return;
  }

    //Get the setting values
  $cookie_name       = $config->get('cookie_name');
  $max_age           = $config->get('max_age');
  $cookie_path       = $config->get('cookie_path');
  $consent_func      = $config->get('consent_function');
  $gen_func          = $config->get('generate_function');

  // Obtain site domain if defined
  if (defined(COOKIE_DOMAIN)) {
    $cookie_domain = COOKIE_DOMAIN;
  } else {
    $cookie_domain = "";
  }

  //Initialize cookie value
  $value = NULL;

  // See if the cookie exists already
  if (isset($_COOKIE[$cookie_name ])) {
    $value = $_COOKIE[$cookie_name];
  }

  // Check consent.  There is consent when
  // 1. Consent function is empty
  // 2. Consent function returns true
  if (!empty($consent_func) && is_callable($consent_func) && !call_user_func($consent_func)) {
    // Delete old cookie if there is no consent
    if ( isset( $value ) ) {
      setcookie( $cookie_name, '', time() - 3600, $cookie_path, $cookie_domain );
    }
    return;
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
    setcookie($cookie_name, $value, time() + $max_age * 86400 , $cookie_path, $cookie_domain);
  }

}  

/**
* {@inheritdoc}
*/
public static function getSubscribedEvents() {
  $events[KernelEvents::RESPONSE][] = ['onRespond', 30];
  return $events;
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
