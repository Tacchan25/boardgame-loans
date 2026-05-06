<?php
// admin/partials/boardgame-loans-admin-display-list.php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$bg_loans_table_name = $wpdb->prefix . 'bg_loans';

$bg_loans_date_format_setting = get_option('bg_loans_date_format', 'eu');
$bg_loans_date_format_str = $bg_loans_date_format_setting === 'us' ? 'Y-m-d' : 'd/m/Y';

$bg_loans_allowed_orderby = ['id', 'loan_date', 'due_date', 'return_date', 'status'];

// Validate default ordering settings with allowlist
$bg_loans_default_orderby_raw = get_option('bg_loans_default_orderby', 'status');
$bg_loans_default_orderby_setting = in_array(strtolower(sanitize_text_field(wp_unslash($bg_loans_default_orderby_raw))), $bg_loans_allowed_orderby, true) ? strtolower(sanitize_text_field(wp_unslash($bg_loans_default_orderby_raw))) : 'status';

$bg_loans_default_order_raw = get_option('bg_loans_default_order', 'DESC');
$bg_loans_default_order_setting = strtoupper(sanitize_text_field(wp_unslash($bg_loans_default_order_raw))) === 'ASC' ? 'ASC' : 'DESC';

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$bg_loans_orderby    = isset($_GET['orderby']) && in_array(strtolower(sanitize_text_field(wp_unslash($_GET['orderby']))), $bg_loans_allowed_orderby, true) ? strtolower(sanitize_text_field(wp_unslash($_GET['orderby']))) : '';
$bg_loans_order      = isset($_GET['order']) && strtoupper(sanitize_text_field(wp_unslash($_GET['order']))) === 'ASC' ? 'ASC' : 'DESC';
$bg_loans_search_game = isset($_REQUEST['search_game']) ? sanitize_text_field(wp_unslash($_REQUEST['search_game'])) : '';
$bg_loans_search_user = isset($_REQUEST['search_user']) ? sanitize_text_field(wp_unslash($_REQUEST['search_user'])) : '';
$bg_loans_filter_status = isset($_REQUEST['filter_status']) ? sanitize_text_field(wp_unslash($_REQUEST['filter_status'])) : '';
$bg_loans_message       = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';
$bg_loans_borrower      = isset($_GET['borrower']) ? sanitize_text_field(wp_unslash($_GET['borrower'])) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$bg_loans_next_order          = $bg_loans_order === 'DESC' ? 'asc' : 'desc';
$bg_loans_extend_days_setting = intval(get_option('bg_loans_extend_days', 7));
$bg_loans_enable_copy_number  = get_option('bg_loans_enable_copy_number', 'false');

if ($bg_loans_orderby) {
    if ($bg_loans_orderby === 'status') {
         $bg_loans_order_clause = "ORDER BY FIELD(status, 'open', 'closed') {$bg_loans_order}, loan_date DESC";
    } else {
         $bg_loans_order_clause = "ORDER BY {$bg_loans_orderby} {$bg_loans_order}";
    }
} else {
    // Default sort based on settings
    if ($bg_loans_default_orderby_setting === 'status') {
    // Status ordering uses a fixed FIELD order, then applies the validated default direction
    $bg_loans_order_clause = "ORDER BY FIELD(status, 'open', 'closed'), loan_date {$bg_loans_default_order_setting}, id {$bg_loans_default_order_setting}";
} else {
    // Safe ORDER BY using validated column and direction
    $bg_loans_order_clause = "ORDER BY {$bg_loans_default_orderby_setting} {$bg_loans_default_order_setting}";
}
}

$bg_loans_where_clauses = array();
$bg_loans_where_values  = array();

if (!empty($bg_loans_search_game)) {
    $bg_loans_where_clauses[] = "(game_title LIKE %s OR internal_code LIKE %s)";
    $bg_loans_like            = '%' . $wpdb->esc_like($bg_loans_search_game) . '%';
    $bg_loans_where_values[]  = $bg_loans_like;
    $bg_loans_where_values[]  = $bg_loans_like;
}

if (!empty($bg_loans_search_user)) {
    $bg_loans_where_clauses[] = "(borrower_name LIKE %s)";
    $bg_loans_like            = '%' . $wpdb->esc_like($bg_loans_search_user) . '%';
    $bg_loans_where_values[]  = $bg_loans_like;
}

if (!empty($bg_loans_filter_status) && $bg_loans_filter_status !== 'all') {
    if ($bg_loans_filter_status === 'overdue') {
        $bg_loans_where_clauses[] = "status = 'open'";
        $bg_loans_where_clauses[] = "due_date < %s";
        $bg_loans_where_values[]  = gmdate('Y-m-d 00:00:00');
    } elseif ($bg_loans_filter_status === 'queue') {
        $bg_loans_where_clauses[] = "status IN ('waitlist', 'available')";
    } else {
        $bg_loans_where_clauses[] = "status = %s";
        $bg_loans_where_values[]  = $bg_loans_filter_status;
    }
}

$bg_loans_where_sql = "";
// Prepare and execute query with safe ORDER BY clause (validated against allowlist)
if (!empty($bg_loans_where_clauses)) {
    $bg_loans_where_sql = "WHERE " . implode(' AND ', $bg_loans_where_clauses);
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Query structure and ORDER BY clause are safe. ORDER BY uses validated column/direction.
    $bg_loans_query = $wpdb->prepare("SELECT * FROM {$bg_loans_table_name} {$bg_loans_where_sql} {$bg_loans_order_clause}", $bg_loans_where_values);
    $bg_loans_items = $wpdb->get_results($bg_loans_query);
} else {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- ORDER BY clause built from validated settings. Using prepare for compliance.
    $bg_loans_query = $wpdb->prepare("SELECT * FROM {$bg_loans_table_name} {$bg_loans_order_clause}", array());
    $bg_loans_items = $wpdb->get_results($bg_loans_query);
}

$bg_loans_base_url = admin_url('admin.php?page=boardgame-loans');
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Loans List', 'boardgame-loans'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new')); ?>" class="page-title-action"><?php esc_html_e('New Loan', 'boardgame-loans'); ?></a>
    <hr class="wp-header-end">

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="boardgame-loans">
        <input type="text" name="search_game" placeholder="<?php esc_attr_e('Search game or code...', 'boardgame-loans'); ?>" value="<?php echo esc_attr($bg_loans_search_game); ?>">
        <input type="text" name="search_user" placeholder="<?php esc_attr_e('Search borrower...', 'boardgame-loans'); ?>" value="<?php echo esc_attr($bg_loans_search_user); ?>">
        <select name="filter_status">
            <option value="all" <?php selected($bg_loans_filter_status, 'all'); ?>><?php esc_html_e('All Statuses', 'boardgame-loans'); ?></option>
            <option value="open" <?php selected($bg_loans_filter_status, 'open'); ?>><?php esc_html_e('Open', 'boardgame-loans'); ?></option>
            <option value="closed" <?php selected($bg_loans_filter_status, 'closed'); ?>><?php esc_html_e('Returned', 'boardgame-loans'); ?></option>
            <option value="overdue" <?php selected($bg_loans_filter_status, 'overdue'); ?>><?php esc_html_e('Overdue', 'boardgame-loans'); ?></option>
            <option value="queue" <?php selected($bg_loans_filter_status, 'queue'); ?>><?php esc_html_e('Waitlist/Queue', 'boardgame-loans'); ?></option>
        </select>
        <?php submit_button(__('Filter', 'boardgame-loans'), 'button', '', false); ?>
        <a href="<?php echo esc_url($bg_loans_base_url); ?>" class="button"><?php esc_html_e('Reset', 'boardgame-loans'); ?></a>
    </form>

    <?php if ($bg_loans_message): ?>
        <?php if ($bg_loans_message === 'extended'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan extended successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'waitlist_triggered'): ?>
            <div class="notice notice-error is-dismissible" style="padding: 15px; border-left-color: #d63638; background: #fcf0f1;">
                <p><strong style="font-size: 16px;">⚠️ <?php esc_html_e('ATTENTION: The returned game is WAITLISTED!', 'boardgame-loans'); ?></strong></p>
                <p><?php 
                /* translators: %s: borrower name */
                printf(esc_html__('This game must be kept safely under the counter. It is reserved for: %s.', 'boardgame-loans'), '<strong>' . esc_html($bg_loans_borrower) . '</strong>'); 
                ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'issued'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan successfully issued to waitlisted borrower!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'deleted'): ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php esc_html_e('Loan record entirely deleted.', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'closed'): ?>
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e('Loan closed and marked as returned!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('New loan saved successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($bg_loans_message === 'updated'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan updated successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-loans-admin-table-wrap">
        <table class="wp-list-table widefat fixed striped" style="border: none; margin: 0;">
        <thead>
            <tr>
                <?php
                $bg_loans_headers = [
                    'id' => __('ID', 'boardgame-loans'),
                    'game_title' => __('Game', 'boardgame-loans'),
                    'borrower' => __('Borrower', 'boardgame-loans'),
                    'loan_date' => __('Loan Date', 'boardgame-loans'),
                    'due_date' => __('Due Date', 'boardgame-loans'),
                    'return_date' => __('Return Date', 'boardgame-loans'),
                    'status' => __('Status', 'boardgame-loans'),
                ];
                foreach ($bg_loans_headers as $bg_loans_col => $bg_loans_label) {
                    $bg_loans_style = '';
                    if ($bg_loans_col === 'id') $bg_loans_style = 'width: 60px;';
                    if ($bg_loans_col === 'game_title') $bg_loans_style = 'width: 30%;';
                    if ($bg_loans_col === 'status') $bg_loans_style = 'width: 80px; text-align: center;';

                    if (in_array($bg_loans_col, $bg_loans_allowed_orderby, true)) {
                        $bg_loans_sort_order = ($bg_loans_orderby === $bg_loans_col) ? $bg_loans_next_order : 'desc';
                        $bg_loans_url = esc_url(add_query_arg(['orderby' => $bg_loans_col, 'order' => $bg_loans_sort_order], $bg_loans_base_url));
                        $bg_loans_icon = '';
                        if ($bg_loans_orderby === $bg_loans_col) {
                            $bg_loans_icon = $bg_loans_order === 'ASC' ? ' &uarr;' : ' &darr;';
                        }
                        ?>
                        <th style="<?php echo esc_attr($bg_loans_style); ?>"><a href="<?php echo esc_url($bg_loans_url); ?>"><?php echo esc_html($bg_loans_label) . wp_kses_post($bg_loans_icon); ?></a></th>
                        <?php
                    } else {
                        ?>
                        <th style="<?php echo esc_attr($bg_loans_style); ?>"><?php echo esc_html($bg_loans_label); ?></th>
                        <?php
                    }
                }
                ?>
                <th style="width: 210px;"><?php esc_html_e('Actions', 'boardgame-loans'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bg_loans_items): ?>
                <?php foreach ($bg_loans_items as $bg_loans_item): ?>
                    <tr>
                        <td data-colname="<?php esc_attr_e('ID', 'boardgame-loans'); ?>"><?php echo esc_html($bg_loans_item->id); ?></td>
                        <td data-colname="<?php esc_attr_e('Game', 'boardgame-loans'); ?>">
                            <strong><?php 
                                echo esc_html($bg_loans_item->game_title); 
                                if ($bg_loans_enable_copy_number === 'true' && isset($bg_loans_item->copy_number) && $bg_loans_item->copy_number > 1) {
                                    /* translators: %d: copy number */
                                    echo esc_html(' (' . sprintf(__('Copy n. %d', 'boardgame-loans'), $bg_loans_item->copy_number) . ')');
                                }
                            ?></strong><br>
                        </td>
                        <td data-colname="<?php esc_attr_e('Borrower', 'boardgame-loans'); ?>">
                            <?php
                            if ($bg_loans_item->borrower_type === 'user' && $bg_loans_item->borrower_user_id) {
                                $bg_loans_user_info = get_userdata($bg_loans_item->borrower_user_id);
                                echo esc_html($bg_loans_user_info ? $bg_loans_user_info->display_name : 'User ID: ' . $bg_loans_item->borrower_user_id);
                            } else {
                                echo esc_html($bg_loans_item->borrower_name);
                            }
                            ?>
                        </td>
                        <td data-colname="<?php esc_attr_e('Loan Date', 'boardgame-loans'); ?>"><?php echo ($bg_loans_item->status === 'waitlist') ? '-' : esc_html(date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->loan_date))); ?></td>
                        <td data-colname="<?php esc_attr_e('Due Date', 'boardgame-loans'); ?>"><?php echo ($bg_loans_item->status === 'waitlist' || empty($bg_loans_item->due_date)) ? '-' : esc_html(date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->due_date))); ?></td>
                        <td data-colname="<?php esc_attr_e('Return Date', 'boardgame-loans'); ?>"><?php echo $bg_loans_item->return_date ? esc_html(date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->return_date))) : '-'; ?></td>
                        <td data-colname="<?php esc_attr_e('Status', 'boardgame-loans'); ?>" style="text-align: center;">
                            <?php if ($bg_loans_item->status === 'closed'): ?>
                                <span class="dashicons dashicons-saved" style="color: #46b450;" title="<?php esc_attr_e('Returned', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($bg_loans_item->status === 'available'): ?>
                                <span class="dashicons dashicons-bell" style="color: #2271b1;" title="<?php esc_attr_e('Available for Pickup', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($bg_loans_item->status === 'waitlist'): ?>
                                <span class="dashicons dashicons-hourglass" style="color: #8c8f94;" title="<?php esc_attr_e('Waitlisted', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($bg_loans_item->status === 'open' && $bg_loans_item->due_date && strtotime(gmdate('Y-m-d', strtotime($bg_loans_item->due_date)) . ' 23:59:59') < time()): ?>
                                <span class="dashicons dashicons-warning" style="color: #d63638;" title="<?php esc_attr_e('Overdue', 'boardgame-loans'); ?>"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-update-alt" style="color: #dba617;" title="<?php esc_attr_e('In progress', 'boardgame-loans'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td data-colname="" style="white-space: nowrap;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new&loan_id=' . $bg_loans_item->id)); ?>" class="button button-small" title="<?php esc_attr_e('Edit', 'boardgame-loans'); ?>"><span class="dashicons dashicons-edit" style="line-height: 1.5;"></span></a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new&action=copy_loan&loan_id=' . $bg_loans_item->id)); ?>" class="button button-small" title="<?php esc_attr_e('Copy', 'boardgame-loans'); ?>"><span class="dashicons dashicons-admin-page" style="line-height: 1.5;"></span></a>
                            <?php if ($bg_loans_item->status === 'available'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=issue_loan&loan_id=' . $bg_loans_item->id), 'issue_loan_' . $bg_loans_item->id)); ?>" class="button button-small" title="<?php esc_attr_e('Issue Loan', 'boardgame-loans'); ?>" style="border-color: #2271b1; color: #2271b1;"><span class="dashicons dashicons-yes" style="line-height: 1.5;"></span></a>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=close_loan&loan_id=' . $bg_loans_item->id), 'close_loan_' . $bg_loans_item->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to cancel this waitlisted item and return it to stock?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Cancel', 'boardgame-loans'); ?>"><span class="dashicons dashicons-no" style="line-height: 1.5; color: #d63638;"></span></a>
                            <?php elseif ($bg_loans_item->status === 'open'): ?>
                                <?php /* translators: %d: extension days */ ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=extend_loan&loan_id=' . $bg_loans_item->id), 'extend_loan_' . $bg_loans_item->id)); ?>" class="button button-small" title="<?php echo esc_attr(sprintf(__('Extend by %d days', 'boardgame-loans'), $bg_loans_extend_days_setting)); ?>"><span class="dashicons dashicons-clock" style="line-height: 1.5;"></span></a>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=close_loan&loan_id=' . $bg_loans_item->id), 'close_loan_' . $bg_loans_item->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to mark this loan as returned?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Close', 'boardgame-loans'); ?>"><span class="dashicons dashicons-yes" style="line-height: 1.5; color: #46b450;"></span></a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=delete_loan&loan_id=' . $bg_loans_item->id), 'delete_loan_' . $bg_loans_item->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to hard delete this record?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Delete', 'boardgame-loans'); ?>"><span class="dashicons dashicons-trash" style="line-height: 1.5; color: #d63638;"></span></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td data-colname="" colspan="7" style="justify-content: center !important;"><?php esc_html_e('No active loans at the moment.', 'boardgame-loans'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
