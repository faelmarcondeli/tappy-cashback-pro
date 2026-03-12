<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Antifraud {

    public function __construct() {
        add_action('woocommerce_order_status_refunded', [$this, 'cancel_cashback']);
        add_action('woocommerce_order_status_cancelled', [$this, 'cancel_cashback']);
    }

    public function cancel_cashback($order_id) {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        // Identifica usuários afetados para limpar cache de saldo
        $user_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT user_id FROM $table WHERE order_id = %d",
                $order_id
            )
        );

        $wpdb->update(
            $table,
            [
                'status' => 'cancelled',
                'updated_at' => current_time('mysql')
            ],
            ['order_id' => $order_id]
        );

        foreach ($user_ids as $user_id) {
            Tappy_CB_Database::clear_balance_cache($user_id);
        }
    }
}
