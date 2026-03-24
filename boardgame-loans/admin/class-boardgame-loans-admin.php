<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BoardGame_Loans_Admin
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'handle_new_loan_submission'));
        add_action('wp_ajax_bg_loans_change_status', array($this, 'ajax_change_status'));
        add_action('wp_ajax_bg_loans_search_tablepress', array($this, 'ajax_search_tablepress'));
    }

    public function handle_new_loan_submission()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bg_loans';

        // Azione: Elimina Prestito
        if (isset($_GET['action']) && $_GET['action'] === 'delete_loan' && isset($_GET['loan_id'])) {
            check_admin_referer('delete_loan_' . $_GET['loan_id']);
            $wpdb->delete($table_name, array('id' => intval($_GET['loan_id'])));
            wp_safe_redirect(admin_url('admin.php?page=boardgame-loans&message=deleted'));
            exit;
        }

        // Azione: Chiudi Prestito
        if (isset($_GET['action']) && $_GET['action'] === 'close_loan' && isset($_GET['loan_id'])) {
            check_admin_referer('close_loan_' . $_GET['loan_id']);
            $loan_id = intval($_GET['loan_id']);
            
            $closed_loan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $loan_id));

            $wpdb->update(
                $table_name,
                array('status' => 'closed', 'return_date' => gmdate('Y-m-d H:i:s')),
                array('id' => $loan_id),
                array('%s', '%s'),
                array('%d')
            );

            // Waitlist interception logic
            if ($closed_loan) {
                $enable_waitlist = get_option('bg_loans_enable_waitlist', 'false');
                if ($enable_waitlist === 'true') {
                    $waitlist_unique = get_option('bg_loans_waitlist_unique', 'title_copy');
                    if ($waitlist_unique === 'internal_code' && !empty($closed_loan->internal_code)) {
                        $waitlisted = $wpdb->get_row($wpdb->prepare("SELECT id, borrower_name FROM $table_name WHERE status = 'waitlist' AND internal_code = %s ORDER BY loan_date ASC LIMIT 1", $closed_loan->internal_code));
                    } else {
                        $waitlisted = $wpdb->get_row($wpdb->prepare("SELECT id, borrower_name FROM $table_name WHERE status = 'waitlist' AND game_title = %s AND copy_number = %d ORDER BY loan_date ASC LIMIT 1", $closed_loan->game_title, $closed_loan->copy_number));
                    }

                    if ($waitlisted) {
                        $expire_date = gmdate('Y-m-d H:i:s', strtotime("+3 days"));
                        $wpdb->update($table_name, array('status' => 'available', 'due_date' => $expire_date), array('id' => $waitlisted->id));
                        wp_safe_redirect(admin_url('admin.php?page=boardgame-loans&message=waitlist_triggered&borrower=' . urlencode($waitlisted->borrower_name)));
                        exit;
                    }
                }
            }

            wp_safe_redirect(admin_url('admin.php?page=boardgame-loans&message=closed'));
            exit;
        }

        // Azione: Estendi Prestito (Proroga)
        if (isset($_GET['action']) && $_GET['action'] === 'extend_loan' && isset($_GET['loan_id'])) {
            check_admin_referer('extend_loan_' . $_GET['loan_id']);
            $loan_id = intval($_GET['loan_id']);

            $loan = $wpdb->get_row($wpdb->prepare("SELECT due_date FROM $table_name WHERE id = %d", $loan_id));
            if ($loan) {
                $base_timestamp = !empty($loan->due_date) ? strtotime($loan->due_date) : time();
                // If it's already overdue, the extension starts from today to avoid useless extensions in the past
                if ($base_timestamp < time() && !empty($loan->due_date)) {
                    $base_timestamp = time();
                }
                
                $extend_days_setting = intval(get_option('bg_loans_extend_days', 7));
                $new_due_date = gmdate('Y-m-d', strtotime("+$extend_days_setting days", $base_timestamp));
                
                $wpdb->update(
                    $table_name,
                    array('due_date' => $new_due_date),
                    array('id' => $loan_id),
                    array('%s'),
                    array('%d')
                );
                
                wp_safe_redirect(admin_url('admin.php?page=boardgame-loans&message=extended'));
                exit;
            }
        }

        // Azione: Attiva prestito da Waitlist (Issue Loan)
        if (isset($_GET['action']) && $_GET['action'] === 'issue_loan' && isset($_GET['loan_id'])) {
            check_admin_referer('issue_loan_' . $_GET['loan_id']);
            $loan_id = intval($_GET['loan_id']);

            $loan_duration = intval(get_option('bg_loans_default_duration', 7));
            $new_due_date = gmdate('Y-m-d H:i:s', strtotime("+$loan_duration days"));

            $wpdb->update(
                $table_name,
                array('status' => 'open', 'loan_date' => current_time('mysql'), 'due_date' => $new_due_date),
                array('id' => $loan_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            
            wp_safe_redirect(admin_url('admin.php?page=boardgame-loans&message=issued'));
            exit;
        }

        // Azione: Salva/Aggiorna Prestito
        if (isset($_POST['action']) && $_POST['action'] === 'save_new_loan' && isset($_POST['submit_new_loan'])) {

            // Verifica di sicurezza sul Nonce
            if (!isset($_POST['bg_loans_nonce']) || !wp_verify_nonce($_POST['bg_loans_nonce'], 'save_new_loan_action')) {
                wp_die(esc_html__('Permission denied.', 'boardgame-loans'));
            }

            // Preparazione dei dati puliti
            $data = array(
                'game_source' => isset($_POST['game_source']) ? sanitize_text_field($_POST['game_source']) : 'manual',
                'game_ref' => isset($_POST['game_ref']) ? sanitize_text_field($_POST['game_ref']) : '',
                'game_title' => sanitize_text_field($_POST['game_title']),
                'internal_code' => isset($_POST['internal_code']) ? sanitize_text_field($_POST['internal_code']) : '',
                'copy_number' => isset($_POST['copy_number']) ? intval($_POST['copy_number']) : 1,
                'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'open',
                'borrower_type' => isset($_POST['borrower_type']) ? sanitize_text_field($_POST['borrower_type']) : 'manual',
                'borrower_user_id' => !empty($_POST['borrower_user_id']) ? intval($_POST['borrower_user_id']) : null,
                'borrower_name' => sanitize_text_field($_POST['borrower_name']),
                'loan_date' => sanitize_text_field($_POST['loan_date']),
                'due_date' => !empty($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : null,
                'notes' => sanitize_textarea_field($_POST['notes']),
            );

            // Controllo base dei campi obbligatori
            if (empty($data['game_title']) || empty($data['loan_date'])) {
                wp_die(esc_html__('Game title and loan date are required.', 'boardgame-loans'));
            }

            // Constraint: prevent duplicate active loan
            if ($data['status'] === 'open') {
                $waitlist_unique = get_option('bg_loans_waitlist_unique', 'title_copy');
                $is_mod = !empty($_POST['loan_id']) ? intval($_POST['loan_id']) : 0;
                
                if ($waitlist_unique === 'internal_code' && !empty($data['internal_code'])) {
                    $conflict = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE status = 'open' AND internal_code = %s AND id != %d", $data['internal_code'], $is_mod));
                } else {
                    $conflict = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE status = 'open' AND game_title = %s AND copy_number = %d AND id != %d", $data['game_title'], $data['copy_number'], $is_mod));
                }
                
                if ($conflict) {
                    wp_die(esc_html__('Error: This game copy is already currently checked out.', 'boardgame-loans'), esc_html__('Double Checkout Conflict', 'boardgame-loans'), array('back_link' => true));
                }
            }

            if (!empty($_POST['loan_id'])) {
                // Modifica
                $loan_id = intval($_POST['loan_id']);
                $wpdb->update($table_name, $data, array('id' => $loan_id));
                $msg = 'updated';
            }
            else {
                // Inserimento
                $wpdb->insert($table_name, $data);
                $loan_id = $wpdb->insert_id;
                $msg = 'success';
            }

            // Refresh ritornando alla pagina elenco prestiti con un messaggio di successo
            $redirect_url = add_query_arg(
                array(
                'page' => 'boardgame-loans',
                'message' => $msg
            ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
    public function ajax_search_tablepress()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'boardgame-loans'));
        }

        $query = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
        if (empty($query)) {
            wp_send_json_error(__('Empty query', 'boardgame-loans'));
        }

        if (!class_exists('TablePress')) {
            wp_send_json_error(__('TablePress is not active or installed.', 'boardgame-loans'));
        }

        $table_id = get_option('bg_loans_tablepress_id');
        if (empty($table_id)) {
            wp_send_json_error(__('TablePress ID not configured in settings.', 'boardgame-loans'));
        }

        $model_table = TablePress::load_model('table');
        $table = $model_table->load($table_id, true, true);
        
        if (is_wp_error($table) || empty($table['data'])) {
            wp_send_json_error(__('Could not load TablePress table. Check the ID in settings.', 'boardgame-loans'));
        }

        $data = $table['data'];
        $headers = $data[0]; // first row
        
        $col_title_name = get_option('bg_loans_tablepress_col', '');
        $col_year_name = get_option('bg_loans_tablepress_col_year', '');
        $col_id_name = get_option('bg_loans_tablepress_col_id', '');

        $col_title_idx = -1;
        $col_year_idx = -1;
        $col_id_idx = -1;

        foreach ($headers as $index => $header) {
            $h = trim($header);
            if (!empty($col_title_name) && strcasecmp($h, $col_title_name) === 0) $col_title_idx = $index;
            if (!empty($col_year_name) && strcasecmp($h, $col_year_name) === 0) $col_year_idx = $index;
            if (!empty($col_id_name) && strcasecmp($h, $col_id_name) === 0) $col_id_idx = $index;
        }

        if ($col_title_idx === -1) {
            wp_send_json_error(__('Configured Title column not found in the table headers. Check settings.', 'boardgame-loans'));
        }

        $results = array();
        $query_lower = mb_strtolower($query);

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            if (empty(array_filter($row))) continue;

            $title = isset($row[$col_title_idx]) ? trim($row[$col_title_idx]) : '';
            if (empty($title)) continue;

            $year = ($col_year_idx !== -1 && isset($row[$col_year_idx])) ? trim($row[$col_year_idx]) : '';
            $id = ($col_id_idx !== -1 && isset($row[$col_id_idx])) ? trim($row[$col_id_idx]) : trim($row[0]);
            
            if (empty($id)) $id = (string)$i;

            $match = false;
            if ($id === $query) {
                $match = true;
            } else if (mb_strpos(mb_strtolower($title), $query_lower) !== false) {
                $match = true;
            } else if (mb_strpos(mb_strtolower($id), $query_lower) !== false) {
                $match = true;
            }

            if ($match) {
                $results[] = array(
                    'id' => $id,
                    'title' => $title,
                    'year' => $year
                );
            }
            
            if (count($results) >= 50) break;
        }

        wp_send_json_success($results);
    }

    public function add_plugin_admin_menu()
    {
        add_menu_page(
            __('BoardGame Loans', 'boardgame-loans'),
            __('BoardGame Loans', 'boardgame-loans'),
            'manage_options',
            'boardgame-loans',
            array($this, 'display_active_loans_page'),
            'dashicons-book',
            25
        );

        add_submenu_page(
            'boardgame-loans',
            __('Loans List', 'boardgame-loans'),
            __('Loans List', 'boardgame-loans'),
            'manage_options',
            'boardgame-loans',
            array($this, 'display_active_loans_page')
        );

        add_submenu_page(
            'boardgame-loans',
            __('New Loan', 'boardgame-loans'),
            __('New Loan', 'boardgame-loans'),
            'manage_options',
            'boardgame-loans-new',
            array($this, 'display_new_loan_page')
        );

        add_submenu_page(
            'boardgame-loans',
            __('Shortcode Help', 'boardgame-loans'),
            __('Shortcode Help', 'boardgame-loans'),
            'manage_options',
            'boardgame-loans-help',
            array($this, 'display_help_page')
        );

        add_submenu_page(
            'boardgame-loans',
            __('Settings', 'boardgame-loans'),
            __('Settings', 'boardgame-loans'),
            'manage_options',
            'boardgame-loans-settings',
            array($this, 'display_settings_page')
        );
    }

    public function display_active_loans_page()
    {
        require plugin_dir_path(dirname(__FILE__)) . 'admin/partials/boardgame-loans-admin-display-list.php';
    }

    public function display_new_loan_page()
    {
        require plugin_dir_path(dirname(__FILE__)) . 'admin/partials/boardgame-loans-admin-display-form.php';
    }

    public function display_help_page()
    {
        require plugin_dir_path(dirname(__FILE__)) . 'admin/partials/boardgame-loans-admin-display-help.php';
    }

    public function display_settings_page()
    {
        require plugin_dir_path(dirname(__FILE__)) . 'admin/partials/boardgame-loans-admin-display-settings.php';
    }
}
