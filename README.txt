=== GDPR Compliance ===
Contributors: scribit
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=riccardosormani@gmail.com&item_name=GDPR Compliance Wordpress plugin donation&no_note=0
Tags: GDPR, compliance, protection, privacy, legislation
Requires at least: 4.0
Tested up to: 6.4.3
Stable tag: 1.3.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GDPR Compliance helps webmasters to accomplish the european GDPR (data protection regulation) allowing users to manage their personal data.

== Description ==

GDPR Compliance helps webmasters to accomplish the european GDPR (data protection regulation) allowing users to manage their personal data.

Select each information regarding users simply selecting it from a list. Customize the labels for a best user readability.
Show with a Shortcode the sensitive data to logged user and allow user to download it.

In the next releases:
* More information on downloaded file, for main contents types

== Installation ==

From your WordPress dashboard

1. Visit ‘Plugins > Add New’
2. Search for ‘GDPR Compliance’
3. Activate the plugin from your Plugins page.

From WordPress.org

1. Download GDPR Compliance zip file
2. Unzip it
3. Upload the unzipped directory to your ‘/wp-content/plugins/’ directory
4. Activate the plugin from your Plugins page.

== Screenshots ==

== Changelog ==

= 1.3.0 =
* Security fixes
* CSV download enabled only for users with "edit_users" capability
* Removed "user_pass" from visible and downloadable informations
* Removed sensible usermeta parameters from visible and downloadable informations
* Wordpress 6.4.3 compatibility

= 1.2.5 =
* Wordpress 5.6 compatibility
* Shortcodes attribute declaration
* Minor changes

= 1.2.4 =
* Wordpress 5.3 compatibility
* Code and documentation improvements
* Minor changes

= 1.2.3 =
* Wordpress 5.2 compatibility
* Minor changes

= 1.2.2 =
* Changed classes organization
* Wordpress 5.1 compatibility

= 1.2.1 =
* Changed classes organization
* Wordpress 5.0 compatibility

= 1.2.0 =
* Added a button in backend users page for download user's personal data
* Backend settings page fully restyled
* Managed some other user information from database
* Added class for frontend privacy page (to simplify css styling)

= 1.1.1 =
* Added user data download function in users page

= 1.1.0 =
* Added "gdpruserdata_download" shortcode for download selected user data in CSV/XLS format (only headers informations for main contents)
* Better shortcode help page
* Added parameters for "gdpruserdata" and "gdpruserdata_download" shortcodes

= 1.0.3 =
* Post content show for contents with no title

= 1.0.2 =
* No limit to shown posts

= 1.0.1 =
* Minor changes

= 1.0.0 =
* First plugin version