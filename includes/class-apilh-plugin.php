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
        // Future: register editor sidebar, REST endpoints, etc.
    }

    public function enqueue_editor_assets() {
        $handle = 'apilh-editor';
        $src    = APILH_URL . 'assets/js/editor.js';
        $deps   = ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'];
        $ver    = defined('APILH_VERSION') ? APILH_VERSION : '0.1.0';

        wp_enqueue_script($handle, $src, $deps, $ver, true);
    }
}
