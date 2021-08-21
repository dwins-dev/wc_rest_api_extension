<?php
/*
Plugin Name: Woocommerce REST API extension
Description: Равширение REST API woocommerce
Version: 1.0.0
Author: DWINS
Author URI: http://t.me/maksim_logvinenko
*/

/**
 * @param $args
 * @param $url
 * @return mixed
 */


function wc_rest_api_extension_users()
{
    $wc_rest_api_extension = WcRestApiExtension::getInstance();
    return $wc_rest_api_extension->getUsers();
}

function wc_rest_api_extension_site_name()
{
    $wc_rest_api_extension = WcRestApiExtension::getInstance();
    return $wc_rest_api_extension->getSiteName();
}

add_action('rest_api_init', function () {
    register_rest_route('wc-rest-api-extension/v1', '/users', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_users',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/site-name', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_site_name',
    ));
});


class WcRestApiExtension
{
    private static ?WcRestApiExtension $instance = null;
    /**
     * @var array
     */
    private static array $users = [];

    /**
     * @return WcRestApiExtension
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }


    public function checkAuth(): bool
    {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            global $wpdb;
            $consumer_key = wc_api_hash($_SERVER['PHP_AUTH_USER']);
            $consumer_secret = $_SERVER['PHP_AUTH_PW'];
            $auth = $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE
                                              consumer_key='{$consumer_key}' AND
                                              consumer_secret='{$consumer_secret}'
                                              "
            );
            return count($auth) > 0;
        } else {
            return false;
        }
    }


    public function getUsers()
    {
        return $this->checkAuth() ? get_users() : false;
    }

    public function getSiteName()
    {
        return $this->checkAuth() ? get_bloginfo('name') : false;
    }
}


