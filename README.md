# Tidemark Analytics for WordPress

Adds the [Tidemark](https://tidemarkanalytics.com) tracking script to a WordPress site. Enter the address of your Tidemark, and every public page sends privacy-friendly pageviews to it. No cookies, and nothing stored in WordPress except the plugin's settings.

- Leaves signed-in administrators (or any roles you choose) out of the statistics; previews are never counted.
- A separate deferred script, so page caches and CDNs work as usual.
- Optional outbound link, file download, form and engagement tracking.
- `plausible('Event name')` works from themes and other plugins.

The plugin is in [`tidemark-analytics/`](tidemark-analytics/); its WordPress.org description is [`readme.txt`](tidemark-analytics/readme.txt).

## Development

The settings rules live in `includes/Options.php` and do not call WordPress, so they are tested on their own with the PHPUnit that ships with Tidemark:

```bash
../tidemark/vendor/bin/phpunit -c phpunit.xml
../tidemark/vendor/bin/pint --config ../tidemark/pint.json .
```

`bash build.sh` writes `tidemark-analytics.zip` for upload.

Licensed under the GPL, version 2 or later, as WordPress requires.
