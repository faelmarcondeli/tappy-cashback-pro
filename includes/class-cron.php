<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Cron {

    public function __construct() {

        add_action('tappy_cb_daily_expiration', [$this, 'expire_cashbacks']);

        if (!wp_next_scheduled('tappy_cb_daily_expiration')) {
            wp_schedule_event(time(), 'daily', 'tappy_cb_daily_expiration');
        }
    }

    public function expire_cashbacks() {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $expired_users = $wpdb->get_col("
            SELECT DISTINCT user_id
            FROM $table
            WHERE status = 'available'
            AND expires_at IS NOT NULL
            AND expires_at <= NOW()
        ");

        $wpdb->query("
            UPDATE $table
            SET status = 'expired',
                updated_at = NOW()
            WHERE status = 'available'
            AND expires_at IS NOT NULL
            AND expires_at <= NOW()
        ");

        foreach ($expired_users as $user_id) {
            Tappy_CB_Database::clear_balance_cache($user_id);
        }
    }
}
