=== BoardGame Loans ===
Contributors: Tacchan25
Tags: boardgame, loans, tracking, associations
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Board game loan tracking management plugin for associations.

== Description ==

A fantastic plugin for game clubs, board game associations, or schools that need a core system to manage their board game inventory lending and tracking.
Allows manual data entry via the WordPress admin backend, recording the borrower and the expected return date.

Furthermore, the plugin exposes a flexible public Shortcode to show site visitors the actively loaned out games!

== Shortcode: bg_loans_list ==

You can setup a public view of your games by entering the `[bg_loans_list]` shortcode in any of your WordPress pages or posts.

= Available parameters =
(You can toggle every column display with "true" or "false")

* **status_filter**: Overrides the display status filter behavior ("open", "closed", "all"). (*Default: "open"*).
* **show_loan_date**: Displays when the loan has taken place. (*Default: "true"*).
* **show_due_date**: Displays the expected return date. Signals overdue loans with bold red dates. (*Default: "true"*).
* **show_borrower**: Displays the name of the user who borrowed the game. (*Default: "false"*).
* **show_return_date**: Displays the day the game was actually returned. (*Default: "false"*).
* **show_status**: Textual label indicating "In progress" or "Returned". (*Default: "false"*).
* **css_class**: Appends a custom CSS class to the HTML table wrapper for custom layout styling. (*Default: empty*)

= Example usage =
`[bg_loans_list show_borrower="true" show_return_date="true" status_filter="all"]`

== Installation ==

1. Upload the `boardgame-loans` folder to `/wp-content/plugins/`.
2. Activate the plugin via the WordPress 'Plugins' menu.
3. You will find the "BoardGame Loans" submenus straight into your WP Administration sidebar!

== Changelog ==

= 0.1.0 =
* Initial Release. Interfaces setup. Modular MVC-style codebase.
* MySQL queries setup with sortable table headers.
* Advanced public Shortcode integration.
* App settings module (Simple Mode forms, default styling orders, etc).
