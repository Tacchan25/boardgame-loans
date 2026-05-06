<?php
// admin/partials/boardgame-loans-admin-display-settings.php

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- POST actions are nonce-verified inside the condition.
$bg_loans_settings_action = isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';
if ($bg_loans_settings_action === 'save_bg_loans_settings') {
    if (!isset($_POST['bg_loans_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bg_loans_settings_nonce'])), 'save_bg_loans_settings_action')) {
        wp_die(esc_html__('Permission denied.', 'boardgame-loans'));
    }

    update_option('bg_loans_form_mode', isset($_POST['form_mode']) ? sanitize_text_field(wp_unslash($_POST['form_mode'])) : 'simple');

    // Allowlist validation at write-time: prevents storing invalid column/direction values
    // that could later be interpolated into ORDER BY clauses (defense-in-depth).
    $bg_loans_allowed_save_orderby = ['id', 'loan_date', 'due_date', 'return_date', 'status'];
    $bg_loans_raw_save_orderby     = isset($_POST['default_orderby']) ? strtolower(sanitize_text_field(wp_unslash($_POST['default_orderby']))) : 'status';
    update_option('bg_loans_default_orderby', in_array($bg_loans_raw_save_orderby, $bg_loans_allowed_save_orderby, true) ? $bg_loans_raw_save_orderby : 'status');

    $bg_loans_raw_save_order = isset($_POST['default_order']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['default_order']))) : 'DESC';
    update_option('bg_loans_default_order', $bg_loans_raw_save_order === 'ASC' ? 'ASC' : 'DESC');
    update_option('bg_loans_date_format', isset($_POST['date_format']) ? sanitize_text_field(wp_unslash($_POST['date_format'])) : 'eu');
    
    if (isset($_POST['tablepress_id'])) {
        update_option('bg_loans_tablepress_id', sanitize_text_field(wp_unslash($_POST['tablepress_id'])));
    }
    if (isset($_POST['tablepress_col'])) {
        update_option('bg_loans_tablepress_col', sanitize_text_field(wp_unslash($_POST['tablepress_col'])));
    }
    if (isset($_POST['tablepress_col_id'])) {
        update_option('bg_loans_tablepress_col_id', sanitize_text_field(wp_unslash($_POST['tablepress_col_id'])));
    }
    if (isset($_POST['tablepress_col_year'])) {
        update_option('bg_loans_tablepress_col_year', sanitize_text_field(wp_unslash($_POST['tablepress_col_year'])));
    }

    if (isset($_POST['default_duration'])) {
        update_option('bg_loans_default_duration', intval(wp_unslash($_POST['default_duration'])));
    }
    if (isset($_POST['extend_days'])) {
        update_option('bg_loans_extend_days', intval(wp_unslash($_POST['extend_days'])));
    }
    if (isset($_POST['enable_copy_number'])) {
        update_option('bg_loans_enable_copy_number', sanitize_text_field(wp_unslash($_POST['enable_copy_number'])));
    }
    if (isset($_POST['enable_waitlist'])) {
        update_option('bg_loans_enable_waitlist', sanitize_text_field(wp_unslash($_POST['enable_waitlist'])));
    }
    if (isset($_POST['waitlist_unique'])) {
        update_option('bg_loans_waitlist_unique', sanitize_text_field(wp_unslash($_POST['waitlist_unique'])));
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'boardgame-loans') . '</p></div>';
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$bg_loans_form_mode           = get_option('bg_loans_form_mode', 'simple');
$bg_loans_default_orderby      = get_option('bg_loans_default_orderby', 'status');
$bg_loans_default_order        = get_option('bg_loans_default_order', 'DESC');
$bg_loans_date_format          = get_option('bg_loans_date_format', 'eu');
$bg_loans_tablepress_id        = get_option('bg_loans_tablepress_id', '');
$bg_loans_tablepress_col       = get_option('bg_loans_tablepress_col', '');
$bg_loans_tablepress_col_id    = get_option('bg_loans_tablepress_col_id', '');
$bg_loans_tablepress_col_year  = get_option('bg_loans_tablepress_col_year', '');
$bg_loans_default_duration     = get_option('bg_loans_default_duration', 7);
$bg_loans_extend_days          = get_option('bg_loans_extend_days', 7);
$bg_loans_enable_copy_number   = get_option('bg_loans_enable_copy_number', 'false');
$bg_loans_enable_waitlist      = get_option('bg_loans_enable_waitlist', 'false');
$bg_loans_waitlist_unique       = get_option('bg_loans_waitlist_unique', 'title_copy');
?>
<div class="wrap">
    <h1><?php esc_html_e('BoardGame Loans Settings', 'boardgame-loans'); ?></h1>

    <h2 class="nav-tab-wrapper" id="bg-loans-settings-tabs">
        <a href="#" data-tab="general" class="nav-tab nav-tab-active"><?php esc_html_e('General', 'boardgame-loans'); ?></a>
        <a href="#" data-tab="tablepress" class="nav-tab"><?php esc_html_e('TablePress', 'boardgame-loans'); ?></a>
        <a href="#" data-tab="loans" class="nav-tab"><?php esc_html_e('Loans Defaults', 'boardgame-loans'); ?></a>
        <a href="#" data-tab="waitlist" class="nav-tab"><?php esc_html_e('Waitlist', 'boardgame-loans'); ?></a>
    </h2>

    <form method="post" action="">
        <?php wp_nonce_field('save_bg_loans_settings_action', 'bg_loans_settings_nonce'); ?>
        <input type="hidden" name="action" value="save_bg_loans_settings">

        <div id="tab-general" class="bg-loans-tab-content">
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Form Mode', 'boardgame-loans'); ?></th>
                <td>
                    <select name="form_mode">
                        <option value="simple" <?php selected($bg_loans_form_mode, 'simple'); ?>><?php esc_html_e('Simple', 'boardgame-loans'); ?></option>
                        <option value="advanced" <?php selected($bg_loans_form_mode, 'advanced'); ?>><?php esc_html_e('Advanced (with TablePress source)', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Date Format', 'boardgame-loans'); ?></th>
                <td>
                    <select name="date_format">
                        <option value="eu" <?php selected($bg_loans_date_format, 'eu'); ?>><?php esc_html_e('European (dd/mm/yyyy)', 'boardgame-loans'); ?></option>
                        <option value="us" <?php selected($bg_loans_date_format, 'us'); ?>><?php esc_html_e('USA (yyyy-mm-dd)', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        </div>
        
        <div id="tab-tablepress" class="bg-loans-tab-content" style="display:none;">
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('TablePress Table ID', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_id" value="<?php echo esc_attr($bg_loans_tablepress_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('ID of the TablePress table holding games (e.g. 2).', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Game Title Column', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_col" value="<?php echo esc_attr($bg_loans_tablepress_col); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Exact Name of the column containing the boardgame title (e.g. "Title").', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Game Year Column (Optional)', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_col_year" value="<?php echo esc_attr($bg_loans_tablepress_col_year); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Exact Name of the column containing the boardgame release year.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Game ID/Code Column (Optional)', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_col_id" value="<?php echo esc_attr($bg_loans_tablepress_col_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Exact Name of the column containing the unique ID or Code. If left empty, the row number or first column will be used.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
        </table>
        </div>
        
        <div id="tab-loans" class="bg-loans-tab-content" style="display:none;">
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('List Default Order By', 'boardgame-loans'); ?></th>
                <td>
                    <select name="default_orderby">
                        <option value="status" <?php selected($bg_loans_default_orderby, 'status'); ?>><?php esc_html_e('Status', 'boardgame-loans'); ?></option>
                        <option value="loan_date" <?php selected($bg_loans_default_orderby, 'loan_date'); ?>><?php esc_html_e('Loan Date', 'boardgame-loans'); ?></option>
                        <option value="due_date" <?php selected($bg_loans_default_orderby, 'due_date'); ?>><?php esc_html_e('Due Date', 'boardgame-loans'); ?></option>
                        <option value="id" <?php selected($bg_loans_default_orderby, 'id'); ?>><?php esc_html_e('ID', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('List Default Order Direction', 'boardgame-loans'); ?></th>
                <td>
                    <select name="default_order">
                        <option value="DESC" <?php selected($bg_loans_default_order, 'DESC'); ?>><?php esc_html_e('Descending', 'boardgame-loans'); ?></option>
                        <option value="ASC" <?php selected($bg_loans_default_order, 'ASC'); ?>><?php esc_html_e('Ascending', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Default Loan Duration (Days)', 'boardgame-loans'); ?></th>
                <td>
                    <input type="number" name="default_duration" value="<?php echo esc_attr($bg_loans_default_duration); ?>" class="small-text" min="1">
                    <p class="description"><?php esc_html_e('Number of days a loan defaults to when created or issued from waitlist.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Extend Loan Days', 'boardgame-loans'); ?></th>
                <td>
                    <input type="number" name="extend_days" value="<?php echo esc_attr($bg_loans_extend_days); ?>" class="small-text" min="1">
                    <p class="description"><?php esc_html_e('Number of days to automatically add to the due date when using the "Extend" button.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Enable Copy Number', 'boardgame-loans'); ?></th>
                <td>
                    <select name="enable_copy_number">
                        <option value="false" <?php selected($bg_loans_enable_copy_number, 'false'); ?>><?php esc_html_e('No (Hidden)', 'boardgame-loans'); ?></option>
                        <option value="true" <?php selected($bg_loans_enable_copy_number, 'true'); ?>><?php esc_html_e('Yes (Show 1-10 dropdown)', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Useful for tracking multiple identical copies of the same game.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
        </table>
        </div>
        
        <div id="tab-waitlist" class="bg-loans-tab-content" style="display:none;">
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Enable Waitlist Logic', 'boardgame-loans'); ?></th>
                <td>
                    <select name="enable_waitlist">
                        <option value="false" <?php selected($bg_loans_enable_waitlist, 'false'); ?>><?php esc_html_e('No (Disabled)', 'boardgame-loans'); ?></option>
                        <option value="true" <?php selected($bg_loans_enable_waitlist, 'true'); ?>><?php esc_html_e('Yes (Enabled)', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Turns the Waitlist complex logics and Status options ON or OFF.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Waitlist Uniqueness Mode', 'boardgame-loans'); ?></th>
                <td>
                    <select name="waitlist_unique">
                        <option value="title_copy" <?php selected($bg_loans_waitlist_unique, 'title_copy'); ?>><?php esc_html_e('Game Title + Copy Number', 'boardgame-loans'); ?></option>
                        <option value="internal_code" <?php selected($bg_loans_waitlist_unique, 'internal_code'); ?>><?php esc_html_e('Internal Code', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('How the system identifies a unique physical box for waitlist tracking.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
        </table>
        </div>
        
        <?php submit_button(__('Save Settings', 'boardgame-loans')); ?>
    </form>
</div>

