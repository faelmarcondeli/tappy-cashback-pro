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

        $wpdb->update(
            $table,
            [
                'status' => 'cancelled',
                'updated_at' => current_time('mysql')
            ],
            ['order_id' => $order_id]
        );
    }
}
