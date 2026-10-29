<?php

namespace Tidemark\Analytics;

/**
 * Puts the Tidemark script on the public side of the site.
 */
class Tracker
{
    const HANDLE = 'tidemark-analytics';

    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_filter('script_loader_tag', [$this, 'addAttributes'], 10, 2);
    }

    public function enqueue(): void
    {
        $options = $this->settings->get();

        if (! Options::isConfigured($options) || $this->isExcluded($options)) {
            return;
        }

        // The version is left off the address on purpose: the script reads its variants from its own file name, and a cached
        // copy should stay valid until the file name changes.
        wp_register_script(self::HANDLE, Options::scriptUrl($options), [], null, ['strategy' => 'defer']);

        // Lets themes and plugins call plausible('Event name') before the script has loaded; the script plays these back.
        wp_add_inline_script(self::HANDLE, 'window.plausible=window.plausible||function(){(window.plausible.q=window.plausible.q||[]).push(arguments)};', 'before');

        wp_enqueue_script(self::HANDLE);
    }

    /**
     * Adds the site name the script sends, and the attributes that tell caching and speed plugins to leave the script alone:
     * it needs to run as its own deferred file so it can read its own address.
     */
    public function addAttributes(string $tag, string $handle): string
    {
        if ($handle !== self::HANDLE) {
            return $tag;
        }

        $domain = Options::trackedDomain($this->settings->get(), (string) wp_parse_url(home_url(), PHP_URL_HOST));

        $attributes = ' data-cfasync="false" data-no-optimize="1" data-no-defer="1" data-no-minify="1"';

        if ($domain !== '') {
            $attributes .= ' data-domain="'.esc_attr($domain).'"';
        }

        return preg_replace('/ src=/', $attributes.' src=', $tag, 1);
    }

    /** Previews and the customizer are never counted, and neither are the roles chosen on the settings page. */
    private function isExcluded(array $options): bool
    {
        if (is_preview() || is_customize_preview()) {
            return true;
        }

        return Options::isExcluded((array) wp_get_current_user()->roles, $options);
    }
}
