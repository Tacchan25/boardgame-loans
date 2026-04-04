<?php
// admin/partials/boardgame-loans-admin-display-list.php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bg_loans';

$default_orderby_setting = get_option('bg_loans_default_orderby', 'status');
$default_order_setting = get_option('bg_loans_default_order', 'DESC');
$date_format_setting = get_option('bg_loans_date_format', 'eu');
$date_format_str = $date_format_setting === 'us' ? 'Y-m-d' : 'd/m/Y';

$allowed_orderby = ['id', 'loan_date', 'due_date', 'return_date', 'status'];
$orderby = isset($_GET['orderby']) && in_array(strtolower($_GET['orderby']), $allowed_orderby) ? strtolower($_GET['orderby']) : '';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
$next_order = $order === 'DESC' ? 'asc' : 'desc';
$extend_days_setting = intval(get_option('bg_loans_extend_days', 7));
$enable_copy_number = get_option('bg_loans_enable_copy_number', 'false');

if ($orderby) {
    if ($orderby === 'status') {
         $order_clause = "ORDER BY FIELD(status, 'open', 'closed') {$order}, loan_date DESC";
    } else {
         $order_clause = "ORDER BY {$orderby} {$order}";
    }
} else {
    // Default sort based on settings
    if ($default_orderby_setting === 'status') {
        $order_clause = "ORDER BY FIELD(status, 'open', 'closed'), loan_date {$default_order_setting}, id {$default_order_setting}";
    } else {
         $order_clause = "ORDER BY {$default_orderby_setting} {$default_order_setting}";
    }
}

// Search and Filter Logic
$search_game = isset($_REQUEST['search_game']) ? sanitize_text_field(stripslashes($_REQUEST['search_game'])) : '';
$search_user = isset($_REQUEST['search_user']) ? sanitize_text_field(stripslashes($_REQUEST['search_user'])) : '';
$filter_status = isset($_REQUEST['filter_status']) ? sanitize_text_field($_REQUEST['filter_status']) : '';

$where_clauses = array();
$where_values = array();

if (!empty($search_game)) {
    $where_clauses[] = "(game_title LIKE %s OR internal_code LIKE %s)";
    $like = '%' . $wpdb->esc_like($search_game) . '%';
    $where_values[] = $like;
    $where_values[] = $like;
}

if (!empty($search_user)) {
    $where_clauses[] = "(borrower_name LIKE %s)";
    $like = '%' . $wpdb->esc_like($search_user) . '%';
    $where_values[] = $like;
}

if (!empty($filter_status) && $filter_status !== 'all') {
    if ($filter_status === 'overdue') {
        $where_clauses[] = "status = 'open'";
        $where_clauses[] = "due_date < %s";
        $where_values[] = gmdate('Y-m-d 00:00:00');
    } elseif ($filter_status === 'queue') {
        $where_clauses[] = "status IN ('waitlist', 'available')";
    } else {
        $where_clauses[] = "status = %s";
        $where_values[] = $filter_status;
    }
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
    $loans = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name $where_sql $order_clause", $where_values));
} else {
    $loans = $wpdb->get_results("SELECT * FROM $table_name $order_clause");
}

$base_url = admin_url('admin.php?page=boardgame-loans');
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Loans List', 'boardgame-loans'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new')); ?>" class="page-title-action"><?php esc_html_e('New Loan', 'boardgame-loans'); ?></a>
    <hr class="wp-header-end">

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="boardgame-loans">
        <input type="text" name="search_game" placeholder="<?php esc_attr_e('Search game or code...', 'boardgame-loans'); ?>" value="<?php echo esc_attr($search_game); ?>">
        <input type="text" name="search_user" placeholder="<?php esc_attr_e('Search borrower...', 'boardgame-loans'); ?>" value="<?php echo esc_attr($search_user); ?>">
        <select name="filter_status">
            <option value="all" <?php selected($filter_status, 'all'); ?>><?php esc_html_e('All Statuses', 'boardgame-loans'); ?></option>
            <option value="open" <?php selected($filter_status, 'open'); ?>><?php esc_html_e('Open', 'boardgame-loans'); ?></option>
            <option value="closed" <?php selected($filter_status, 'closed'); ?>><?php esc_html_e('Returned', 'boardgame-loans'); ?></option>
            <option value="overdue" <?php selected($filter_status, 'overdue'); ?>><?php esc_html_e('Overdue', 'boardgame-loans'); ?></option>
            <option value="queue" <?php selected($filter_status, 'queue'); ?>><?php esc_html_e('Waitlist/Queue', 'boardgame-loans'); ?></option>
        </select>
        <?php submit_button(__('Filter', 'boardgame-loans'), 'button', '', false); ?>
        <a href="<?php echo esc_url($base_url); ?>" class="button"><?php esc_html_e('Reset', 'boardgame-loans'); ?></a>
    </form>

    <?php if (isset($_GET['message'])): ?>
        <?php if ($_GET['message'] === 'extended'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan extended successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'waitlist_triggered'): ?>
            <div class="notice notice-error is-dismissible" style="padding: 15px; border-left-color: #d63638; background: #fcf0f1;">
                <p><strong style="font-size: 16px;">⚠️ <?php esc_html_e('ATTENTION: The returned game is WAITLISTED!', 'boardgame-loans'); ?></strong></p>
                <p><?php 
                /* translators: %s: borrower name */
                printf(esc_html__('This game must be kept safely under the counter. It is reserved for: %s.', 'boardgame-loans'), '<strong>' . esc_html($_GET['borrower']) . '</strong>'); 
                ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'issued'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan successfully issued to waitlisted borrower!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'deleted'): ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php esc_html_e('Loan record entirely deleted.', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'closed'): ?>
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e('Loan closed and marked as returned!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('New loan saved successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'updated'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Loan updated successfully!', 'boardgame-loans'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <style>
        .bg-loans-admin-table-wrap { overflow-x: auto; max-width: 100%; border: 1px solid #c3c4c7; margin-top: 10px; }
        .bg-loans-admin-table-wrap table.wp-list-table { display: table !important; min-width: 1000px; border: none; margin: 0; }
        .bg-loans-admin-table-wrap table.wp-list-table thead { display: table-header-group !important; }
        .bg-loans-admin-table-wrap table.wp-list-table tbody { display: table-row-group !important; }
        .bg-loans-admin-table-wrap table.wp-list-table tr { display: table-row !important; }
        .bg-loans-admin-table-wrap table.wp-list-table th,
        .bg-loans-admin-table-wrap table.wp-list-table td { display: table-cell !important; }
        .bg-loans-admin-table-wrap table.wp-list-table td::before { display: none !important; content: none !important; }
    </style>
    <div class="bg-loans-admin-table-wrap">
        <table class="wp-list-table widefat fixed striped" style="border: none; margin: 0;">
        <thead>
            <tr>
                <?php
                $headers = [
                    'id' => __('ID', 'boardgame-loans'),
                    'game_title' => __('Game', 'boardgame-loans'),
                    'borrower' => __('Borrower', 'boardgame-loans'),
                    'loan_date' => __('Loan Date', 'boardgame-loans'),
                    'due_date' => __('Due Date', 'boardgame-loans'),
                    'return_date' => __('Return Date', 'boardgame-loans'),
                    'status' => __('Status', 'boardgame-loans'),
                ];
                foreach ($headers as $col => $label) {
                    $style = '';
                    if ($col === 'id') $style = 'width: 60px;';
                    if ($col === 'game_title') $style = 'width: 30%;';
                    if ($col === 'status') $style = 'width: 80px; text-align: center;';

                    if (in_array($col, $allowed_orderby)) {
                        $sort_order = ($orderby === $col) ? $next_order : 'desc';
                        $url = esc_url(add_query_arg(['orderby' => $col, 'order' => $sort_order], $base_url));
                        $icon = '';
                        if ($orderby === $col) {
                            $icon = $order === 'ASC' ? ' &uarr;' : ' &darr;';
                        }
                        ?>
                        <th style="<?php echo esc_attr($style); ?>"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label) . wp_kses_post($icon); ?></a></th>
                        <?php
                    } else {
                        ?>
                        <th style="<?php echo esc_attr($style); ?>"><?php echo esc_html($label); ?></th>
                        <?php
                    }
                }
                ?>
                <th style="width: 210px;"><?php esc_html_e('Actions', 'boardgame-loans'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($loans): ?>
                <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td data-colname="<?php esc_attr_e('ID', 'boardgame-loans'); ?>"><?php echo esc_html($loan->id); ?></td>
                        <td data-colname="<?php esc_attr_e('Game', 'boardgame-loans'); ?>">
                            <strong><?php 
                                echo esc_html($loan->game_title); 
                                if ($enable_copy_number === 'true' && isset($loan->copy_number) && $loan->copy_number > 1) {
                                    /* translators: %d: copy number */
                                    echo esc_html(' (' . sprintf(__('Copy n. %d', 'boardgame-loans'), $loan->copy_number) . ')');
                                }
                            ?></strong><br>
                        </td>
                        <td data-colname="<?php esc_attr_e('Borrower', 'boardgame-loans'); ?>">
                            <?php
                            if ($loan->borrower_type === 'user' && $loan->borrower_user_id) {
                                $user_info = get_userdata($loan->borrower_user_id);
                                echo esc_html($user_info ? $user_info->display_name : 'User ID: ' . $loan->borrower_user_id);
                            } else {
                                echo esc_html($loan->borrower_name);
                            }
                            ?>
                        </td>
                        <td data-colname="<?php esc_attr_e('Loan Date', 'boardgame-loans'); ?>"><?php echo ($loan->status === 'waitlist') ? '-' : esc_html(date_i18n($date_format_str, strtotime($loan->loan_date))); ?></td>
                        <td data-colname="<?php esc_attr_e('Due Date', 'boardgame-loans'); ?>"><?php echo ($loan->status === 'waitlist' || empty($loan->due_date)) ? '-' : esc_html(date_i18n($date_format_str, strtotime($loan->due_date))); ?></td>
                        <td data-colname="<?php esc_attr_e('Return Date', 'boardgame-loans'); ?>"><?php echo $loan->return_date ? esc_html(date_i18n($date_format_str, strtotime($loan->return_date))) : '-'; ?></td>
                        <td data-colname="<?php esc_attr_e('Status', 'boardgame-loans'); ?>" style="text-align: center;">
                            <?php if ($loan->status === 'closed'): ?>
                                <span class="dashicons dashicons-saved" style="color: #46b450;" title="<?php esc_attr_e('Returned', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($loan->status === 'available'): ?>
                                <span class="dashicons dashicons-bell" style="color: #2271b1;" title="<?php esc_attr_e('Available for Pickup', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($loan->status === 'waitlist'): ?>
                                <span class="dashicons dashicons-hourglass" style="color: #8c8f94;" title="<?php esc_attr_e('Waitlisted', 'boardgame-loans'); ?>"></span>
                            <?php elseif ($loan->status === 'open' && $loan->due_date && strtotime(gmdate('Y-m-d', strtotime($loan->due_date)) . ' 23:59:59') < time()): ?>
                                <span class="dashicons dashicons-warning" style="color: #d63638;" title="<?php esc_attr_e('Overdue', 'boardgame-loans'); ?>"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-update-alt" style="color: #dba617;" title="<?php esc_attr_e('In progress', 'boardgame-loans'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td data-colname="" style="white-space: nowrap;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new&loan_id=' . $loan->id)); ?>" class="button button-small" title="<?php esc_attr_e('Edit', 'boardgame-loans'); ?>"><span class="dashicons dashicons-edit" style="line-height: 1.5;"></span></a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boardgame-loans-new&action=copy_loan&loan_id=' . $loan->id)); ?>" class="button button-small" title="<?php esc_attr_e('Copy', 'boardgame-loans'); ?>"><span class="dashicons dashicons-admin-page" style="line-height: 1.5;"></span></a>
                            <?php if ($loan->status === 'available'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=issue_loan&loan_id=' . $loan->id), 'issue_loan_' . $loan->id)); ?>" class="button button-small" title="<?php esc_attr_e('Issue Loan', 'boardgame-loans'); ?>" style="border-color: #2271b1; color: #2271b1;"><span class="dashicons dashicons-yes" style="line-height: 1.5;"></span></a>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=close_loan&loan_id=' . $loan->id), 'close_loan_' . $loan->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to cancel this waitlisted item and return it to stock?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Cancel', 'boardgame-loans'); ?>"><span class="dashicons dashicons-no" style="line-height: 1.5; color: #d63638;"></span></a>
                            <?php elseif ($loan->status === 'open'): ?>
                                <?php /* translators: %d: extension days */ ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=extend_loan&loan_id=' . $loan->id), 'extend_loan_' . $loan->id)); ?>" class="button button-small" title="<?php echo esc_attr(sprintf(__('Extend by %d days', 'boardgame-loans'), $extend_days_setting)); ?>"><span class="dashicons dashicons-clock" style="line-height: 1.5;"></span></a>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=close_loan&loan_id=' . $loan->id), 'close_loan_' . $loan->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to mark this loan as returned?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Close', 'boardgame-loans'); ?>"><span class="dashicons dashicons-yes" style="line-height: 1.5; color: #46b450;"></span></a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=boardgame-loans&action=delete_loan&loan_id=' . $loan->id), 'delete_loan_' . $loan->id)); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e('Are you sure you want to hard delete this record?', 'boardgame-loans'); ?>');" title="<?php esc_attr_e('Delete', 'boardgame-loans'); ?>"><span class="dashicons dashicons-trash" style="line-height: 1.5; color: #d63638;"></span></a>
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
