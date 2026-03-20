<?php
// admin/partials/boardgame-loans-admin-display-settings.php

if (!defined('ABSPATH')) {
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'save_bg_loans_settings') {
    check_admin_referer('bg_loans_save_settings');
    update_option('bg_loans_form_mode', sanitize_text_field($_POST['form_mode']));
    update_option('bg_loans_default_orderby', sanitize_text_field($_POST['default_orderby']));
    update_option('bg_loans_default_order', sanitize_text_field($_POST['default_order']));
    update_option('bg_loans_date_format', sanitize_text_field($_POST['date_format']));
    update_option('bg_loans_tablepress_id', sanitize_text_field($_POST['tablepress_id']));
    update_option('bg_loans_tablepress_col', sanitize_text_field($_POST['tablepress_col']));
    update_option('bg_loans_extend_days', intval($_POST['extend_days']));
    update_option('bg_loans_enable_copy_number', sanitize_text_field($_POST['enable_copy_number']));
    update_option('bg_loans_enable_waitlist', sanitize_text_field($_POST['enable_waitlist']));
    update_option('bg_loans_waitlist_unique', sanitize_text_field($_POST['waitlist_unique']));
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'boardgame-loans') . '</p></div>';
}

$form_mode = get_option('bg_loans_form_mode', 'advanced');
$default_orderby = get_option('bg_loans_default_orderby', 'status');
$default_order = get_option('bg_loans_default_order', 'DESC');
$date_format = get_option('bg_loans_date_format', 'eu');
$tablepress_id = get_option('bg_loans_tablepress_id', '');
$tablepress_col = get_option('bg_loans_tablepress_col', 'Nome gioco');
$extend_days = get_option('bg_loans_extend_days', 7);
$enable_copy_number = get_option('bg_loans_enable_copy_number', 'false');
$enable_waitlist = get_option('bg_loans_enable_waitlist', 'true');
$waitlist_unique = get_option('bg_loans_waitlist_unique', 'title_copy');
?>
<div class="wrap">
    <h1><?php esc_html_e('Settings', 'boardgame-loans'); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('bg_loans_save_settings'); ?>
        <input type="hidden" name="action" value="save_bg_loans_settings">
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Loan Form Mode', 'boardgame-loans'); ?></th>
                <td>
                    <select name="form_mode">
                        <option value="simple" <?php selected($form_mode, 'simple'); ?>><?php esc_html_e('Simple Mode (Title, Code, Name, Dates, Notes)', 'boardgame-loans'); ?></option>
                        <option value="advanced" <?php selected($form_mode, 'advanced'); ?>><?php esc_html_e('Advanced Mode (BGG/TablePress reference, User linkage, etc)', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('List Default Order By', 'boardgame-loans'); ?></th>
                <td>
                    <select name="default_orderby">
                        <option value="status" <?php selected($default_orderby, 'status'); ?>><?php esc_html_e('Status', 'boardgame-loans'); ?></option>
                        <option value="loan_date" <?php selected($default_orderby, 'loan_date'); ?>><?php esc_html_e('Loan Date', 'boardgame-loans'); ?></option>
                        <option value="due_date" <?php selected($default_orderby, 'due_date'); ?>><?php esc_html_e('Due Date', 'boardgame-loans'); ?></option>
                        <option value="id" <?php selected($default_orderby, 'id'); ?>><?php esc_html_e('ID', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('List Default Order Direction', 'boardgame-loans'); ?></th>
                <td>
                    <select name="default_order">
                        <option value="DESC" <?php selected($default_order, 'DESC'); ?>><?php esc_html_e('Descending', 'boardgame-loans'); ?></option>
                        <option value="ASC" <?php selected($default_order, 'ASC'); ?>><?php esc_html_e('Ascending', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Date display format', 'boardgame-loans'); ?></th>
                <td>
                    <select name="date_format">
                        <option value="eu" <?php selected($date_format, 'eu'); ?>><?php esc_html_e('European (dd/mm/yyyy)', 'boardgame-loans'); ?></option>
                        <option value="us" <?php selected($date_format, 'us'); ?>><?php esc_html_e('USA (yyyy-mm-dd)', 'boardgame-loans'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('TablePress Table ID', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_id" value="<?php echo esc_attr($tablepress_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('ID of the TablePress table holding games (e.g. 2).', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('TablePress Game Title Column', 'boardgame-loans'); ?></th>
                <td>
                    <input type="text" name="tablepress_col" value="<?php echo esc_attr($tablepress_col); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Exact Name of the column containing the boardgame title.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Extend Loan Days', 'boardgame-loans'); ?></th>
                <td>
                    <input type="number" name="extend_days" value="<?php echo esc_attr($extend_days); ?>" class="small-text" min="1">
                    <p class="description"><?php esc_html_e('Number of days to automatically add to the due date when using the "Extend" button.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Enable Copy Number', 'boardgame-loans'); ?></th>
                <td>
                    <select name="enable_copy_number">
                        <option value="false" <?php selected($enable_copy_number, 'false'); ?>><?php esc_html_e('No (Hidden)', 'boardgame-loans'); ?></option>
                        <option value="true" <?php selected($enable_copy_number, 'true'); ?>><?php esc_html_e('Yes (Show 1-10 dropdown)', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Useful for tracking multiple identical copies of the same game.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Enable Waitlist Logic', 'boardgame-loans'); ?></th>
                <td>
                    <select name="enable_waitlist">
                        <option value="false" <?php selected($enable_waitlist, 'false'); ?>><?php esc_html_e('No (Disabled)', 'boardgame-loans'); ?></option>
                        <option value="true" <?php selected($enable_waitlist, 'true'); ?>><?php esc_html_e('Yes (Enabled)', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Turns the Waitlist complex logics and Status options ON or OFF.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Waitlist Uniqueness Mode', 'boardgame-loans'); ?></th>
                <td>
                    <select name="waitlist_unique">
                        <option value="title_copy" <?php selected($waitlist_unique, 'title_copy'); ?>><?php esc_html_e('Game Title + Copy Number', 'boardgame-loans'); ?></option>
                        <option value="internal_code" <?php selected($waitlist_unique, 'internal_code'); ?>><?php esc_html_e('Internal Code', 'boardgame-loans'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('How the system identifies a unique physical box for waitlist tracking.', 'boardgame-loans'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Save Settings', 'boardgame-loans')); ?>
    </form>
</div>
