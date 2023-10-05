<?php

namespace WPTurbo\App\Core;

class Helper
{
    public static function getUploadDirectoryPath(): string
    {
        // $WPTurboDirectory = wp_upload_dir()['basedir'] . '/wp-turbo';
        $WPTurboDirectory = WP_CONTENT_DIR . '/uploads/wp-turbo';
        if (!file_exists($WPTurboDirectory)) {
            mkdir($WPTurboDirectory, 0777, true);
        }

        return $WPTurboDirectory . '/';
    }

    public static function getUploadDirectoryUrl()
    {
        //return wp_upload_dir()['baseurl'] . '/wp-turbo/';
        return WP_CONTENT_URL . '/uploads/wp-turbo/';
    }
    public static function getSiteBaseUrl(): string
    {
        return preg_replace("(^https?://)", "", get_site_url() );
    }

    public static function getSiteId(): int
    {
        return get_current_blog_id();
    }

    public static function isWooCommerceActive(): bool
    {
        /**
         * Check if WooCommerce is activated
         */
        return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        /*
        if ( ! function_exists( 'is_woocommerce_activated' ) ) {
            function is_woocommerce_activated() {
                return class_exists('WooCommerce');
            }
        }

        return is_woocommerce_activated();
        */
    }
}
