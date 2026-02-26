<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_MyAccount {

    public function __construct() {
        add_action('init', [$this, 'add_endpoint']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_menu_item']);
        add_action('woocommerce_account_cashback_endpoint', [$this, 'content']);
    }

    public function add_endpoint() {
        add_rewrite_endpoint('cashback', EP_ROOT | EP_PAGES);
    }

    public function add_query_vars($vars) {
        $vars[] = 'cashback';
        return $vars;
    }

    public function add_menu_item($items) {
        $items['cashback'] = 'Cashback';
        return $items;
    }

    private function get_valid_cashback($user_id) {

        $cashbacks = get_user_meta($user_id, '_tappy_cashbacks', true);
        if (!is_array($cashbacks)) return [];

        $valid = [];

        foreach ($cashbacks as $cb) {

            if (!empty($cb['expiration']) && strtotime($cb['expiration']) < time()) {
                continue;
            }

            $valid[] = $cb;
        }

        return $valid;
    }

    public function content() {

        $user_id = get_current_user_id();
        $cashbacks = $this->get_valid_cashback($user_id);

        echo '<h2>Meu Cashback</h2>';

        if (empty($cashbacks)) {
            echo '<p>Nenhum cashback disponível.</p>';
            return;
        }

        echo '<table class="woocommerce-orders-table shop_table shop_table_responsive my_account_orders">';
        echo '<thead>
            <tr>
                <th>Pedido</th>
                <th>Data</th>
                <th>Total do Pedido</th>
                <th>Total do Cashback</th>
                <th>Ações</th>
            </tr>
        </thead><tbody>';

        foreach ($cashbacks as $cb) {

            $order = wc_get_order($cb['order_id']);
            if (!$order) continue;

            echo '<tr>';
            echo '<td>#' . $cb['order_id'] . '</td>';
            echo '<td>' . wc_format_datetime($order->get_date_created()) . '</td>';
            echo '<td>' . wc_price($order->get_total()) . '</td>';
            echo '<td>' . wc_price($cb['amount']) . '</td>';
            echo '<td><a class="woocommerce-button button view" href="' . esc_url($order->get_view_order_url()) . '">Visualizar</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}
