<?php

namespace Tidemark\Analytics;

/**
 * The settings page under Settings > Tidemark Analytics.
 */
class Settings
{
    const OPTION = 'tidemark_analytics_settings';

    const PAGE = 'tidemark-analytics';

    const GROUP = 'tidemark_analytics';

    public function get(): array
    {
        return Options::merge(get_option(self::OPTION));
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_filter('plugin_action_links_'.plugin_basename(TIDEMARK_ANALYTICS_FILE), [$this, 'addActionLink']);
        add_action('admin_init', [$this, 'addPrivacyPolicyContent']);
    }

    public function addPage(): void
    {
        add_options_page(
            __('Tidemark Analytics', 'tidemark-analytics'),
            __('Tidemark Analytics', 'tidemark-analytics'),
            'manage_options',
            self::PAGE,
            [$this, 'renderPage']
        );
    }

    public function addActionLink(array $links): array
    {
        $url = esc_url(admin_url('options-general.php?page='.self::PAGE));

        array_unshift($links, '<a href="'.$url.'">'.esc_html__('Settings', 'tidemark-analytics').'</a>');

        return $links;
    }

    public function registerSettings(): void
    {
        register_setting(self::GROUP, self::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => Options::defaults(),
        ]);

        add_settings_section('connection', __('Connection', 'tidemark-analytics'), [$this, 'renderConnectionIntro'], self::PAGE);
        add_settings_field('address', __('Tidemark address', 'tidemark-analytics'), [$this, 'renderAddress'], self::PAGE, 'connection', ['label_for' => 'tidemark-address']);
        add_settings_field('domain', __('Site name', 'tidemark-analytics'), [$this, 'renderDomain'], self::PAGE, 'connection', ['label_for' => 'tidemark-domain']);

        add_settings_section('tracking', __('Tracking', 'tidemark-analytics'), '__return_false', self::PAGE);
        add_settings_field('variants', __('Also record', 'tidemark-analytics'), [$this, 'renderVariants'], self::PAGE, 'tracking');
        add_settings_field('script_file', __('Script file', 'tidemark-analytics'), [$this, 'renderScriptFile'], self::PAGE, 'tracking', ['label_for' => 'tidemark-script-file']);
        add_settings_field('excluded_roles', __('Do not track', 'tidemark-analytics'), [$this, 'renderExcludedRoles'], self::PAGE, 'tracking');
    }

    public function sanitize($input): array
    {
        return Options::sanitize($input, array_keys(wp_roles()->get_names()));
    }

    public function addPrivacyPolicyContent(): void
    {
        if (! function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        wp_add_privacy_policy_content(
            __('Tidemark Analytics', 'tidemark-analytics'),
            wp_kses_post(
                '<p class="privacy-policy-tutorial">'.__('Tidemark Analytics loads a script from your Tidemark server on every public page. The script sends the page address, the referrer and the screen width to that server. It sets no cookies, and Tidemark stores no IP address and nothing that follows a visitor from one day to the next. The plugin itself stores only its settings in your WordPress database.', 'tidemark-analytics').'</p>'
            )
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $options = $this->get();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php if (! Options::isConfigured($options)) { ?>
                <div class="notice notice-info inline">
                    <p><?php esc_html_e('Enter the address of your Tidemark install below and save. Nothing is added to your site until you do.', 'tidemark-analytics'); ?></p>
                </div>
            <?php } ?>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::GROUP);
        do_settings_sections(self::PAGE);
        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    public function renderConnectionIntro(): void
    {
        echo '<p>'.esc_html__('The plugin adds the Tidemark script to your public pages. Your statistics stay in Tidemark; WordPress stores only these settings.', 'tidemark-analytics').'</p>';
    }

    public function renderAddress(): void
    {
        $options = $this->get();
        ?>
        <input type="url" id="tidemark-address" class="regular-text code" name="<?php echo esc_attr(self::OPTION); ?>[address]" value="<?php echo esc_attr($options['address']); ?>" placeholder="https://app.tidemarkanalytics.com">
        <p class="description"><?php esc_html_e('Where your Tidemark runs, for example https://stats.example.com. On Tidemark Cloud this is https://app.tidemarkanalytics.com.', 'tidemark-analytics'); ?></p>
        <?php
    }

    public function renderDomain(): void
    {
        $options = $this->get();
        $siteHost = Options::trackedDomain(Options::defaults(), (string) wp_parse_url(home_url(), PHP_URL_HOST));
        ?>
        <input type="text" id="tidemark-domain" class="regular-text code" name="<?php echo esc_attr(self::OPTION); ?>[domain]" value="<?php echo esc_attr($options['domain']); ?>" placeholder="<?php echo esc_attr($siteHost); ?>">
        <p class="description"><?php esc_html_e('The domain of the site as you added it in Tidemark. Leave it empty to use this site\'s own domain.', 'tidemark-analytics'); ?></p>
        <?php
    }

    public function renderVariants(): void
    {
        $options = $this->get();
        $labels = [
            'outbound-links' => __('Clicks on links to other sites', 'tidemark-analytics'),
            'file-downloads' => __('File downloads', 'tidemark-analytics'),
            'form-submissions' => __('Form submissions', 'tidemark-analytics'),
            'engagement' => __('Scroll depth and time on page', 'tidemark-analytics'),
        ];

        echo '<fieldset>';
        foreach ($labels as $variant => $label) {
            printf(
                '<label><input type="checkbox" name="%1$s[variants][]" value="%2$s" %3$s> %4$s</label><br>',
                esc_attr(self::OPTION),
                esc_attr($variant),
                checked(in_array($variant, $options['variants'], true), true, false),
                esc_html($label)
            );
        }
        echo '</fieldset>';
        echo '<p class="description">'.esc_html__('Each one also needs a matching goal in Tidemark to show up under Behaviors.', 'tidemark-analytics').'</p>';
    }

    public function renderScriptFile(): void
    {
        $options = $this->get();
        ?>
        <input type="text" id="tidemark-script-file" class="regular-text code" name="<?php echo esc_attr(self::OPTION); ?>[script_file]" value="<?php echo esc_attr($options['script_file']); ?>" placeholder="<?php echo esc_attr(Options::scriptFile(Options::merge(['script_file' => '', 'variants' => $options['variants']]))); ?>">
        <p class="description"><?php esc_html_e('Optional. If your Tidemark snippet uses a file name of its own, such as 3fa91c0b2e.outbound-links.js, enter it here. It replaces the choices above.', 'tidemark-analytics'); ?></p>
        <?php
    }

    public function renderExcludedRoles(): void
    {
        $options = $this->get();

        echo '<fieldset>';
        foreach (wp_roles()->get_names() as $role => $name) {
            printf(
                '<label><input type="checkbox" name="%1$s[excluded_roles][]" value="%2$s" %3$s> %4$s</label><br>',
                esc_attr(self::OPTION),
                esc_attr($role),
                checked(in_array($role, $options['excluded_roles'], true), true, false),
                esc_html(translate_user_role($name))
            );
        }
        echo '</fieldset>';
        echo '<p class="description">'.esc_html__('Visits by signed-in people with these roles are not counted. Page previews are never counted.', 'tidemark-analytics').'</p>';
    }
}
