=== Social Share ===
Contributors: akash badole
Donate link: https://github.com/akashsbadole
Tags: social media, share buttons, facebook, twitter, linkedin and custom
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add beautiful, customizable social media sharing buttons to your WordPress site with Social Share.

== Description ==

Social Share is a lightweight and customizable social media sharing plugin for WordPress. Allow your visitors to easily share your content on popular Social Media including Facebook, Twitter, LinkedIn, Pinterest, Reddit, via Email, and Custom Icon.

### Features

* **Multiple Social Media** - Support for Facebook, Twitter, LinkedIn, Pinterest, Reddit, and Email sharing
* **Customizable Appearance** - Choose from different button styles, sizes, and shapes
* **Flexible Placement** - Automatically place buttons before content, after content, or both
* **Shortcode Support** - Use the `[social_share]` shortcode to place sharing buttons anywhere in your content
* **Custom Styling** - Add your own CSS to match your theme's design
* **Fast & Lightweight** - Minimal impact on your site's performance
* **Mobile-Friendly** - Responsive design works on all devices

### Supported Networks

* Facebook
* Twitter
* LinkedIn
* Pinterest
* Reddit
* Email
* Custom Social Icon

== Installation ==

1. Upload the `social-share` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Social Share to configure the plugin
4. Customize the appearance and select which Social Media to display
5. Optionally use the `[social_share]` shortcode in your content

== Frequently Asked Questions ==

= Can I choose which Social Media to display? =

Yes, you can enable or disable each supported social network in the plugin settings.

= How can I add the sharing buttons to a specific location? =

You can use the `[social_share]` shortcode anywhere in your content or in a widget.

= Can I customize the appearance of the buttons? =

Yes, you can choose from different button styles (icon only, icon and text, text only), sizes (small, medium, large), and shapes (square, rounded, circle). You can also add custom CSS in the plugin settings.

= Does this plugin work with any theme? =

Yes, Social Share is designed to work with any WordPress theme.

= Will this plugin slow down my website? =

No, Social Share is designed to be lightweight and has minimal impact on your site's performance.

== Screenshots ==

1. Social Share Buttons on a blog post
2. Plugin Settings Page
3. Different Button Styles
4. Mobile View

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of the Social Share plugin.

== Shortcode Usage ==

The basic shortcode usage is:

`[social_share]`

You can customize the shortcode with these parameters:

* `networks` - Comma-separated list of networks to display (e.g., facebook,twitter,linkedin)
* `style` - Button style: icon-only, icon-text, or text-only
* `size` - Button size: small, medium, or large
* `shape` - Button shape: square, rounded, or circle

Example:

`[social_share networks="facebook,twitter" style="icon-only" size="large" shape="circle"]`