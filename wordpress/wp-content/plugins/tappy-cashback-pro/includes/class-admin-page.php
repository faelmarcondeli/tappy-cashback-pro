<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Admin_Page {

    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
    }

    public function menu() {
        add_submenu_page(
            'woocommerce',
            'Cashback',
            'Cashback',
            'manage_woocommerce',
            'tappy-cashback',
            [$this, 'render']
        );
    }

    public function render() {

        echo '<div class="wrap"><h1>Cashback</h1>';

        $table = new Tappy_CB_List_Table();
        $table->prepare_items();
        $table->display();

        echo '</div>';
    }
}
