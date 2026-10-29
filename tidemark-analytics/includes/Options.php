<?php

namespace Tidemark\Analytics;

/**
 * The plugin's settings and the rules that follow from them. Nothing here talks to WordPress, so it can be tested on its own.
 */
class Options
{
    /** Script variants a site can switch on, in the order Tidemark puts them in the file name. */
    const VARIANTS = ['outbound-links', 'file-downloads', 'form-submissions', 'engagement'];

    /** The file names Tidemark serves: "script" or a site's own ten-character code, then variants, then .js. */
    const SCRIPT_FILE_PATTERN = '/^(script|[a-f0-9]{10})(\.[a-z-]+)*\.js$/';

    public static function defaults(): array
    {
        return [
            'address' => '',
            'domain' => '',
            'script_file' => '',
            'variants' => [],
            'excluded_roles' => ['administrator'],
        ];
    }

    /** Stored settings laid over the defaults, so a missing key never has to be checked for. */
    public static function merge($stored): array
    {
        return array_merge(self::defaults(), is_array($stored) ? $stored : []);
    }

    /**
     * Cleans what was submitted on the settings page.
     *
     * @param  string[]  $knownRoles  Role slugs that exist on this site.
     */
    public static function sanitize($input, array $knownRoles): array
    {
        $input = is_array($input) ? $input : [];

        return [
            'address' => self::normalizeAddress(self::text($input, 'address')),
            'domain' => self::normalizeDomain(self::text($input, 'domain')),
            'script_file' => self::normalizeScriptFile(self::text($input, 'script_file')),
            'variants' => array_values(array_intersect(self::VARIANTS, self::list($input, 'variants'))),
            'excluded_roles' => array_values(array_intersect($knownRoles, self::list($input, 'excluded_roles'))),
        ];
    }

    /**
     * The address of the Tidemark install without a trailing slash. Pasting the whole script address works too: anything from
     * /js/ onward is dropped. Returns an empty string when there is no usable http or https address.
     */
    public static function normalizeAddress(string $address): string
    {
        $parts = parse_url(trim($address));

        if (! is_array($parts) || empty($parts['host']) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return '';
        }

        $path = preg_replace('#/js(/[^/]*)?$#', '', rtrim($parts['path'] ?? '', '/'));

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$path;
    }

    /** A bare host name: no scheme, path or port, lower case. Returns an empty string when it is not a valid name. */
    public static function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^[a-z]+://#', '', $domain);
        $domain = explode('/', $domain)[0];

        return preg_match('/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/', $domain) ? $domain : '';
    }

    /** A script file name copied from the Tidemark snippet, or an empty string when it is not one Tidemark serves. */
    public static function normalizeScriptFile(string $file): string
    {
        $file = strtolower(basename(trim($file)));

        return preg_match(self::SCRIPT_FILE_PATTERN, $file) ? $file : '';
    }

    /** The file to load: the one copied from the snippet if there is one, else script.js with the chosen variants. */
    public static function scriptFile(array $options): string
    {
        if ($options['script_file'] !== '') {
            return $options['script_file'];
        }

        $variants = array_intersect(self::VARIANTS, (array) $options['variants']);

        return implode('.', array_merge(['script'], $variants)).'.js';
    }

    public static function scriptUrl(array $options): string
    {
        return $options['address'].'/js/'.self::scriptFile($options);
    }

    /** The site name sent with every pageview: the one set here, else this site's own host name. */
    public static function trackedDomain(array $options, string $siteHost): string
    {
        return $options['domain'] !== '' ? $options['domain'] : self::normalizeDomain($siteHost);
    }

    public static function isConfigured(array $options): bool
    {
        return $options['address'] !== '';
    }

    /** Whether a person with these roles is left out of the statistics. */
    public static function isExcluded(array $userRoles, array $options): bool
    {
        return count(array_intersect($userRoles, (array) $options['excluded_roles'])) > 0;
    }

    private static function text(array $input, string $key): string
    {
        return isset($input[$key]) && is_string($input[$key]) ? $input[$key] : '';
    }

    private static function list(array $input, string $key): array
    {
        return isset($input[$key]) && is_array($input[$key]) ? array_filter($input[$key], 'is_string') : [];
    }
}
