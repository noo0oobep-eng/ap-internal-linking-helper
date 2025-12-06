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
        add_action('admin_notices', [$this, 'admin_notice']);
    }

    public function init() {
        // Future: register editor sidebar, REST endpoints, etc.
    }

    public function admin_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="notice notice-success is-dismissible"><p>AP Internal Linking Helper is active.</p></div>';
    }
}

