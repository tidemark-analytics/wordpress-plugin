=== Tidemark Analytics ===
Contributors: foggymtndrifter
Tags: analytics, privacy, statistics, web analytics, tidemark
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds the Tidemark tracking script to your site. Privacy-friendly statistics with no cookies, and nothing stored in WordPress.

== Description ==

[Tidemark](https://tidemarkanalytics.com) is privacy-first web analytics you can run on your own hosting or use as a hosted service. This plugin connects a WordPress site to it: enter the address of your Tidemark, and the tracking script is added to every public page.

* **No cookies, no personal data.** Tidemark stores no IP addresses and nothing that follows a visitor from one day to the next.
* **Nothing stored in WordPress.** The plugin saves its settings and nothing else. Your statistics stay in Tidemark.
* **Leaves you out.** Signed-in administrators are not counted by default, and you can choose other roles. Page previews are never counted.
* **Works with caching.** The script is a separate, deferred file, so page caches and CDNs can store your pages as usual. The script tag asks the common caching and speed plugins not to combine, delay or rewrite it.
* **Optional extras.** Record clicks on links to other sites, file downloads, form submissions, and scroll depth and time on page.
* **Custom events.** Call `plausible('Signup')` from your theme or another plugin to record your own events.

You need a Tidemark to use this plugin: [Tidemark Cloud](https://tidemarkanalytics.com) or your own install, which is free and open source.

= External services =

This plugin loads a JavaScript file from the Tidemark server whose address you enter in the settings, and that script sends data about each visit to the same server.

* What is sent: the address of the page, the referrer, the screen width, and the names and properties of any events you record. Tidemark counts unique visitors with a one-way hash that changes every day; it does not store the IP address or the browser's user agent and sets no cookies.
* When: on each public page view by visitors who are not excluded in the settings. Nothing is loaded or sent until you enter an address.
* Where: the server you enter. If you use Tidemark Cloud, that is run by Tidemark: [terms of service](https://tidemarkanalytics.com/terms) and [privacy policy](https://tidemarkanalytics.com/privacy). If you run Tidemark yourself, the data goes only to your own server.

== Installation ==

1. Install the plugin from the Plugins screen, or upload the `tidemark-analytics` folder to `/wp-content/plugins/`.
2. Activate it.
3. Go to Settings > Tidemark Analytics, enter the address of your Tidemark and save.
4. In Tidemark, add your site using the same domain. Visits show up within seconds.

== Frequently Asked Questions ==

= Where do I find the address? =

It is the address you open Tidemark at, for example `https://stats.example.com`. On Tidemark Cloud it is `https://app.tidemarkanalytics.com`. If you paste the whole script address from your Tidemark snippet, the plugin keeps only the part it needs.

= What should the site name be? =

The domain you added in Tidemark. If you leave it empty, the plugin uses this site's own domain.

= My Tidemark snippet has a file name like 3fa91c0b2e.js. Do I have to use it? =

It works either way. If you want the same file name as your snippet, enter it under Script file.

= It does not work with my caching or optimization plugin. =

Exclude the Tidemark script from JavaScript combining, delaying and "load on interaction" in that plugin's settings. The script must run as its own file so it can read its settings from its address.

= Why are my own visits missing? =

Signed-in administrators are not counted by default. Change this under Settings > Tidemark Analytics > Do not track.

= What happens to my settings when I delete the plugin? =

They are removed. Nothing else was ever stored in WordPress.

== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
First release.
