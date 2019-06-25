<?php

namespace Drupal\publisher_common_id\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
const DAYS_IN_SECONDS = 86400;

defined( 'PUBCID_PIXEL_MAX_AGE' ) or define( 'PUBCID_PIXEL_MAX_AGE', 1 );

/*
 * Publisher Common ID Controller, called by /pubcid
 */
class PublisherCommonIdController {

    public function updatePubcid()
    {

        \Drupal::service('page_cache_kill_switch')->trigger();

        $config = \Drupal::config('publisher_common_id.settings');

        //If the cookie name is empty, then there is nothing to do
        if (empty($config->get('cookie_name'))) {
            return;
        }

        //Get the setting values
        $cookie_name = $config->get('cookie_name');
        $max_age = $config->get('max_age');
        $cookie_domain = $config->get('cookie_domain');
        $consent_func = $config->get('consent_function');
        $gen_func = $config->get('generate_function');

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
                setcookie($cookie_name, '', time() - 3600, '/', $cookie_domain);
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
            $expires = gmdate('D, d M Y H:i:s T', time() + $max_age * DAYS_IN_SECONDS);
            header('Set-Cookie: ' . $cookie_name . '=' . $value . '; expires=' . $expires . '; path=/; domain='.$cookie_domain. '; SameSite=Lax' );
        }

    }

    /**
     * Function for the pixel response
     * @return array|BinaryFileResponse
     */
    public function extend() {

        $this->updatePubcid();

        $config = \Drupal::config('publisher_common_id.settings');
        $cookie_name = $config->get('cookie_name');
        header( 'Content-Encoding: none' );
        header( 'Content-Type: image/gif' );
        header( 'Content-Length: 43' ) ;

        //Set different caching options based on having cookie
        if (isset($_COOKIE[$cookie_name]) ) {
            $max_age = PUBCID_PIXEL_MAX_AGE * DAY_IN_SECONDS;
            $expires = gmdate('D, d M Y H:i:s T', time() + $max_age );
            header( 'Cache-Control: private, max-age=' . $max_age );
            header( 'Expries: ' . $expires);

        } else {
            header( 'Cache-Control: no-cache' );
            header( 'Pragma: no-cache');
        }
        echo "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x90\x00\x00\xff\x00\x00\x00\x00\x00\x21\xf9\x04\x05\x10\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x04\x01\x00\x3b";
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