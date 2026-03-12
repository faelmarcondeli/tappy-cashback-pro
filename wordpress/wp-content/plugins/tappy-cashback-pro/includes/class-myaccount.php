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

    private function get_cashbacks($user_id) {
        return Tappy_CB_Database::get_history($user_id);
    }

    public function content() {

        $user_id = get_current_user_id();
        $cashbacks = $this->get_cashbacks($user_id);

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
                <th>Valor</th>
                <th>Usado</th>
                <th>Status</th>
                <th>Expira em</th>
                <th>Ações</th>
            </tr>
        </thead><tbody>';

        foreach ($cashbacks as $cb) {

            $order = wc_get_order($cb->order_id);
            if (!$order) continue;

            $expires = $cb->expires_at
                ? wc_format_datetime(wc_string_to_datetime($cb->expires_at))
                : '—';

            $status_labels = array(
                'available' => 'Disponível',
                'used'      => 'Utilizado',
                'expired'   => 'Expirado',
                'cancelled' => 'Cancelado',
            );

            $status_label = $status_labels[$cb->status] ?? $cb->status;

            echo '<tr>';
            echo '<td>#' . esc_html($cb->order_id) . '</td>';
            echo '<td>' . esc_html(wc_format_datetime($order->get_date_created())) . '</td>';
            echo '<td>' . wc_price($cb->amount) . '</td>';
            echo '<td>' . wc_price($cb->amount_used) . '</td>';
            echo '<td>' . esc_html($status_label) . '</td>';
            echo '<td>' . esc_html($expires) . '</td>';
            echo '<td><a class="woocommerce-button button view" href="' . esc_url($order->get_view_order_url()) . '">Visualizar</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}
