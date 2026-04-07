<?php
// admin/partials/boardgame-loans-admin-display-form.php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bg_loans';

$form_mode = get_option('bg_loans_form_mode', 'simple');

$is_edit = false;
$is_copy = false;
$loan = null;

if (!empty($_GET['loan_id'])) {
    $loan_id = intval($_GET['loan_id']);
    $loan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $loan_id));

    if (isset($_GET['action']) && $_GET['action'] === 'copy_loan') {
        $is_copy = true;
    } else {
        $is_edit = true;
    }
}

if ($is_copy) {
    $title = __('New loan (Copy)', 'boardgame-loans');
} elseif ($is_edit) {
    $title = __('Edit loan', 'boardgame-loans');
} else {
    $title = __('New loan', 'boardgame-loans');
}
?>
<div class="wrap">
    <h1><?php echo esc_html($title); ?></h1>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if ($_GET['message'] === 'success') {
                    esc_html_e('Loan saved successfully!', 'boardgame-loans');
                } elseif ($_GET['message'] === 'updated') {
                    esc_html_e('Loan updated successfully!', 'boardgame-loans');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('save_new_loan_action', 'bg_loans_nonce'); ?>
        <input type="hidden" name="action" value="save_new_loan">
        <?php if ($is_edit && $loan): ?>
            <input type="hidden" name="loan_id" value="<?php echo esc_attr($loan->id); ?>">
        <?php endif; ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <?php if ($form_mode === 'advanced'): ?>
                <tr>
                    <th scope="row"><label for="game_source"><?php esc_html_e('Source', 'boardgame-loans'); ?></label></th>
                    <td>
                        <select name="game_source" id="game_source" required>
                            <option value="tablepress" <?php selected($loan ? $loan->game_source : 'tablepress', 'tablepress'); ?>>TablePress</option>
                            <option value="manual" <?php selected($loan ? $loan->game_source : '', 'manual'); ?>>Manual</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="game_ref"><?php esc_html_e('Reference (TablePress ID)', 'boardgame-loans'); ?></label></th>
                    <td>
                        <input name="game_ref" type="text" id="game_ref" value="<?php echo esc_attr($loan ? $loan->game_ref : ''); ?>" class="regular-text">
                        <button type="button" class="button" id="btn_search_game"><?php esc_html_e('Search', 'boardgame-loans'); ?></button>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><label for="game_title"><?php esc_html_e('Game Title', 'boardgame-loans'); ?></label></th>
                    <td>
                        <input name="game_title" type="text" id="game_title" value="<?php echo esc_attr($loan ? $loan->game_title : ''); ?>" class="regular-text" required>
                    </td>
                </tr>
                <?php $enable_waitlist = get_option('bg_loans_enable_waitlist', 'false'); ?>
                <tr>
                    <th scope="row"><label for="loan_status"><?php esc_html_e('Status', 'boardgame-loans'); ?></label></th>
                    <td>
                        <select name="status" id="loan_status">
                            <option value="open" <?php selected($loan ? $loan->status : 'open', 'open'); ?>><?php esc_html_e('Open', 'boardgame-loans'); ?></option>
                            <?php if ($enable_waitlist === 'true'): ?>
                                <option value="waitlist" <?php selected($loan ? $loan->status : '', 'waitlist'); ?>><?php esc_html_e('Waitlist', 'boardgame-loans'); ?></option>
                                <?php if ($loan && $loan->status === 'available'): ?>
                                    <option value="available" selected><?php esc_html_e('Available for Pickup', 'boardgame-loans'); ?></option>
                                <?php endif; ?>
                            <?php endif; ?>
                            <option value="closed" <?php selected($loan ? $loan->status : '', 'closed'); ?>><?php esc_html_e('Returned', 'boardgame-loans'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="internal_code"><?php esc_html_e('Internal Code', 'boardgame-loans'); ?></label></th>
                    <td>
                        <input name="internal_code" type="text" id="internal_code" value="<?php echo esc_attr($loan ? (isset($loan->internal_code) ? $loan->internal_code : '') : ''); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Optional internal code for the game.', 'boardgame-loans'); ?></p>
                    </td>
                </tr>
                <?php $enable_copy_number = get_option('bg_loans_enable_copy_number', 'false'); ?>
                <?php if ($enable_copy_number === 'true'): ?>
                <tr>
                    <th scope="row"><label for="copy_number"><?php esc_html_e('Copy Number', 'boardgame-loans'); ?></label></th>
                    <td>
                        <select name="copy_number" id="copy_number">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo esc_attr($i); ?>" <?php selected($loan ? $loan->copy_number : 1, $i); ?>><?php echo esc_html($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($form_mode === 'advanced'): ?>
                <tr>
                    <th scope="row"><label for="borrower_type"><?php esc_html_e('Borrower type', 'boardgame-loans'); ?></label></th>
                    <td>
                        <select name="borrower_type" id="borrower_type" required>
                            <option value="user" <?php selected($loan ? $loan->borrower_type : '', 'user'); ?>>User</option>
                            <option value="manual" <?php selected($loan ? $loan->borrower_type : 'manual', 'manual'); ?>>Manual</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="borrower_user_id"><?php esc_html_e('Borrower user', 'boardgame-loans'); ?></label></th>
                    <td>
                        <select name="borrower_user_id" id="borrower_user_id">
                            <option value=""><?php esc_html_e('-- Select a user (TODO) --', 'boardgame-loans'); ?></option>
                            <!-- TODO: Populate dynamically or via JS (Select2 ajax) -->
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><label for="borrower_name"><?php esc_html_e('Borrower name', 'boardgame-loans'); ?></label></th>
                    <td>
                        <input name="borrower_name" type="text" id="borrower_name" value="<?php echo esc_attr($loan ? $loan->borrower_name : ''); ?>" class="regular-text" <?php if($form_mode === 'simple') echo 'required'; ?>>
                        <?php if ($form_mode === 'advanced'): ?>
                        <p class="description"><?php esc_html_e('In case the loan is not linked to a registered user.', 'boardgame-loans'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="loan_date"><?php esc_html_e('Loan date', 'boardgame-loans'); ?></label></th>
                    <td>
                        <?php $default_loan_date = ($loan && !$is_copy) ? gmdate('Y-m-d', strtotime($loan->loan_date)) : gmdate('Y-m-d'); ?>
                        <input name="loan_date" type="date" id="loan_date" value="<?php echo esc_attr($default_loan_date); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="due_date"><?php esc_html_e('Due date', 'boardgame-loans'); ?></label></th>
                    <td>
                        <?php $default_due_date = ($loan && $loan->due_date && !$is_copy) ? gmdate('Y-m-d', strtotime($loan->due_date)) : gmdate('Y-m-d', strtotime('+7 days')); ?>
                        <input name="due_date" type="date" id="due_date" value="<?php echo esc_attr($default_due_date); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="notes"><?php esc_html_e('Notes', 'boardgame-loans'); ?></label></th>
                    <td>
                        <textarea name="notes" id="notes" rows="4" cols="50" class="large-text"><?php echo esc_textarea($loan ? $loan->notes : ''); ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(__('Save loan', 'boardgame-loans'), 'primary', 'submit_new_loan'); ?>
    </form>
</div>
<?php if ($form_mode === 'advanced'): ?>
<?php else: ?>
<?php endif; ?>
