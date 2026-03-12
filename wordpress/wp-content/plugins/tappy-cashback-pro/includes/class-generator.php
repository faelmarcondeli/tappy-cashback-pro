<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Generator {

    public function __construct() {
        // Gera cashback em diferentes ganchos de conclusão
        add_action('woocommerce_order_status_completed', [$this, 'generate'], 10, 1);
        add_action('woocommerce_order_status_processing', [$this, 'generate'], 10, 1);
        add_action('woocommerce_payment_complete', [$this, 'generate'], 10, 1);
    }

    public function generate($order_id) {

        if (get_option('tappy_cashback_enabled') !== 'yes') return;

        /*
        |--------------------------------------------------------------------------
        | LOCK PARA EVITAR DUPLICAÇÃO
        |--------------------------------------------------------------------------
        */

        $lock_key = 'tappy_cb_lock_' . $order_id;

        $lock = wp_cache_add($lock_key, 1, 'tappy_cashback', 30);

        if (!$lock) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        /*
        |--------------------------------------------------------------------------
        | VERIFICA SE JÁ EXISTE CASHBACK PARA O PEDIDO
        |--------------------------------------------------------------------------
        */

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE order_id = %d",
                $order_id
            )
        );

        if ($exists) {
            wp_cache_delete($lock_key, 'tappy_cashback');
            return;
        }

        $order = wc_get_order($order_id);

        if (!$order || !$order->get_user_id()) {
            wp_cache_delete($lock_key, 'tappy_cashback');
            return;
        }

        $percentage = floatval(get_option('tappy_cashback_percentage'));

        if ($percentage <= 0) {
            wp_cache_delete($lock_key, 'tappy_cashback');
            return;
        }

        $amount = ($order->get_total() * $percentage) / 100;

        $expires = null;
        $days = get_option('tappy_cashback_expiration');

        if ($days) {
            $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        $wpdb->insert($table, [
            'user_id' => $order->get_user_id(),
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => 'available',
            'expires_at' => $expires,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | LIMPA CACHE DO SALDO
        |--------------------------------------------------------------------------
        */

        Tappy_CB_Database::clear_balance_cache($order->get_user_id());

        /*
        |--------------------------------------------------------------------------
        | REMOVE LOCK
        |--------------------------------------------------------------------------
        */

        wp_cache_delete($lock_key, 'tappy_cashback');
    }
}
