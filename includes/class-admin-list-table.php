<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Tappy_CB_List_Table extends WP_List_Table {

    public function get_columns() {
        return [
            'id' => 'ID',
            'user' => 'Usuário',
            'order' => 'Pedido',
            'amount' => 'Valor',
            'used' => 'Usado',
            'status' => 'Status',
            'expires_at' => 'Expira em',
            'created_at' => 'Criado em',
        ];
    }

    public function no_items() {
        echo 'Nenhum cashback encontrado.';
    }

    public function column_id($item) {
        return '#' . intval($item->id);
    }

    public function column_user($item) {
        $user = get_userdata($item->user_id);
        return $user ? esc_html($user->user_email) : '-';
    }

    public function column_order($item) {
        $url = admin_url('post.php?post=' . $item->order_id . '&action=edit');
        return '<a href="' . esc_url($url) . '">#' . intval($item->order_id) . '</a>';
    }

    public function column_amount($item) {
        return wc_price($item->amount);
    }

    public function column_used($item) {
        return wc_price($item->amount_used);
    }

    public function column_status($item) {
        $map = [
            'available' => 'Disponível',
            'used'      => 'Utilizado',
            'expired'   => 'Expirado',
            'cancelled' => 'Cancelado',
        ];
        return esc_html($map[$item->status] ?? $item->status);
    }

    public function column_expires_at($item) {
        if (empty($item->expires_at)) {
            return '—';
        }
        $dt = wc_string_to_datetime($item->expires_at);
        return $dt ? wc_format_datetime($dt) : esc_html($item->expires_at);
    }

    public function column_created_at($item) {
        $dt = wc_string_to_datetime($item->created_at);
        return $dt ? wc_format_datetime($dt) : esc_html($item->created_at);
    }

    public function prepare_items() {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $per_page = 20;
        $paged = $this->get_pagenum();

        $offset = ($paged - 1) * $per_page;

        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 ORDER BY created_at DESC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $this->_column_headers = [$this->get_columns(), [], []];

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $per_page,
        ]);
    }
}
