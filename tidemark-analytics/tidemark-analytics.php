<?php

use Tidemark\Analytics\Settings;
use Tidemark\Analytics\Tracker;

/**
 * Plugin Name:       Tidemark Analytics
 * Plugin URI:        https://tidemarkanalytics.com
 * Description:       Adds the Tidemark tracking script to your site. Privacy-friendly statistics with no cookies, and nothing stored in WordPress.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Tidemark
 * Author URI:        https://tidemarkanalytics.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tidemark-analytics
 */
if (! defined('ABSPATH')) {
    exit;
}

define('TIDEMARK_ANALYTICS_VERSION', '1.0.0');
define('TIDEMARK_ANALYTICS_FILE', __FILE__);

require_once __DIR__.'/includes/Options.php';
require_once __DIR__.'/includes/Settings.php';
require_once __DIR__.'/includes/Tracker.php';

function tidemark_analytics_boot()
{
    $settings = new Settings;
    $settings->register();

    (new Tracker($settings))->register();
}

tidemark_analytics_boot();
