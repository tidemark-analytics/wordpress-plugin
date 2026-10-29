<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$tidemarkOption = 'tidemark_analytics_settings';

if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids']) as $tidemarkSiteId) {
        delete_blog_option($tidemarkSiteId, $tidemarkOption);
    }
} else {
    delete_option($tidemarkOption);
}
