<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Cron {

    public function __construct() {

        add_filter('cron_schedules', [$this, 'add_schedules']);
        add_action('tappy_cb_daily_expiration', [$this, 'expire_cashbacks']);

        $this->ensure_schedule();
    }

    private function get_interval_slug() {
        $option = get_option('tappy_cashback_cron_interval', 'daily');
        $allowed = ['hourly', 'three_hours', 'six_hours', 'twelve_hours', 'daily'];
        return in_array($option, $allowed, true) ? $option : 'daily';
    }

    public function add_schedules($schedules) {

        $schedules['three_hours'] = array(
            'interval' => 3 * HOUR_IN_SECONDS,
            'display'  => 'A cada 3 horas'
        );

        $schedules['six_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => 'A cada 6 horas'
        );

        $schedules['twelve_hours'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => 'A cada 12 horas'
        );

        return $schedules;
    }

    private function ensure_schedule() {

        $interval = $this->get_interval_slug();
        $current = wp_get_schedule('tappy_cb_daily_expiration');

        if ($current !== $interval) {
            wp_clear_scheduled_hook('tappy_cb_daily_expiration');
        }

        if (!wp_next_scheduled('tappy_cb_daily_expiration')) {
            wp_schedule_event(time(), $interval, 'tappy_cb_daily_expiration');
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
