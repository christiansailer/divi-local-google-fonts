<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Divi local Google fonts
 * Description:       mirrors google fonts from webfonthelper and loads from the same domain
 * Version:           1.0
 * Author:            Christian Sailer
 * Author URI:        https://christian-sailer.de
 */

// If this file is called directly, abort.
if (!defined( 'WPINC')) {
    die();
}

define('CS_LOCAL_FONT_FILE', __FILE__);
define('CS_LOCAL_FONT_TEXT_DOMAIN', __FILE__);
define('CS_LOCAL_FONT_VERSION', '1.0');

require plugin_dir_path(__FILE__ ) . 'vendor/autoload.php';

\CS\Application::getInstance()->init();