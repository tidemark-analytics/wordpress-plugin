<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tidemark\Analytics\Options;

class OptionsTest extends TestCase
{
    #[DataProvider('addresses')]
    public function test_address_is_normalized(string $input, string $expected): void
    {
        $this->assertSame($expected, Options::normalizeAddress($input));
    }

    public static function addresses(): array
    {
        return [
            'plain' => ['https://stats.example.com', 'https://stats.example.com'],
            'trailing slash' => ['https://stats.example.com/', 'https://stats.example.com'],
            'subfolder' => ['https://example.com/tidemark/', 'https://example.com/tidemark'],
            'port' => ['http://localhost:8000', 'http://localhost:8000'],
            'pasted script address' => ['https://stats.example.com/js/3fa91c0b2e.engagement.js', 'https://stats.example.com'],
            'pasted script folder' => ['https://stats.example.com/js/', 'https://stats.example.com'],
            'query and fragment dropped' => ['https://stats.example.com/?a=1#top', 'https://stats.example.com'],
            'no scheme' => ['stats.example.com', ''],
            'other scheme' => ['javascript:alert(1)', ''],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('domains')]
    public function test_domain_is_normalized(string $input, string $expected): void
    {
        $this->assertSame($expected, Options::normalizeDomain($input));
    }

    public static function domains(): array
    {
        return [
            'plain' => ['example.com', 'example.com'],
            'upper case' => ['Example.COM', 'example.com'],
            'with scheme and path' => ['https://www.example.com/blog', 'www.example.com'],
            'spaces' => ['  example.com  ', 'example.com'],
            'quotes are refused' => ['exa"mple.com', ''],
            'empty' => ['', ''],
        ];
    }

    public function test_script_file_must_be_one_tidemark_serves(): void
    {
        $this->assertSame('script.js', Options::normalizeScriptFile('script.js'));
        $this->assertSame('3fa91c0b2e.outbound-links.engagement.js', Options::normalizeScriptFile('3fa91c0b2e.outbound-links.engagement.js'));
        $this->assertSame('3fa91c0b2e.js', Options::normalizeScriptFile('https://stats.example.com/js/3fa91c0b2e.js'));
        $this->assertSame('', Options::normalizeScriptFile('evil.js'));
        $this->assertSame('', Options::normalizeScriptFile('script.js"><script>'));
    }

    public function test_file_name_is_built_from_the_chosen_variants_in_a_fixed_order(): void
    {
        $options = Options::merge(['variants' => ['engagement', 'outbound-links']]);

        $this->assertSame('script.outbound-links.engagement.js', Options::scriptFile($options));
        $this->assertSame('script.js', Options::scriptFile(Options::merge([])));
    }

    public function test_a_script_file_from_the_snippet_wins_over_the_variants(): void
    {
        $options = Options::merge(['address' => 'https://stats.example.com', 'script_file' => '3fa91c0b2e.js', 'variants' => ['engagement']]);

        $this->assertSame('https://stats.example.com/js/3fa91c0b2e.js', Options::scriptUrl($options));
    }

    public function test_tracked_domain_falls_back_to_the_site_host(): void
    {
        $this->assertSame('blog.example.com', Options::trackedDomain(Options::merge([]), 'Blog.Example.com'));
        $this->assertSame('example.com', Options::trackedDomain(Options::merge(['domain' => 'example.com']), 'blog.example.com'));
    }

    public function test_nothing_is_added_until_an_address_is_saved(): void
    {
        $this->assertFalse(Options::isConfigured(Options::merge([])));
        $this->assertTrue(Options::isConfigured(Options::merge(['address' => 'https://stats.example.com'])));
    }

    public function test_administrators_are_left_out_by_default(): void
    {
        $options = Options::merge([]);

        $this->assertTrue(Options::isExcluded(['administrator'], $options));
        $this->assertFalse(Options::isExcluded(['subscriber'], $options));
        $this->assertFalse(Options::isExcluded([], $options));
    }

    public function test_submitted_settings_are_cleaned(): void
    {
        $clean = Options::sanitize([
            'address' => ' https://stats.example.com/ ',
            'domain' => 'Example.com',
            'script_file' => 'nope.js',
            'variants' => ['engagement', 'made-up', ['array']],
            'excluded_roles' => ['editor', 'ghost'],
        ], ['administrator', 'editor']);

        $this->assertSame([
            'address' => 'https://stats.example.com',
            'domain' => 'example.com',
            'script_file' => '',
            'variants' => ['engagement'],
            'excluded_roles' => ['editor'],
        ], $clean);
    }

    public function test_unchecking_every_role_is_respected(): void
    {
        $this->assertSame([], Options::sanitize(['address' => 'https://stats.example.com'], ['administrator'])['excluded_roles']);
    }

    public function test_garbage_input_gives_the_empty_settings(): void
    {
        $clean = Options::sanitize('nonsense', ['administrator']);

        $this->assertSame('', $clean['address']);
        $this->assertSame([], $clean['variants']);
    }
}
