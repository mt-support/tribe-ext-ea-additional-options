=== Event Aggregator Extension: Additional Options ===
Contributors: theeventscalendar, codingmusician, aguseo
Donate link: https://evnt.is/29
Tags: events, calendar
Requires at least: 6.3
Tested up to: 6.7.2
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPL version 3 or any later version
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Adds extra options to Event Aggregator settings and imports.

== Installation ==

Install and activate like any other plugin!

* You can upload the plugin zip file via the *Plugins ‣ Add New* screen
* You can unzip the plugin and then upload to your plugin directory (typically _wp-content/plugins_) via FTP
* Once it has been installed or uploaded, simply visit the main plugin list and activate it

== Frequently Asked Questions ==

= Where can I find more extensions? =

Please visit our [extension library](https://theeventscalendar.com/extensions/) to learn about our complete range of extensions for The Events Calendar and its associated plugins.

= What if I experience problems? =

We're always interested in your feedback and our [Help Desk](https://support.theeventscalendar.com/) are the best place to flag any issues. Do note, however, that the degree of support we provide for extensions like this one tends to be very limited.

== Changelog ==

= [1.5.0] 2025-03-10 =

* Feature - Add setting to automatically delete past ignored events.
* Feature - Add a 'Permanently delete all' button to the ignored view of events.
* Tweak - Adjust the 'Move to trash' label in the bulk options dropdown to 'Move to trash/ignore' to correctly reflect the handling of imported events.
* Tweak - Adjust styling of the settings so it matches the new admin UI.
* Fix - Adjust how Venues are handled in the block editor template to make sure they show up in the block editor.
* Tweak - Add Meetup as a valid import source when using block editor template.
* Tweak - Adjust import field mapping to make sure description is only removed when there is an alternative.

= [1.4.2] 2023-11-01 =

* Fix - Update so that DST changes are taken into account for time zone change calculations.
* Fix - Make sure that the right event times are used for calculations when the source uses am/pm format.
* Fix - Ensure that UTC times are used when they are available.

= [1.4.1] 2023-06-24 =

* Fix - Update to use the new Service_Provider contract in common.

= [1.4.0] 2023-02-15 =

* Feature - Add option to use a draft event as a template for imported events.
* Fix - The query responsible for deleting events that were removed from the calendar did not work when Events Calendar Pro was active. [EXT-301]

= [1.3.0] Skipped for compatibility reasons

= [1.2.1] 2020-10-02 =

* Fix - Timezone additional option was not reflecting the selected value.

= [1.2.0] 2020-09-23 =

* Feature - Update plugin structure to follow the new Extension Layout.
* Feature - Add option to purge (remove events) from a Schedule import.

= [1.1.1] 2020-07-27 =

* Bug Fix - Fixed typo that was preventing the Event URL field to be populated

= [1.1] 2020-07-21 =

* Feature - Import field to assign website URL to all events

= [1.0.0] 2020-05-25 =

* Initial release
