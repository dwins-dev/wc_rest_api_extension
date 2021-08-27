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


function wc_rest_api_extension_site_name()
{
    $wc_rest_api_extension = WcRestApiExtension::getInstance();
    return $wc_rest_api_extension->getSiteName();
}

function wc_rest_api_extension_users()
{
    $wc_rest_api_extension = WcRestApiExtension::getInstance();
    return $wc_rest_api_extension->getUsers();
}

function wc_rest_api_extension_user_create()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    return WcRestApiExtension::createUser($username, $password, $email);
}

function wc_rest_api_extension_user_update()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $ID = $data['ID'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    return WcRestApiExtension::updateUser($ID, $username, $password, $email);
}

function wc_rest_api_extension_user_delete()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $ID = $data['ID'] ?? '';
    return WcRestApiExtension::deleteUser($ID);
}

add_action('rest_api_init', function () {
    register_rest_route('wc-rest-api-extension/v1', '/site-name', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_site_name',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/users', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_users',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-create', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_create',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-update', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_update',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-delete', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_delete',
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


    public static function checkAuth(): bool
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


    public static function getUsers()
    {
        return self::checkAuth() ? get_users() : false;
    }

    public static function getSiteName()
    {
        return self::checkAuth() ? get_bloginfo('name') : false;
    }

    public static function createUser($username, $password = '', $email = '')
    {
        if (self::checkAuth()) {
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['message' => 'Email has the wrong format'];
            } else {
                return wp_create_user($username, $password, $email);
            }
        } else {
            return false;
        }

    }

    public static function updateUser($ID, $username, $password = '', $email = '')
    {
        if (!$ID) {
            return ['message' => 'No user id specified'];
        } elseif (self::checkAuth()) {
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['message' => 'Email has the wrong format'];
            } else {
                if (!$password) {
                    $user_obj = get_userdata($ID);
                    $password = $user_obj->user_pass;
                } else {
                    $password = wp_hash_password($password);
                }

                $userdata = [
                    'ID' => $ID,
                    'user_pass' => $password,
                    'user_email' => $email,
                    'user_login' => $username,
                    'user_nicename' => $username,
                    'display_name' => $username,
                ];
                return wp_insert_user($userdata);
            }
        } else {
            return false;
        }

    }

    public static function deleteUser($ID)
    {
        if (self::checkAuth()) {
            if (isset($ID) && $ID) {
                $user = new WP_User($ID);
                if (!$user->exists()) {
                    return ['message' => 'User does not exist'];
                }
                global $wpdb;
                do_action('delete_user', $ID, null, $user);
                $wpdb->delete($wpdb->users, array('ID' => $ID));
                clean_user_cache($user);
                do_action('deleted_user', $ID, null, $user);
                return true;
            } else {
                return ['message' => 'user_id required'];
            }
        } else {
            return false;
        }
    }
}


