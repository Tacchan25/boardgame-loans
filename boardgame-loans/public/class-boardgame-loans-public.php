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

        // Enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('boardgame-loans-public', plugin_dir_url(dirname(__FILE__)) . 'public/css/boardgame-loans-public.css', array(), '1.0.4');
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
        $bg_loans_table_name = $wpdb->prefix . 'bg_loans';

        // Default attributes
        $bg_loans_atts = shortcode_atts(array(
            'status_filter'    => 'open',   // Values: 'open', 'closed', 'all'
            'show_loan_date'   => 'true',
            'show_due_date'    => 'true',
            'show_borrower'    => 'false',
            'show_return_date' => 'false',
            'show_status'      => 'false',
            'css_class'        => '',
        ), $atts, 'bg_loans_list');

        $bg_loans_date_setting = get_option('bg_loans_date_format', 'eu');
        $bg_loans_date_format_str = $bg_loans_date_setting === 'us' ? 'Y-m-d' : 'd/m/Y';

        // Build Query
        $bg_loans_where = "";
        if ($bg_loans_atts['status_filter'] === 'open') {
            $bg_loans_where = "WHERE status = 'open'";
        } elseif ($bg_loans_atts['status_filter'] === 'closed') {
            $bg_loans_where = "WHERE status = 'closed'";
        } elseif ($bg_loans_atts['status_filter'] === 'queue' || $bg_loans_atts['status_filter'] === 'waitlist') {
            $bg_loans_where = "WHERE status IN ('waitlist', 'available')";
        }

        // Newest loans first by default
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Query structure is safe; identifiers are internal. Using prepare for compliance.
        $bg_loans_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bg_loans_table_name} {$bg_loans_where} ORDER BY loan_date DESC", array()));
        
        $bg_loans_enable_copy_pref = get_option('bg_loans_enable_copy_number', 'false');

        if (!$bg_loans_items) {
            return '<p class="bg-loans-public-empty" style="text-align: center; margin: 20px 0; font-style: italic;">' . esc_html__('No loans to show at the moment.', 'boardgame-loans') . '</p>';
        }

        $bg_loans_extra_css = !empty($bg_loans_atts['css_class']) ? ' ' . esc_attr($bg_loans_atts['css_class']) : '';

        // Responsive container for mobile screens
        $bg_loans_html = '<div class="bg-loans-table-responsive">';
        
        // Output table
        $bg_loans_html .= '<table class="bg-loans-public-table' . $bg_loans_extra_css . '" style="width: 100%; text-align: left; border-collapse: collapse; min-width: 600px;">';
        $bg_loans_html .= '<thead><tr>';
        
        $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Game', 'boardgame-loans') . '</th>';
        
        if (filter_var($bg_loans_atts['show_borrower'], FILTER_VALIDATE_BOOLEAN)) {
            $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Borrower', 'boardgame-loans') . '</th>';
        }
        if (filter_var($bg_loans_atts['show_loan_date'], FILTER_VALIDATE_BOOLEAN)) {
            $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Loan Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($bg_loans_atts['show_due_date'], FILTER_VALIDATE_BOOLEAN)) {
            $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Estimated Due Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($bg_loans_atts['show_return_date'], FILTER_VALIDATE_BOOLEAN)) {
            $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Return Date', 'boardgame-loans') . '</th>';
        }
        if (filter_var($bg_loans_atts['show_status'], FILTER_VALIDATE_BOOLEAN)) {
            $bg_loans_html .= '<th style="border-bottom: 2px solid #ccc; padding: 8px;">' . esc_html__('Status', 'boardgame-loans') . '</th>';
        }
        
        $bg_loans_html .= '</tr></thead><tbody>';

        foreach ($bg_loans_items as $bg_loans_item) {
            $bg_loans_html .= '<tr>';
            $bg_loans_title_label = esc_html($bg_loans_item->game_title);
            if ($bg_loans_enable_copy_pref === 'true' && isset($bg_loans_item->copy_number) && $bg_loans_item->copy_number > 1) {
                /* translators: %d: copy number */
                $bg_loans_title_label .= ' (' . esc_html(sprintf(__('Copy n. %d', 'boardgame-loans'), $bg_loans_item->copy_number)) . ')';
            }
            $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;"><strong>' . $bg_loans_title_label . '</strong></td>';

            if (filter_var($bg_loans_atts['show_borrower'], FILTER_VALIDATE_BOOLEAN)) {
                $bg_loans_borrower_label = '';
                if ($bg_loans_item->borrower_type === 'user' && $bg_loans_item->borrower_user_id) {
                    $bg_loans_user_info = get_userdata($bg_loans_item->borrower_user_id);
                    $bg_loans_borrower_label = $bg_loans_user_info ? $bg_loans_user_info->display_name : 'User ID: ' . $bg_loans_item->borrower_user_id;
                } else {
                    $bg_loans_borrower_label = $bg_loans_item->borrower_name;
                }
                $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($bg_loans_borrower_label) . '</td>';
            }

            if (filter_var($bg_loans_atts['show_loan_date'], FILTER_VALIDATE_BOOLEAN)) {
                $bg_loans_loan_date_str = ($bg_loans_item->status === 'waitlist') ? '-' : date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->loan_date));
                $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($bg_loans_loan_date_str) . '</td>';
            }

            if (filter_var($bg_loans_atts['show_due_date'], FILTER_VALIDATE_BOOLEAN)) {
                $bg_loans_due_date_str = ($bg_loans_item->status === 'waitlist' || empty($bg_loans_item->due_date)) ? '-' : date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->due_date));
                
                // Visual alert if overdue and still open
                $bg_loans_is_overdue_now = ($bg_loans_item->status === 'open' && $bg_loans_item->due_date && strtotime(gmdate('Y-m-d', strtotime($bg_loans_item->due_date)) . ' 23:59:59') < time());
                if ($bg_loans_is_overdue_now) {
                    $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px; color: #d63638; font-weight: bold;">' . esc_html($bg_loans_due_date_str) . '</td>';
                } else {
                    $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($bg_loans_due_date_str) . '</td>';
                }
            }

            if (filter_var($bg_loans_atts['show_return_date'], FILTER_VALIDATE_BOOLEAN)) {
                $bg_loans_return_date_label = $bg_loans_item->return_date ? date_i18n($bg_loans_date_format_str, strtotime($bg_loans_item->return_date)) : '-';
                $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($bg_loans_return_date_label) . '</td>';
            }

            if (filter_var($bg_loans_atts['show_status'], FILTER_VALIDATE_BOOLEAN)) {
                if ($bg_loans_item->status === 'open') {
                    $bg_loans_status_txt = __('In progress', 'boardgame-loans');
                } elseif ($bg_loans_item->status === 'closed') {
                    $bg_loans_status_txt = __('Returned', 'boardgame-loans');
                } elseif ($bg_loans_item->status === 'waitlist') {
                    $bg_loans_status_txt = __('Waitlisted', 'boardgame-loans');
                } elseif ($bg_loans_item->status === 'available') {
                    $bg_loans_status_txt = __('Available for Pickup', 'boardgame-loans');
                } else {
                    $bg_loans_status_txt = $bg_loans_item->status;
                }
                $bg_loans_html .= '<td style="border-bottom: 1px solid #eee; padding: 8px;">' . esc_html($bg_loans_status_txt) . '</td>';
            }

            $bg_loans_html .= '</tr>';
        }

        $bg_loans_html .= '</tbody></table>';
        $bg_loans_html .= '</div>';

        return $bg_loans_html;
    }
}
