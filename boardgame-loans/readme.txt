=== BoardGame Loans ===
Contributors: Tacchan25
Tags: boardgame, loans, rental, library, membership, ludotheque
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A complete lightweight solution to manage board game loans, rentals, and waitlists for associations and ludotheques.

== Description ==

BoardGame Loans is an easy-to-use WordPress plugin tailored specifically for board game associations, ludotheques, and non-profit gaming groups. It allows administrators to perfectly track game rentals, borrowing periods, overdue returns, and waitlists directly from the WordPress back-office.

Features:
* Check-in / Check-out board games to registered users or external guests.
* Set expected return dates automatically or manually.
* Track multiple identical physical copies using "Copy Number" and "Internal Code".
* Integrated Waitlist system to reserve games (with specific Status logic to queue borrowers).
* Detailed Admin Dashboard with search, filters, and color-coded statuses (Open, Returned, Overdue, Waitlist/Ready for pickup).
* Extend loans with a single click.
* Custom shortcodes `[bg_loans_list]` and `[bg_loans_waitlist]` to showcase active loans and queue on any front-end page with dynamic column settings.
* Full WordPress i18n support, ready to be localized in any language (Italian translation .po included!).

Perfect for small-to-medium libraries that need a clean and fast way to know who has which box!

== Installation ==

1. Upload the `boardgame-loans` directory to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the 'BoardGame Loans' menu in your admin dashboard to configure Settings and start registering loans.
4. Place the `[bg_loans_list]` shortcode on any public page to display the current rentals.

== Frequently Asked Questions ==

= Does it integrate with TablePress or BGG? =

Yes, you can enable "Advanced Mode" in the Settings to reveal a Reference field specifically designed to link your physical games to a main catalogue (like a TablePress list ID or a BGG reference).

= Is the waitlist automatic? =

When returning a game, the plugin checks if there is someone in the Waitlist for that specific game. If there is, it warns the administrator with a visual alert so you can manually put the game "Under the counter" and call the next person in line.

== Screenshots ==

1. screenshot-1.png == Admin Dashboard viewing all Active and Waitlisted Loans.
2. screenshot-2.png == Adding a new loan with Borrower details and Dates.
3. screenshot-3.png == The Public Shortcode table showing current borrowed games.

== Changelog ==

= 1.0.2 =
* Fix: mobile layout bug where the table header was overlapping data rows in the admin loans list due to WordPress admin responsive CSS being applied to the table.

= 1.0.1 =
* Fix: mobile layout bug where the table header was overlapping data rows in the `[bg_loans_list]` shortcode due to theme CSS stacking styles being applied to the table.

= 1.0.0 =
* Initial public release! Features complete loan management, UI improvements, shortcodes, and waitlist tracking.
