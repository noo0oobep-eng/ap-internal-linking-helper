<?php
if (!defined('ABSPATH')) {
    exit;
}

class APILH_Plugin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

public function init() {
    add_action('rest_api_init', [$this, 'register_rest_routes']);
}

public function register_rest_routes() {
    register_rest_route('apilh/v1', '/suggestions', [
        'methods'  => 'GET',
        'callback' => [$this, 'get_suggestions'],
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => [
            'post_id' => [
                'required' => false,
                'type'     => 'integer',
            ],
        ],
    ]);
}

public function get_suggestions($request) {
    $post_id = (int) $request->get_param('post_id');

    $args = [
        'post_type'      => ['post', 'page'],
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post__not_in'   => $post_id ? [$post_id] : [],
        'no_found_rows'  => true,
    ];

    $posts = get_posts($args);

    $items = array_map(function ($p) {
        return [
            'id'    => $p->ID,
            'title' => get_the_title($p),
            'link'  => get_permalink($p),
            'type'  => $p->post_type,
        ];
    }, $posts);

    return rest_ensure_response([
        'items' => $items,
    ]);
}

public function enqueue_editor_assets() {
    $handle = 'apilh-editor';
    $src    = APILH_URL . 'assets/js/editor.js';
    $deps   = [
        'wp-plugins',
        'wp-edit-post',
        'wp-element',
        'wp-components',
        'wp-data',
        'wp-i18n',
        'wp-api-fetch',
    ];
    $ver    = defined('APILH_VERSION') ? APILH_VERSION : '0.1.0';

    wp_enqueue_script($handle, $src, $deps, $ver, true);
 }
}
