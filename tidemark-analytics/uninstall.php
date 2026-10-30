<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$tidemark_analytics_option = 'tidemark_analytics_settings';

if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids']) as $tidemark_analytics_site_id) {
        delete_blog_option($tidemark_analytics_site_id, $tidemark_analytics_option);
    }
} else {
    delete_option($tidemark_analytics_option);
}
