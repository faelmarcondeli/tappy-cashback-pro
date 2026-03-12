<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Checkout {

    private static $balance_cache = [];

    public function __construct() {

        // Gera cashback quando o pedido é concluído
        add_action(
            'woocommerce_order_status_completed',
            [$this, 'handle_order_completed'],
            10,
            1
        );

        add_action(
            'woocommerce_cart_calculate_fees',
            [$this, 'apply_cashback'],
            20
        );

        add_action(
            'woocommerce_checkout_create_order',
            [$this, 'mark_used'],
            20,
            2
        );

        // Mostra cashback previsto abaixo do total do pedido (carrinho e checkout)
        add_action(
            'woocommerce_cart_totals_after_order_total',
            [$this, 'render_future_cashback_total_row']
        );

        add_action(
            'woocommerce_review_order_after_order_total',
            [$this, 'render_future_cashback_total_row']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SALDO COM CACHE EM MEMÓRIA
    |--------------------------------------------------------------------------
    */

    private function get_balance($user_id) {

        if (isset(self::$balance_cache[$user_id])) {
            return self::$balance_cache[$user_id];
        }

        $balance = Tappy_CB_Database::get_balance($user_id);

        self::$balance_cache[$user_id] = $balance;

        return $balance;
    }

    /*
    |--------------------------------------------------------------------------
    | GERAÇÃO DE CASHBACK AO CONCLUIR PEDIDO
    |--------------------------------------------------------------------------
    */

    public function handle_order_completed($order_id) {
        $generator = new Tappy_CB_Generator();
        $generator->generate($order_id);
    }

    /*
    |--------------------------------------------------------------------------
    | APLICAR CASHBACK
    |--------------------------------------------------------------------------
    */

    public function apply_cashback() {

        if (!is_user_logged_in()) {
            return;
        }

        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $user_id = get_current_user_id();

        $balance = $this->get_balance($user_id);

        if ($balance <= 0) {
            return;
        }

        $cart_total = WC()->cart->get_subtotal();

        if ($cart_total <= 0) {
            return;
        }

        $discount = min($balance, $cart_total);

        if ($discount <= 0) {
            return;
        }

        WC()->cart->add_fee(
            __('Cashback aplicado', 'tappy'),
            -$discount
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR CASHBACK FUTURO NO TOTAL DO PEDIDO
    |--------------------------------------------------------------------------
    */

    public function render_future_cashback_total_row() {

        if (get_option('tappy_cashback_enabled') !== 'yes') {
            return;
        }

        $percentage = floatval(get_option('tappy_cashback_percentage'));

        if ($percentage <= 0) {
            return;
        }

        $cart = WC()->cart;
        if (!$cart) {
            return;
        }

        // Usa valor bruto numérico; se zerar, faz fallback para subtotal.
        $order_total = floatval($cart->get_total('edit'));
        if ($order_total <= 0) {
            $order_total = floatval($cart->get_subtotal());
        }

        if ($order_total <= 0) {
            return;
        }

        $future_cashback = ($order_total * $percentage) / 100;

        echo '<tr class="order-total tappy-future-cashback-next">';
        echo '<th>' . esc_html__('Cashback para a próxima compra', 'tappy') . '</th>';
        echo '<td data-title="' . esc_attr__('Cashback para a próxima compra', 'tappy') . '"><strong>' . wc_price($future_cashback) . '</strong></td>';
        echo '</tr>';
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAR CASHBACK COMO USADO
    |--------------------------------------------------------------------------
    */

    public function mark_used($order, $data) {

        if (!is_user_logged_in()) {
            return;
        }

        global $wpdb;

        $table = $wpdb->prefix . 'tappy_cashback';

        $user_id = get_current_user_id();

        $balance = $this->get_balance($user_id);

        if ($balance <= 0) {
            return;
        }

        $remaining = min($balance, $order->get_subtotal());

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE user_id = %d
                 AND status = 'available'
                 AND (expires_at IS NULL OR expires_at > NOW())
                 ORDER BY created_at ASC",
                $user_id
            )
        );

        foreach ($rows as $row) {

            $available = $row->amount - $row->amount_used;

            if ($available <= 0) {
                continue;
            }

            $consume = min($available, $remaining);

            $new_used = $row->amount_used + $consume;

            $status = ($new_used >= $row->amount)
                ? 'used'
                : 'available';

            $wpdb->update(
                $table,
                [
                    'amount_used' => $new_used,
                    'status' => $status,
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $row->id]
            );

            $remaining -= $consume;

            if ($remaining <= 0) {
                break;
            }
        }

        Tappy_CB_Database::clear_balance_cache($user_id);
    }
}
