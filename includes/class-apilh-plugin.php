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

        $exclude = [];
        if ($post_id) {
            $exclude[] = $post_id;
        }

        $cats = $post_id ? wp_get_post_terms($post_id, 'category', ['fields' => 'ids']) : [];
        $tags = $post_id ? wp_get_post_terms($post_id, 'post_tag', ['fields' => 'ids']) : [];

        $tax_parts = [];

        if (!empty($cats)) {
            $tax_parts[] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $cats,
            ];
        }

        if (!empty($tags)) {
            $tax_parts[] = [
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => $tags,
            ];
        }

        $tax_query = [];
        if (count($tax_parts) === 1) {
            $tax_query = $tax_parts;
        } elseif (count($tax_parts) > 1) {
            $tax_query = array_merge(['relation' => 'OR'], $tax_parts);
        }

        $items = [];

        // 1) Related first (same category/tag)
        if (!empty($tax_query)) {
            $related_args = [
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post__not_in'   => $exclude,
                'no_found_rows'  => true,
                'tax_query'      => $tax_query,
            ];

            $related = get_posts($related_args);

            foreach ($related as $p) {
                $exclude[] = $p->ID;

                $items[] = [
                    'id'            => $p->ID,
                    'title'         => get_the_title($p),
                    'link'          => get_permalink($p),
                    'type'          => $p->post_type,
                    'same_category' => $post_id && !empty($cats) ? has_term($cats, 'category', $p) : false,
                    'same_tag'      => $post_id && !empty($tags) ? has_term($tags, 'post_tag', $p) : false,
                ];
            }
        }

        // 2) Fill remaining with recent
        $remaining = 5 - count($items);

        if ($remaining > 0) {
            $recent_args = [
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                'posts_per_page' => $remaining,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post__not_in'   => $exclude,
                'no_found_rows'  => true,
            ];

            $recent = get_posts($recent_args);

            foreach ($recent as $p) {
                $items[] = [
                    'id'            => $p->ID,
                    'title'         => get_the_title($p),
                    'link'          => get_permalink($p),
                    'type'          => $p->post_type,
                    'same_category' => $post_id && !empty($cats) ? has_term($cats, 'category', $p) : false,
                    'same_tag'      => $post_id && !empty($tags) ? has_term($tags, 'post_tag', $p) : false,
                ];
            }
        }

        return rest_ensure_response([
            'items' => $items,
        ]);
    }
}
