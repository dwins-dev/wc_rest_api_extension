<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class WcRestApiExtension
{
    public static string $auth_user = '';
    public static string $auth_password = '';
    public static bool $auth_check = false;
    public static ?int $auth_id =  null;
    public static $WP_REST_Users_Controller;


    /**
     * @return array
     */
    public static function getData(): array
    {
        return [
            'site_name' => self::getSiteName(),
            'roles' => self::getRoles(),
        ];
    }

    /**
     * @return array
     */
    public static function getUsers(): array
    {
        return get_users();
    }

    /**
     * @return false|string|void
     */
    public static function getSiteName()
    {
        return get_bloginfo('name');
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $role
     * @return false|int|string[]|WP_Error
     */
    public static function createUser(string $username, string $password = '', string $email = '', string $role = '')
    {
        $create_user_request = new WP_REST_Request();
        $create_user_request->set_param('username', $username);
        $create_user_request->set_param('password', $password ?? wp_generate_password(8, false, false));
        $create_user_request->set_param('email', $email);
        if ($role) $create_user_request->set_param('roles', [$role]);

        $WP_REST_Users_Controller = self::WP_REST_Users_Controller_initial();
        return $WP_REST_Users_Controller->create_item($create_user_request);

    }

    /**
     * @param int $ID user_id
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $role
     * @return false|int|string[]|WP_Error
     */
    public static function updateUser(int $ID, string $username, string $password = '', string $email = '', string $role = '')
    {
        $update_user_request = new WP_REST_Request();
        $update_user_request->set_param('id', $ID);
        if ($password) $update_user_request->set_param('password', $password);
        if ($email) $update_user_request->set_param('email', $email);
        if ($role) $update_user_request->set_param('roles', [$role]);

        $WP_REST_Users_Controller = self::WP_REST_Users_Controller_initial();

        return $WP_REST_Users_Controller->update_item($update_user_request);
    }

    /**
     * @param int $ID user_id
     * @return WP_Error|WP_REST_Response|WP_User
     */
    public static function deleteUser(int $ID)
    {
        $delete_user_request = new WP_REST_Request();
        $delete_user_request->set_param('id', $ID);
        $delete_user_request->set_param('force', true);
        $WP_REST_Users_Controller = self::WP_REST_Users_Controller_initial();
        return $WP_REST_Users_Controller->delete_item($delete_user_request);
    }

    /**
     * @return array Array roles
     */
    public static function getRoles(): array
    {
        return wp_roles()->role_names;
    }

    /**
     * @return bool
     */
    public static function checkAuth(): bool
    {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            if (self::$auth_check && $_SERVER['PHP_AUTH_USER'] === self::$auth_user && $_SERVER['PHP_AUTH_PW'] === self::$auth_password) {
                return true;
            } else {
                global $wpdb;
                $consumer_key = wc_api_hash($_SERVER['PHP_AUTH_USER']);
                $consumer_secret = $_SERVER['PHP_AUTH_PW'];
                $auth = $wpdb->get_results(
                    "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE
                                              consumer_key='{$consumer_key}' AND
                                              consumer_secret='{$consumer_secret}'
                                              "
                );
                if (count($auth) > 0) {
                    self::$auth_user = $_SERVER['PHP_AUTH_USER'];
                    self::$auth_password = $_SERVER['PHP_AUTH_PW'];
                    self::$auth_id = $auth[0]->key_id;
                    return self::$auth_check = true;
                } else {
                    self::$auth_user = '';
                    self::$auth_password = '';
                    self::$auth_id = null;
                    return self::$auth_check = false;
                }
            }
        } else {
            return false;
        }
    }

    public static function WP_REST_Users_Controller_initial(): WP_REST_Users_Controller
    {
        if (!self::$WP_REST_Users_Controller)
            self::$WP_REST_Users_Controller = new WP_REST_Users_Controller;
        return self::$WP_REST_Users_Controller;
    }
}


