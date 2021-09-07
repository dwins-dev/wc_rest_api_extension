<?php


abstract class WcRestApiExtension
{
    public static string $auth_user = '';
    public static string $auth_password = '';
    public static bool $auth_check = false;


    /**
     * @return array|false
     */
    public static function getData()
    {
        if (self::checkAuth()) {
            return [
                'site_name' => self::getSiteName(),
                'roles' => self::getRoles(),
            ];
        } else {
            return false;
        }
    }

    /**
     * @return array|false
     */
    public static function getUsers()
    {
        return self::checkAuth() ? get_users() : false;
    }

    /**
     * @return false|string|void
     */
    public static function getSiteName()
    {
        return self::checkAuth() ? get_bloginfo('name') : false;
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
        if (self::checkAuth()) {
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['message' => 'Email has the wrong format'];
            } else {
                $user_id = wp_create_user($username, $password, $email);
                if ($role) {
                    $user_id_role = new WP_User($user_id);
                    $user_id_role->set_role($role);
                }
                return $user_id;
            }
        } else {
            return false;
        }

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
                    'display_name' => $username
                ];
                if ($role) $userdata['role'] = $role;
                return wp_insert_user($userdata);
            }
        } else {
            return false;
        }

    }

    /**
     * @param $ID int user_id
     * @return bool|string[]
     */
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

    /**
     * @return false|array Array roles
     */
    public static function getRoles()
    {
        if (self::checkAuth()) {
            return wp_roles()->role_names;
        } else {
            return false;
        }
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
                    return self::$auth_check = true;
                } else {
                    self::$auth_user = '';
                    self::$auth_password = '';
                    return self::$auth_check = false;
                }
            }
        } else {
            return false;
        }
    }
}


