<?php
/**
 * Plugin Name: Tappy Cashback Pro
 * Description: Sistema profissional de cashback para WooCommerce.
 * Version: 1.1.0
 * Author: Rafael Moreno
 */

if (!defined('ABSPATH')) exit;

define('TAPPY_CB_VERSION', '1.1.0');
define('TAPPY_CB_PATH', plugin_dir_path(__FILE__));
define('TAPPY_CB_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| COMPATIBILIDADE HPOS
|--------------------------------------------------------------------------
*/

add_action('before_woocommerce_init', function () {

    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {

        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );

    }

});


/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

require_once TAPPY_CB_PATH . 'includes/class-install.php';
require_once TAPPY_CB_PATH . 'includes/class-settings.php';
require_once TAPPY_CB_PATH . 'includes/class-database.php';
require_once TAPPY_CB_PATH . 'includes/class-generator.php';
require_once TAPPY_CB_PATH . 'includes/class-antifraud.php';
require_once TAPPY_CB_PATH . 'includes/class-checkout.php';
require_once TAPPY_CB_PATH . 'includes/class-myaccount.php';
require_once TAPPY_CB_PATH . 'includes/class-cron.php';
require_once TAPPY_CB_PATH . 'includes/class-admin-list-table.php';
require_once TAPPY_CB_PATH . 'includes/class-admin-page.php';


/*
|--------------------------------------------------------------------------
| ATIVAÇÃO
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, function () {

    Tappy_CB_Install::install();

    // Disponibiliza os agendamentos personalizados durante a ativação
    add_filter('cron_schedules', function ($schedules) {
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
    });

    $interval = get_option('tappy_cashback_cron_interval', 'daily');
    $allowed = ['hourly', 'three_hours', 'six_hours', 'twelve_hours', 'daily'];

    if (!in_array($interval, $allowed, true)) {
        $interval = 'daily';
    }

    if (!wp_next_scheduled('tappy_cb_daily_expiration')) {

        wp_schedule_event(
            time(),
            $interval,
            'tappy_cb_daily_expiration'
        );

    }

    flush_rewrite_rules();

});


/*
|--------------------------------------------------------------------------
| DESATIVAÇÃO
|--------------------------------------------------------------------------
*/

register_deactivation_hook(__FILE__, function () {

    wp_clear_scheduled_hook('tappy_cb_daily_expiration');

    flush_rewrite_rules();

});


/*
|--------------------------------------------------------------------------
| INICIALIZAÇÃO DO PLUGIN
|--------------------------------------------------------------------------
*/

class Tappy_Cashback_Pro {

    public function __construct() {

        new Tappy_CB_Settings();
        new Tappy_CB_Generator();
        new Tappy_CB_Antifraud();
        new Tappy_CB_Checkout();
        new Tappy_CB_MyAccount();
        new Tappy_CB_Cron();
        new Tappy_CB_Admin_Page();

    }

}

add_action('plugins_loaded', function () {
    new Tappy_Cashback_Pro();
});
