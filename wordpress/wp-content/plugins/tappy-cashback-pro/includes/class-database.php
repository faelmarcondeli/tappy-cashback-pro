<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Database {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'tappy_cashback';
    }

    public static function get_balance($user_id) {

        $cache_key = 'tappy_cb_balance_' . $user_id;

        $cached = wp_cache_get($cache_key, 'tappy_cashback');
        if ($cached !== false) {
            return $cached;
        }

        global $wpdb;
        $table = self::table();

        $balance = $wpdb->get_var(
            $wpdb->prepare("
                SELECT SUM(amount - amount_used)
                FROM $table
                WHERE user_id = %d
                AND status = 'available'
                AND (expires_at IS NULL OR expires_at > NOW())
            ", $user_id)
        );

        $balance = floatval($balance);

        wp_cache_set($cache_key, $balance, 'tappy_cashback', 300);

        return $balance;
    }

    public static function get_history($user_id) {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE user_id = %d
                 ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    public static function clear_balance_cache($user_id) {
        wp_cache_delete('tappy_cb_balance_' . $user_id, 'tappy_cashback');
    }
}
