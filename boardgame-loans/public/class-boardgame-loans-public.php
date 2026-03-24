<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BoardGame_Loans_Public
{
    public function init()
    {
        // Shortcode registration
        add_shortcode('bg_loans_list', array($this, 'render_bg_loans_list_shortcode'));
        add_shortcode('bg_loans_waitlist', array($this, 'render_bg_loans_waitlist_shortcode'));
    }

    public function render_bg_loans_waitlist_shortcode($atts)
    {
        if (!is_array($atts)) {
            $atts = array();
        }
        $atts['status_filter'] = 'queue';
        if (!isset($atts['show_status'])) $atts['show_status'] = 'true';
        if (!isset($atts['show_borrower'])) $atts['show_borrower'] = 'true';
        if (!isset($atts['show_loan_date'])) $atts['show_loan_date'] = 'false';
        if (!isset($atts['show_due_date'])) $atts['show_due_date'] = 'false';
        if (!isset($atts['show_return_date'])) $atts['show_return_date'] = 'false';
        return $this->render_bg_loans_list_shortcode($atts);
    }

    public function render_bg_loans_list_shortcode($atts)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bg_loans';

        // Default attributes
        $a = shortcode_atts(array(
            'status_filter'    => 'open',   // Values: 'open', 'closed', 'all'
            'show_loan_date'   => 'true',
            'show_due_date'    => 'true',
            'show_borrower'    => 'false',
            'show_return_date' => 'false',
            'show_status'      => 'false',
            'css_class'        => '',
        ), $atts, 'bg_loans_list');

        $date_format_setting = get_option('bg_loans_date_format', 'eu');
        $date_format_str = $date_format_setting === 'us' ? 'Y-m-d' : 'd/m/Y';

        // Build Query
        $where = "";
        if ($a['status_filter'] === 'open') {
            $where = "WHERE status = 'open'";
        } elseif ($a['status_filter'] === 'closed') {
            $where = "WHERE status = 'closed'";
        } elseif ($a['status_filter'] === 'queue' || $a['status_filter'] === 'waitlist') {
            $where = "WHERE status IN ('waitlist', 'available')";
        }

        // Newest loans first by default
        $loans = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY loan_date DESC");
        
        $enable_copy_number = get_option('bg_loans_enable_copy_number', 'false');

        if (!$loans) {
            return '<p class="bg-loans-public-empty" style="text-align: center; margin: 20px 0; font-style: italic;">' . esc_html__('No loans to show at the moment.', 'boardgame-loans') . '</p>';
        }

        $extra_class = !empty($a['css_class']) ? ' ' . esc_attr($a['css_class']) : '';

        // Responsive container for mobile screens
        $html = '<div class="bg-loans-table-responsive" style="overflow-x: auto; max-width: 100%;">';
        
        // Output table
        $html .= '<table class="bg-loans-public-table' . $extra_class . '" style="width: 100%; text-align: left; border-collapse: collapse; min-width: 600px;">';
        $html .= '<thead><tr>';
        
        $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Game', 'boardgame-loans') . '</th>';
        
        if (filter_var($a['show_borrower'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Borrower', 'boardgame-loans') . '</th>';
        }
        if (filter_var($a['show_loan_date'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Loan Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($a['show_due_date'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Estimated Due Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($a['show_return_date'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Return Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($a['show_status'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Status', 'boardgame-loans') . '</th>';
        }
        
        $html .= '</tr></thead><tbody>';

        foreach ($loans as $loan) {
            $html .= '<tr>';
            $title_html = esc_html($loan->game_title);
            if ($enable_copy_number === 'true' && isset($loan->copy_number) && $loan->copy_number > 1) {
                /* translators: %d: copy number */
                $title_html .= ' (' . esc_html(sprintf(__('Copy n. %d', 'boardgame-loans'), $loan->copy_number)) . ')';
            }
            $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;"><strong>' . $title_html . '</strong></td>';

            if (filter_var($a['show_borrower'], FILTER_VALIDATE_BOOLEAN)) {
                $borrower = '';
                if ($loan->borrower_type === 'user' && $loan->borrower_user_id) {
                    $user_info = get_userdata($loan->borrower_user_id);
                    $borrower = $user_info ? $user_info->display_name : 'User ID: ' . $loan->borrower_user_id;
                } else {
                    $borrower = $loan->borrower_name;
                }
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($borrower) . '</td>';
            }

            if (filter_var($a['show_loan_date'], FILTER_VALIDATE_BOOLEAN)) {
                $loan_str = ($loan->status === 'waitlist') ? '-' : date_i18n($date_format_str, strtotime($loan->loan_date));
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($loan_str) . '</td>';
            }

            if (filter_var($a['show_due_date'], FILTER_VALIDATE_BOOLEAN)) {
                $due_formatted = ($loan->status === 'waitlist' || empty($loan->due_date)) ? '-' : date_i18n($date_format_str, strtotime($loan->due_date));
                
                // Visual alert if overdue and still open
                $is_overdue = ($loan->status === 'open' && $loan->due_date && strtotime(gmdate('Y-m-d', strtotime($loan->due_date)) . ' 23:59:59') < time());
                if ($is_overdue) {
                    $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px; color: #d63638; font-weight: bold;">' . esc_html($due_formatted) . '</td>';
                } else {
                    $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($due_formatted) . '</td>';
                }
            }

            if (filter_var($a['show_return_date'], FILTER_VALIDATE_BOOLEAN)) {
                $return_formatted = $loan->return_date ? date_i18n($date_format_str, strtotime($loan->return_date)) : '-';
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($return_formatted) . '</td>';
            }

            if (filter_var($a['show_status'], FILTER_VALIDATE_BOOLEAN)) {
                if ($loan->status === 'open') $status_label = __('In progress', 'boardgame-loans');
                elseif ($loan->status === 'closed') $status_label = __('Returned', 'boardgame-loans');
                elseif ($loan->status === 'waitlist') $status_label = __('Waitlisted', 'boardgame-loans');
                elseif ($loan->status === 'available') $status_label = __('Available for Pickup', 'boardgame-loans');
                else $status_label = $loan->status;
                $html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($status_label) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }
}
