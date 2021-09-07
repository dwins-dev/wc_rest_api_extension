<?php
/*
Plugin Name: Woocommerce REST API extension
Description: Равширение REST API woocommerce
Version: 1.0.0
Author: DWINS
Author URI: http://t.me/maksim_logvinenko
*/

require_once __DIR__ .'/includes/WcRestApiExtension.php';


/**
 * @return false|array Array ['site_name'=>'...', 'roles' => [...]]
 */
function wc_rest_api_extension_site_data()
{
    return WcRestApiExtension::getData();
}

/**
 * @return false|string
 */
function wc_rest_api_extension_site_name()
{
    return WcRestApiExtension::getSiteName();
}

/**
 * @return array|false
 */
function wc_rest_api_extension_users()
{
    return WcRestApiExtension::getUsers();
}

/**
 * @return false|int|string[]|WP_Error
 */
function wc_rest_api_extension_user_create()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? '';
    return WcRestApiExtension::createUser($username, $password, $email, $role);
}

/**
 * @return false|int|string[]|WP_Error
 */
function wc_rest_api_extension_user_update()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $ID = $data['ID'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? '';
    return WcRestApiExtension::updateUser($ID, $username, $password, $email, $role);
}

/**
 * @return bool|string[]
 */
function wc_rest_api_extension_user_delete()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $ID = $data['ID'] ?? '';
    return WcRestApiExtension::deleteUser($ID);
}

/**
 * @return array|false
 */
function wc_rest_api_extension_roles()
{
    return WcRestApiExtension::getRoles();
}


add_action('rest_api_init', function () {
    register_rest_route('wc-rest-api-extension/v1', '/site-data', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_site_data',
    ));
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
    register_rest_route('wc-rest-api-extension/v1', '/roles', array(
        'methods' => 'POST',
        'callback' => 'wc_rest_api_extension_roles',
    ));
});

