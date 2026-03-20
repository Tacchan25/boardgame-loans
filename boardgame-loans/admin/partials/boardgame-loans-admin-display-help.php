<?php
// admin/partials/boardgame-loans-admin-display-help.php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Shortcodes Documentation', 'boardgame-loans'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php esc_html_e('You can embed the plugin tables on any public page using Shortcodes.', 'boardgame-loans'); ?></p>
    </div>

    <div class="card" style="max-width: 100%; margin-top: 20px;">
        <h2><?php esc_html_e('How to add the table to a Page or Post', 'boardgame-loans'); ?></h2>
        <ol>
            <li><?php esc_html_e('Go to the Pages or Posts section of your WordPress and create a new page.', 'boardgame-loans'); ?></li>
            <li><?php esc_html_e('If you use the new Block Editor (Gutenberg), click the [+] button to add a new block.', 'boardgame-loans'); ?></li>
            <li><?php esc_html_e('Search for the block named "Shortcode" and click it to insert it into your page.', 'boardgame-loans'); ?></li>
            <li><?php esc_html_e('Inside the text field of the block that appears, write exactly one of the shortcodes explained below.', 'boardgame-loans'); ?></li>
            <li><?php esc_html_e('Save or Update the page. Done! The site will replace that block with the actual table.', 'boardgame-loans'); ?></li>
        </ol>
        <p><em><?php esc_html_e('Note: if you use the old Classic Editor, you can just paste the shortcode directly in the middle of the rest of the text.', 'boardgame-loans'); ?></em></p>
    </div>

    <!-- Active Loans Shortcode -->
    <div class="card" style="max-width: 100%; margin-top: 20px;">
        <h2><?php esc_html_e('1. Public Loans List', 'boardgame-loans'); ?></h2>
        <p><?php esc_html_e('To display the standard list of games currently borrowed by members, insert this shortcode in a page:', 'boardgame-loans'); ?></p>
        <p style="background:#f0f0f1; padding:10px; font-size:16px;"><code>[bg_loans_list]</code></p>
        
        <h4 style="margin-top:20px;"><?php esc_html_e('Optional Parameters for', 'boardgame-loans'); ?> <code>[bg_loans_list]</code></h4>
        <p><?php esc_html_e('By default, the table shows the game title and the expected return date. You can customize the columns by adding parameters, for example:', 'boardgame-loans'); ?></p>
        <p><code>[bg_loans_list show_borrower="true" show_status="true"]</code></p>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li><strong>show_borrower</strong>: <code>"true"</code> / <code>"false"</code> (<?php esc_html_e('Default: false', 'boardgame-loans'); ?>)</li>
            <li><strong>show_loan_date</strong>: <code>"true"</code> / <code>"false"</code> (<?php esc_html_e('Default: true', 'boardgame-loans'); ?>)</li>
            <li><strong>show_due_date</strong>: <code>"true"</code> / <code>"false"</code> (<?php esc_html_e('Default: true', 'boardgame-loans'); ?>)</li>
            <li><strong>show_return_date</strong>: <code>"true"</code> / <code>"false"</code> (<?php esc_html_e('Default: false', 'boardgame-loans'); ?>)</li>
            <li><strong>show_status</strong>: <code>"true"</code> / <code>"false"</code> (<?php esc_html_e('Default: false', 'boardgame-loans'); ?>)</li>
            <li><strong>status_filter</strong>: <code>"open"</code>, <code>"closed"</code>, <code>"all"</code> (<?php esc_html_e('Default: open', 'boardgame-loans'); ?>)</li>
        </ul>
    </div>

    <!-- Waitlist Queue Shortcode -->
    <div class="card" style="max-width: 100%; margin-top: 20px;">
        <h2><?php esc_html_e('2. Waitlist Queue Tab', 'boardgame-loans'); ?></h2>
        <p><?php esc_html_e('To display a dedicated page showing only the waitlist requests and the games ready for pickup, use this specific shortcode:', 'boardgame-loans'); ?></p>
        <p style="background:#f0f0f1; padding:10px; font-size:16px;"><code>[bg_loans_waitlist]</code></p>
        
        <p><?php esc_html_e('By default, this shortcode is pre-configured to only show the Game Title, the Borrower Name, and the Wait Status. Dates are hidden automatically because the games have not been issued yet.', 'boardgame-loans'); ?></p>
    </div>
</div>
