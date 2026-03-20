<?php
/**
 * Plugin Name:       WP BoardGame Loans
 * Plugin URI:        https://github.com/Tacchan25/boardgame-loans
 * Description:       Gestione prestiti di giochi da tavolo per associazioni.
 * Version:           0.1.0
 * Author:            Tacchan25
 * Author URI:        https://github.com/Tacchan25
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       WP-boardgame-loans
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * TODO:
 * - Salvataggio del form (gestione del POST per il nuovo prestito) nel pannello Admin.
 * - Validazione dei dati di input (es. date corrette, formato dei selected fields, ecc.).
 * - Gestione avanzata del campo 'Borrower user', es. con una tendina popolata tramite i dati degli utenti WP o tramite autocompletamento (AJAX/Select2).
 * - Integrazione della ricerca dei giochi per TablePress o BGG nel campo 'Game reference', in modo da evitare l'inserimento manuale dell'ID.
 * - Creazione e gestione di una vista di dettaglio o modifica per i prestiti esistenti.
 * - Nuova funzionalità per segnare un prestito come restituito (compilando `return_date` e chiudendo lo `status` a 'closed').
 */

class BoardGame_Loans {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // DB upgrade routine
        if (get_option('bg_loans_db_version') !== '1.1') {
            $this->activate();
            update_option('bg_loans_db_version', '1.1');
        }

        // Auto-compile MO file if missing (useful for dev/distribution without CLI)
        $mofile = dirname(__FILE__) . '/languages/boardgame-loans-it_IT.mo';
        $pofile = dirname(__FILE__) . '/languages/boardgame-loans-it_IT.po';
        if (!file_exists($mofile) && file_exists($pofile)) {
            require_once dirname(__FILE__) . '/compile_mo.php';
            bg_loans_compile_mo($pofile, $mofile);
        }

        $this->load_dependencies();
        
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        if ( is_admin() ) {
            $admin = new BoardGame_Loans_Admin();
            $admin->init();
        }

        $public = new BoardGame_Loans_Public();
        $public->init();
    }

    public function load_textdomain() {
        load_plugin_textdomain('boardgame-loans', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function load_dependencies() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-boardgame-loans-admin.php';
        require_once plugin_dir_path( __FILE__ ) . 'public/class-boardgame-loans-public.php';
    }

    public function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bg_loans';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
            game_source VARCHAR(20) NOT NULL,
            game_ref VARCHAR(100) NOT NULL,
            game_title VARCHAR(190) NULL,
            internal_code VARCHAR(100) NULL,
            copy_number INT(11) NOT NULL DEFAULT 1,
            borrower_type VARCHAR(20) NOT NULL,
            borrower_user_id BIGINT UNSIGNED NULL,
            borrower_name VARCHAR(190) NULL,
            loan_date DATETIME NOT NULL,
            due_date DATETIME NULL,
            return_date DATETIME NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            notes TEXT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY game_source_ref (game_source, game_ref)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

function run_boardgame_loans() {
    BoardGame_Loans::get_instance();
}

run_boardgame_loans();
