<?php

require_once __DIR__.'/../tidemark-analytics/includes/Options.php';

// The tests run with the PHPUnit that ships with Tidemark: php ../tidemark/vendor/bin/phpunit -c phpunit.xml
require_once __DIR__.'/../../tidemark/vendor/autoload.php';

// Stands in for the WordPress function of the same name, which the settings rules call.
if (! function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1)
    {
        return parse_url($url, $component);
    }
}
