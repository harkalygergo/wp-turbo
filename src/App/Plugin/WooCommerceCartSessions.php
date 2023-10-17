<?php declare( strict_types=1 );

namespace WPTurbo\App\Plugin;


use WPTurbo\App\Core\Helper;

class WooCommerceCartSessions extends \WPTurbo
{
    public function __construct()
    {
        // do nothing
        parent::__construct();
    }


    public function init()
    {
        if (Helper::isWooCommerceActive()) {
            $this->setHooks();
        }
    }

    public function setHooks()
    {
        add_action( 'admin_menu', [$this, 'add_abandoned_carts_submenu']);
    }

    public function add_abandoned_carts_submenu()
    {
        add_submenu_page('wp-turbo', 'WooCommerce '.__('Sessions'), 'WC '.__('Sessions'), 'manage_options', 'woocommerce-sessions', [$this, 'abandonedCartsPageContent']);
    }

    public function abandonedCartsPageContent()
    {
        ?>
        <div class="wrap">
            <h1><?php echo 'WooCommerce '.__('Sessions'); ?></h1>
            <?php
            $cartSessions = $this->getSessions();
            if ( $cartSessions ) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead>
                        <tr>
                            <th># / ID</th>
                            <th>'.__('User').'</th>
                            <th>'.__('Expiry date', 'woocommerce').'</th>
                            <th>'.__('Subtotal', 'woocommerce').'</th>
                            <th>'.__('Coupon', 'woocommerce').'</th>
                            <th>'.__('Products', 'woocommerce').'</th>
                        </tr>
                      </thead>
                      <tbody>
                ';
                $i = 0;

                foreach ($cartSessions as $cart) {
                    $session_data = maybe_unserialize($cart->session_value);
                    $cartTotals = isset($session_data['cart_totals']) ? maybe_unserialize($session_data['cart_totals']) : [];

                    if (!empty($cartTotals)) {
                        $cartSubtotal = $cartTotals['subtotal'] + $cartTotals['subtotal_tax'];
                        $cart_products = $session_data['cart'] ?? [];

                        $user = $this->getUser($cart);
                        if (is_null($user)) {
                            continue;
                        }

                        $applied_coupons = '';
                        if (isset($session_data['applied_coupons'])) {
                            $applied_coupons = maybe_unserialize($session_data['applied_coupons']);
                            if (is_array($applied_coupons)) {
                                $applied_coupons = implode(', ', $applied_coupons);
                            }
                        }

                        echo '<tr>';
                        echo '<td>' . ++$i. '. / ' . $cart->session_id. '</td>';
                        echo '<td>' . $user. '</td>';
                        echo '<td>' . date('Y-m-d H:i:s', (int)$cart->session_expiry) . '</td>';
                        echo '<td>' . $cartSubtotal. '</td>';
                        echo '<td>' . $applied_coupons. '</td>';
                        echo '<td>';
                        if (!empty($cart_products)) {
                            $cart_products = maybe_unserialize($cart_products);
                            echo '<ul>';
                            foreach ($cart_products as $cartItem) {
                                $product = $this->getProduct($cartItem);
                                echo '<li><a target="_blank" href="'.$product->get_permalink().'">'.$product->get_name().' ('.__('quantity', 'paperstories-plugin').': '.$cartItem['quantity'].')</a></li>';
                            }
                            echo '</ul>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                echo '</tbody>';
                echo '</table>';
            } ?>
        </div>
    <?php }

    private function getUser(object $cart): ?string
    {
        $user = $cart->session_key;
        // if $user is integer, get user display name and create a link to the user's profile
        if (is_numeric($user)) {
            $userObject = get_user_by('id', $user);
            // exit if user is administrator
            if (in_array('administrator', $userObject->roles)) {
                return null;
            }

            $user = '<a href="' . esc_url(get_edit_user_link($userObject->ID)) . '">' . esc_html($userObject->display_name) . '</a>';
            $user .= ' (<a target="_blank" href="/wp-admin/edit.php?post_type=shop_order&_customer_user='.$userObject->ID.'">' . __('Orders', 'woocommerce') . '</a>)';
        }

        return $user;
    }

    private function getSessions(): array
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_sessions';
        $query      = "SELECT * FROM $table_name ORDER BY session_expiry DESC";

        return $wpdb->get_results( $query );
    }

    private function getProduct(array $cartItem): object
    {
        if ($cartItem['variation_id']) {
            return wc_get_product($cartItem['variation_id']);
        }

        return wc_get_product($cartItem['product_id']);
    }

}

