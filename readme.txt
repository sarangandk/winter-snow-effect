=== Winter Snow Effect ===
Contributors: sarangan112
Tags: snow, winter, effect, seasonal, weather
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically adds a falling snow effect to your website only during winter months (December, January, February).

== Description ==

Bring the winter spirit to your WordPress site with the Winter Snow Effect plugin. This lightweight plugin automatically detects the current month and enables a beautiful, non-intrusive falling snow animation during the winter season (December, January, and February).

**Features:**
*   **Automatic Detection:** Activates only during winter months (customizable date range).
*   **Manual Control:** Enable or disable the effect manually, or use automatic mode.
*   **Fully Customizable:** Adjust snowflake count, size, speed, and opacity.
*   **Performance Optimized:** Pause on scroll, pause on inactive tabs, and respect reduced motion preferences.
*   **Accessibility:** Respects user's reduced motion preferences for better accessibility.
*   **Responsive:** Optimized for smartphones and tablets with separate settings.
*   **Lightweight:** Minimal CSS and JavaScript for optimal performance.

== Installation ==

**From WordPress.org:**
1.  Visit the plugin page: https://wordpress.org/plugins/winter-snow-effect/
2.  Click "Download" or install directly from your WordPress admin dashboard by searching for "Winter Snow Effect".
3.  Activate the plugin through the 'Plugins' screen in WordPress.
4.  Configure settings in Settings > Winter Snow Effect (optional).
5.  Enjoy the snow during the winter months!

**Manual Installation:**
1.  Upload the plugin files to the `/wp-content/plugins/winter-snow-effect` directory.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Configure settings in Settings > Winter Snow Effect (optional).
4.  Enjoy the snow during the winter months!

== Configuration ==

After activation, you can configure the plugin by going to **Settings > Winter Snow Effect** in your WordPress admin panel.

**Enable Snow Effect:**
*   **Automatic:** Snow appears based on your custom date range (default: December 1 - February 28).
*   **Always On:** Snow effect is always active regardless of date.
*   **Always Off:** Snow effect is disabled.

**Date Range:**
Set custom start and end dates for when the snow should appear (in automatic mode). Perfect for different hemispheres or custom seasonal periods.

**Snowflake Settings:**
*   **Count:** Adjust the number of snowflakes for mobile and desktop devices separately.
*   **Size:** Set minimum and maximum size range for snowflakes.
*   **Speed:** Control how fast snowflakes fall.
*   **Opacity:** Adjust transparency of snowflakes.

**Performance Options:**
*   **Respect Reduced Motion:** Automatically disables animation for users who prefer reduced motion (accessibility feature).
*   **Pause on Scroll:** Pauses animation when user is scrolling to improve performance.
*   **Pause on Inactive Tab:** Saves resources by pausing animation when browser tab is in background.

== Frequently Asked Questions ==

= When does the snow appear? =
By default, the snow effect is active during December, January, and February. You can customize the date range in the settings page.

= Can I disable it? =
Yes! You can disable it in three ways:
1. Set "Enable Snow Effect" to "Always Off" in the settings.
2. Deactivate the plugin.
3. Adjust the date range so the current date is outside the range.

= Can I test the snow effect outside of winter months? =
Yes! Set "Enable Snow Effect" to "Always On" in the settings to test the effect at any time.

= Does the plugin respect accessibility preferences? =
Yes! If "Respect Reduced Motion" is enabled (default), the plugin will automatically disable the animation for users who have enabled reduced motion in their system preferences.

= Will the snow effect slow down my website? =
The plugin is optimized for performance with options to pause on scroll and when tabs are inactive. You can also reduce the number of snowflakes if needed.

== Changelog ==

= 2.0 =
*   **Major Update:** Added comprehensive admin settings page.
*   **New Feature:** Manual enable/disable toggle (Always On, Always Off, Automatic).
*   **New Feature:** Custom date range configuration.
*   **New Feature:** Customizable snowflake count, size, speed, and opacity.
*   **New Feature:** Performance optimizations (pause on scroll, pause on inactive tab).
*   **New Feature:** Accessibility support (respects reduced motion preferences).
*   **Enhancement:** Settings are now passed from PHP to JavaScript for better control.

= 1.1 =
*   Optimization: Reduced snow density on smartphones to improve visibility and performance.
*   Enhancement: Increased snowflake size on smaller screens for a better visual effect.

= 1.0 =
*   Initial release.
