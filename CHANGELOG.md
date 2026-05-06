# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2026-05-06

### Security
- **SQL Hardening:** Implemented strict allow-lists for `ORDER BY` clauses in admin and public views.
- **Database Integrity:** Enforced `$wpdb->prepare()` for all queries, even when using validated identifiers or internal strings.
- **Explicit Formatting:** Added mandatory format arrays (`%s`, `%d`) to all `$wpdb->insert()` and `$wpdb->update()` calls.
- **Defense-in-Depth:** Added write-time allowlist validation in the settings save handler for `default_orderby` and `default_order`, preventing invalid values from ever being stored in the database (in addition to the existing read-time validation before SQL use).

### Standards
- **PHPCS Justification:** Added detailed technical explanations to all `// phpcs:ignore` blocks to satisfy repository security audits.
- **Sanitize ASAP:** Refactored settings handlers to ensure all `$_POST` and `$_GET` data is sanitized immediately upon retrieval.

## [1.0.4] - 2026-05-03

### Security
- Hardened all user input handling with `wp_unslash` and improved sanitization.
- Added nonce verification to all administrative forms (settings and loans).
- Improved SQL query safety using `wpdb::prepare()`, placeholders, and explicit suppression for false-positives.

### Standards
- Implemented full variable prefixing in all template files.
- Renamed global functions for better compliance with WordPress coding standards.

### Fixed
- Updated asset versioning to force browser cache refresh.

## [1.0.0] - 2026-03-20

### Added
- **Initial public release** of BoardGame Loans.
- **Loan Management:** Complete backend UI (add, edit, renew, return, delete).
- **Waitlist System:** Track games in queue, with `Waitlisted` and `Available for Pickup` status logic.
- **Copy Tracking:** Identify specific physical copies using internal codes and copy numbers (1-10). 
- **Shortcodes:** `[bg_loans_list]` and `[bg_loans_waitlist]` to dynamically display data on frontend pages.
- **Visual Feedback:** Alerts and styling for Overdue items and Waitlisted games currently returning to the library.
- **Settings Page:** Customizable UI. Toggle Advanced forms, Waitlist logic, date formats, and sorting preferences.
- **Integrations:** Full compatibility with TablePress IDs for advanced catalog linking.
- **Internationalization (i18n):** Full support for translation, including a complete Italian `.po` dictionary bundle.
