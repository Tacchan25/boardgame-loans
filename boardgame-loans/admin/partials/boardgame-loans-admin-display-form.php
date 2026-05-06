<?php
// admin/partials/boardgame-loans-admin-display-form.php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$bg_loans_db_table = $wpdb->prefix . 'bg_loans';

$bg_loans_form_title = __('New Loan', 'boardgame-loans');
$bg_loans_mode       = get_option('bg_loans_form_mode', 'simple');

$bg_loans_editing = false;
$bg_loans_copying = false;
$bg_loans_data    = null;

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$bg_loans_id_to_load = !empty($_GET['loan_id']) ? intval(wp_unslash($_GET['loan_id'])) : 0;
$bg_loans_op         = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
$bg_loans_msg_code   = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

if ($bg_loans_id_to_load > 0) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is internal and safe, ID is prepared.
    $bg_loans_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$bg_loans_db_table} WHERE id = %d", $bg_loans_id_to_load));

    if ($bg_loans_op === 'copy_loan') {
        $bg_loans_copying = true;
        $bg_loans_form_title = __('Copy Loan', 'boardgame-loans');
    } else {
        $bg_loans_editing = true;
        $bg_loans_form_title = __('Edit Loan', 'boardgame-loans');
    }
}

// Default values
$bg_loans_today      = current_time('Y-m-d');
$bg_loans_duration_val = intval(get_option('bg_loans_default_duration', 7));
$bg_loans_due_default  = gmdate('Y-m-d', strtotime("+$bg_loans_duration_val days"));

$bg_loans_waitlist_enabled = get_option('bg_loans_enable_waitlist', 'false');
$bg_loans_copy_num_enabled = get_option('bg_loans_enable_copy_number', 'false');
?>

<div class="wrap">
    <h1><?php echo esc_html($bg_loans_form_title); ?></h1>
    
    <?php if ($bg_loans_msg_code): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ($bg_loans_msg_code === 'success') {
                    esc_html_e('Loan saved successfully!', 'boardgame-loans');
                } elseif ($bg_loans_msg_code === 'updated') {
                    esc_html_e('Loan updated successfully!', 'boardgame-loans');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new')); ?>" id="bg-loan-form">
        <input type="hidden" name="action" value="save_new_loan">
        <?php wp_nonce_field('save_new_loan_action', 'bg_loans_nonce'); ?>
        
        <?php if ($bg_loans_editing && $bg_loans_data): ?>
            <input type="hidden" name="loan_id" value="<?php echo esc_attr($bg_loans_data->id); ?>">
        <?php endif; ?>

        <div id="bg-loans-form-grid">
            <div class="bg-loans-card">
                <h2><span class="dashicons dashicons-products"></span> <?php esc_html_e('Game Information', 'boardgame-loans'); ?></h2>
                
                <?php if ($bg_loans_mode === 'advanced'): ?>
                    <div class="form-field">
                        <label><?php esc_html_e('Source', 'boardgame-loans'); ?></label>
                        <select name="game_source" id="game_source">
                            <option value="manual" <?php selected($bg_loans_data ? $bg_loans_data->game_source : 'manual', 'manual'); ?>><?php esc_html_e('Manual', 'boardgame-loans'); ?></option>
                            <option value="tablepress" <?php selected($bg_loans_data ? $bg_loans_data->game_source : 'manual', 'tablepress'); ?>>TablePress</option>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="game_source" value="manual">
                <?php endif; ?>

                <div class="form-field">
                    <label><?php esc_html_e('Title', 'boardgame-loans'); ?> <span class="required">*</span></label>
                    <input type="text" name="game_title" value="<?php echo esc_attr($bg_loans_data ? $bg_loans_data->game_title : ''); ?>" required>
                </div>

                <div class="form-field">
                    <label><?php esc_html_e('Code', 'boardgame-loans'); ?></label>
                    <input type="text" name="internal_code" value="<?php echo esc_attr($bg_loans_data ? $bg_loans_data->internal_code : ''); ?>">
                </div>

                <?php if ($bg_loans_copy_num_enabled === 'true'): ?>
                <div class="form-field">
                    <label><?php esc_html_e('Copy #', 'boardgame-loans'); ?></label>
                    <select name="copy_number">
                        <?php for ($bg_loans_idx = 1; $bg_loans_idx <= 20; $bg_loans_idx++): ?>
                            <option value="<?php echo esc_attr($bg_loans_idx); ?>" <?php selected($bg_loans_data ? $bg_loans_data->copy_number : 1, $bg_loans_idx); ?>><?php echo esc_html($bg_loans_idx); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-field">
                    <label><?php esc_html_e('Status', 'boardgame-loans'); ?></label>
                    <?php $bg_loans_current_status = ($bg_loans_data && !$bg_loans_copying) ? $bg_loans_data->status : 'open'; ?>
                    <select name="status">
                        <option value="open" <?php selected($bg_loans_current_status, 'open'); ?>><?php esc_html_e('Open', 'boardgame-loans'); ?></option>
                        <option value="closed" <?php selected($bg_loans_current_status, 'closed'); ?>><?php esc_html_e('Returned', 'boardgame-loans'); ?></option>
                        <?php if ($bg_loans_waitlist_enabled === 'true'): ?>
                            <option value="waitlist" <?php selected($bg_loans_current_status, 'waitlist'); ?>><?php esc_html_e('Waitlist', 'boardgame-loans'); ?></option>
                            <option value="available" <?php selected($bg_loans_current_status, 'available'); ?>><?php esc_html_e('Ready for pickup', 'boardgame-loans'); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="bg-loans-card">
                <h2><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e('Borrower Information', 'boardgame-loans'); ?></h2>
                
                <div class="form-field">
                    <label><?php esc_html_e('Name', 'boardgame-loans'); ?> <span class="required">*</span></label>
                    <input type="text" name="borrower_name" value="<?php echo esc_attr($bg_loans_data ? $bg_loans_data->borrower_name : ''); ?>" required>
                </div>

                <div class="form-field-row">
                    <div class="form-field">
                        <label><?php esc_html_e('Loan Date', 'boardgame-loans'); ?></label>
                        <?php $bg_loans_f_date = ($bg_loans_data && !$bg_loans_copying) ? gmdate('Y-m-d', strtotime($bg_loans_data->loan_date)) : $bg_loans_today; ?>
                        <input type="date" name="loan_date" value="<?php echo esc_attr($bg_loans_f_date); ?>" required>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Due Date', 'boardgame-loans'); ?></label>
                        <?php $bg_loans_f_due = ($bg_loans_data && $bg_loans_data->due_date && !$bg_loans_copying) ? gmdate('Y-m-d', strtotime($bg_loans_data->due_date)) : $bg_loans_due_default; ?>
                        <input type="date" name="due_date" value="<?php echo esc_attr($bg_loans_f_due); ?>">
                    </div>
                </div>

                <div class="form-field">
                    <label><?php esc_html_e('Notes', 'boardgame-loans'); ?></label>
                    <textarea name="notes" rows="3"><?php echo esc_textarea($bg_loans_data ? $bg_loans_data->notes : ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="bg-loans-submit-wrap">
            <?php submit_button($bg_loans_editing ? __('Update Loan', 'boardgame-loans') : __('Save Loan', 'boardgame-loans'), 'primary large', 'submit_new_loan', false); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans')); ?>" class="button button-large"><?php esc_html_e('Cancel', 'boardgame-loans'); ?></a>
        </div>
    </form>
</div>
