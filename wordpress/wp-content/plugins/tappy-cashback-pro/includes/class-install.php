<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Install {

    public static function install() {

        global $wpdb;

        $table = $wpdb->prefix . 'tappy_cashback';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            order_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(15,4) NOT NULL,
            amount_used DECIMAL(15,4) DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'available',
            expires_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
        
            PRIMARY KEY (id),
        
            KEY user_id (user_id),
            UNIQUE KEY order_id (order_id),
            KEY status (status)
        
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
