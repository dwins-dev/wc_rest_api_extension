<?php
/*
Plugin Name: Woocommerce REST API extension
Description: Равширение REST API woocommerce
Version: 1.0.0
Author: DWINS
Author URI: http://t.me/maksim_logvinenko
*/

require_once __DIR__ . '/includes/WcRestApiExtension.php';


/**
 * @return array Array ['site_name'=>'...', 'roles' => [...]]
 */
function wc_rest_api_extension_site_data(): array
{
    return WcRestApiExtension::getData();
}

/**
 * @return string|array
 */
function wc_rest_api_extension_site_name(): string
{
    return WcRestApiExtension::getSiteName();
}

/**
 * @return array
 */
function wc_rest_api_extension_users(): array
{
    return WcRestApiExtension::getUsers();
}

/**
 * @return array|WP_Error
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
 * @return array
 */
function wc_rest_api_extension_roles(): array
{
    return WcRestApiExtension::getRoles();
}

/**
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
add_filter( 'send_password_change_email', '__return_false' );

// todo вынести в отдельный класс WcRestApiExtensionWebhook
const URL_NGH = 'https://ngh-mainframe-wp.test/api/webhooks-connector-users';

function webhook_delete_user($id, $reassign, $user)
{
    test($id, 'delete');
}

function webhook_update_user( $user_id ) {
    test($user_id, 'update');
}
function webhook_create_user($user_id) {
    test($user_id, 'create');
}
function test($id, $message)
{
    error_log("$message: $id");

    $body = [
        'ID' => $id
    ];
//    todo рабочий вариант запроса к сайту проекта
    $response = Requests::post( URL_NGH, array(), $body );

    error_log(print_r($response, 1));
}


// не срабатывает при запросе rest api
add_action('wpmu_new_user', 'webhook_create_user', 10, 2);
// todo сделать что бы не срабатывало при запросе rest api
add_action('deleted_user', 'webhook_delete_user', 10, 3);
add_action('profile_update', 'webhook_update_user', 10, 2);