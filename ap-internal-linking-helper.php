<?php
/**
 * Plugin Name: AP Internal Linking Helper
 * Plugin URI: https://github.com/noo0oobep-eng/ap-internal-linking-helper
 * Description: Lightweight internal linking suggestions inside the block editor.
 * Version: 0.1.0
 * Author: AP Systems Lab
 * Author URI: https://github.com/noo0oobep-eng
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ap-internal-linking-helper
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}
define('APILH_VERSION', '0.1.0');
define('APILH_PATH', plugin_dir_path(__FILE__));
define('APILH_URL', plugin_dir_url(__FILE__));

require_once APILH_PATH . 'includes/class-apilh-plugin.php';

APILH_Plugin::instance();
