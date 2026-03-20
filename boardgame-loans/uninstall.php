<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * TODO: 
 * - Selezionare se cancellare o mantenere la tabella wp_bg_loans alla disinstallazione del plugin.
 * - Esempio (da scommentare se richiesto):
 * global $wpdb;
 * $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bg_loans" );
 */
