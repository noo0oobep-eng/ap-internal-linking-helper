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
    }

    public function init() {
        // Future: register editor sidebar, REST endpoints, etc.
    }
}
