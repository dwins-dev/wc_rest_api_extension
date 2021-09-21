<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Webhooks')) {
    abstract class Webhooks
    {
        /**
         * Initial events delete/create/update
         */
        public static function init()
        {
            add_action('deleted_user', __CLASS__ . '::delete_user', 10, 1);
            add_action('profile_update', __CLASS__ . '::update_user', 10, 3);
        }

        /**
         * Action deleted user to event deleted_user
         * @param $user_id
         */
        public static function delete_user($user_id)
        {
            self::push_user_event('delete', $user_id);
        }

        /**
         * Actions update/create user to events profile_update
         * @param $user_id
         * @param ?WP_User $old_user_data
         * @param ?array $user_data
         */
        public static function update_user($user_id, WP_User $old_user_data = null, array $user_data = null)
        {
            if (isset($user_data['role'])) {
                self::push_user_event('update', $user_id, $user_data['user_email'], $user_data['role']);
            } else {
                self::push_user_event('create', $user_id, $user_data['user_email'], get_userdata($user_id)->roles[0]);
            }
        }

        /**
         * Push ngh user
         * @param string $event_type update|create|delete
         * @param int $event_user_id
         * @param ?array $event_user_email
         * @param ?string $event_user_role
         */
        private static function push_user_event(string $event_type, int $event_user_id, array $event_user_email = null, string $event_user_role = null)
        {

            if ($data = get_option('wc_rest_api_extension_webhook')) {
                if (isset($data['url']) && isset($data['auth_id']) && $data['url'] && $data['auth_id']) {
                    $webhook_url = $data['url'];
                    $webhook_auth_id = $data['auth_id'];

                    global $wpdb;
                    $auth = $wpdb->get_results(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE key_id='{$webhook_auth_id}'"
                    );
                    if (count($auth) > 0) {
                        $body = [
                            'site_url' => site_url(),
                            'user_id' => $event_user_id,
                            'user_email' => $event_user_email,
                            'user_role' => $event_user_role,
                        ];
                        $auth = base64_encode("{$auth[0]->consumer_key}:{$auth[0]->consumer_secret}");
                        $options = [
                            'http' => [
                                "header" =>
                                    "Authorization: Basic $auth\r\n" .
                                    "Content-type: application/x-www-form-urlencoded\r\n",
                                'method' => '',
                                'content' => http_build_query($body),
                            ],
                            "ssl" => [
                                "verify_peer" => false,
                                "verify_peer_name" => false,
                            ],
                        ];
                        switch ($event_type) {
                            case 'update':
                                $options['http']['method'] = 'PATCH';
                                break;
                            case 'delete':
                                $options['http']['method'] = 'DELETE';
                                break;
                            case 'create':
                                $options['http']['method'] = 'POST';
                                break;
                        }
                        file_get_contents("$webhook_url/api/webhooks/connector-users", false, stream_context_create($options));
                    }

                }
            }
        }

        /**
         * Add info this webhook connected
         * @param string $url
         * @return array
         */
        public static function set(string $url): array
        {
            update_option('wc_rest_api_extension_webhook', [
                'url' => $url,
                'auth_id' => WcRestApiExtension::$auth_id,
            ]);
            return ['code' => 'success'];
        }


    }

    Webhooks::init();
}