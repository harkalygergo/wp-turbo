<?php declare( strict_types=1 );

namespace WPTurbo\App\Plugin;

use WPTurbo\App\Core\Dashboard;

class WooCommerce
{
    private ?string $productCategoryDescriptionKey = null;
    private ?string $productCategoryKeywordsKey = null;

    public function __construct()
    {
        // do nothing
    }


    public function init()
    {
        if ($this->is_woocommerce_activated()) {
            $this->setVariables();
            $this->setHooks();
        }
    }

    public function setVariables()
    {
        $this->productCategoryDescriptionKey = '_meta_description';
        $this->productCategoryKeywordsKey = '_meta_keywords';
    }

    /**
     * Check if WooCommerce is activated
     */
    private function is_woocommerce_activated(): bool
    {
        if ( ! function_exists( 'is_woocommerce_activated' ) ) {
            function is_woocommerce_activated()
            {
                return class_exists( 'woocommerce' );
            }
        }

        return is_woocommerce_activated();
    }

    public function setHooks()
    {
        if (is_admin()) {
            add_action( 'admin_init', [$this, 'addSettingsOptions'] );
        }

        add_action('product_cat_add_form_fields', [$this, 'addProductCategoryCustomFields'], 10, 1);
        add_action('product_cat_edit_form_fields', [$this, 'editProductCategoryCustomFields'], 10, 1);

        add_action('create_product_cat', [$this, 'actionSaveCustomFields'], 10, 1);
        add_action('edited_product_cat', [$this, 'actionSaveCustomFields'], 10, 1);
        add_action( 'wp_head', [$this, 'addProductCategoryDescriptionAndKeywords'], 2);

        if (isset(Dashboard::getOptions()['removeWooCommerceBlocksStylesAndScripts']) && Dashboard::getOptions()['removeWooCommerceBlocksStylesAndScripts'] === "true") {
            add_action( 'init', [$this, 'disableWpBlocksCSS'], 100 );
            add_action( 'init', [$this, 'disableWpBlocksJS'], 100 );
        }
    }

    public function addSettingsOptions()
    {
        add_settings_field(
            'removeWooCommerceBlocksStylesAndScripts',
            'Remove wp-blocks styles and scripts?',
            [Dashboard::class, 'generateFormSelect'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce',
            ['name' => 'removeWooCommerceBlocksStylesAndScripts']
        );
    }

    public function addProductCategoryDescriptionAndKeywords()
    {
        /** @var \WP_Term $currentCategory */
        $currentCategory = get_queried_object();

        if (is_product_category()) {
            //echo '<meta name="description" content="'.get_term_meta($currentCategory->term_id, $this->productCategoryDescriptionKey, true).'" />'."\n";
            $keywords = get_term_meta($currentCategory->term_id, $this->productCategoryKeywordsKey, true);
            if ($keywords) {
                echo '<meta name="keywords" content="'.$keywords.'" />'."\n";
            }
        }
    }

    //Product Cat Create page
    public function addProductCategoryCustomFields(): void
    {
        ?>
        <div class="form-field">
            <label for="<?php echo $this->productCategoryDescriptionKey; ?>">Meta Description (wpt)</label>
            <input type="text" name="<?php echo $this->productCategoryDescriptionKey; ?>" id="<?php echo $this->productCategoryDescriptionKey; ?>">
            <p class="description"><?php _e('Enter a meta description, <= 130-160 character', 'wp-turbo'); ?></p>
        </div>
        <div class="form-field">
            <label for="<?php echo $this->productCategoryKeywordsKey; ?>">Meta Keywords (wpt)</label>
            <input type="text" name="<?php echo $this->productCategoryKeywordsKey; ?>" id="<?php echo $this->productCategoryKeywordsKey; ?>">
            <p class="description"><?php _e('Enter comma separated keywords', 'wp-turbo'); ?></p>
        </div>
        <?php
    }

    //Product Cat Edit page
    public function editProductCategoryCustomFields($term): void
    {
        //getting term ID
        $term_id = $term->term_id;

        // retrieve the existing value(s) for this meta field.
        $_meta_desc = get_term_meta($term_id, $this->productCategoryDescriptionKey, true);
        $_meta_keywords = get_term_meta($term_id, $this->productCategoryKeywordsKey, true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $this->productCategoryDescriptionKey; ?>">Meta Description (wpt)</label></th>
            <td>
                <input type="text" name="<?php echo $this->productCategoryDescriptionKey; ?>" id="<?php echo $this->productCategoryDescriptionKey; ?>" value="<?php echo esc_attr($_meta_desc) ? esc_attr($_meta_desc) : ''; ?>">
                <p class="description"><?php _e('Enter a meta description, <= 130-160 character', 'wp-turbo'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $this->productCategoryKeywordsKey; ?>">Meta Keywords (wpt)</label></th>
            <td>
                <input type="text" name="<?php echo $this->productCategoryKeywordsKey; ?>" id="<?php echo $this->productCategoryKeywordsKey; ?>" value="<?php echo esc_attr($_meta_keywords) ? esc_attr($_meta_keywords) : ''; ?>">
                <p class="description"><?php _e('Enter comma separated keywords', 'wp-turbo'); ?></p>
            </td>
        </tr>
        <?php
    }

    // Save extra taxonomy fields callback function.
    public function actionSaveCustomFields($term_id): void
    {
        update_term_meta($term_id, $this->productCategoryDescriptionKey, filter_input(INPUT_POST, $this->productCategoryDescriptionKey));
        update_term_meta($term_id, $this->productCategoryKeywordsKey, filter_input(INPUT_POST, $this->productCategoryKeywordsKey));
    }

    public function disableWpBlocksCSS()
    {
        $WooCommerceStyles = [
            'wp-block-library',
            'wc-blocks-style',
            'wc-blocks-style-active-filters',
            'wc-blocks-style-add-to-cart-form',
            'wc-blocks-packages-style',
            'wc-blocks-style-all-products',
            'wc-blocks-style-all-reviews',
            'wc-blocks-style-attribute-filter',
            'wc-blocks-style-breadcrumbs',
            'wc-blocks-style-catalog-sorting',
            'wc-blocks-style-customer-account',
            'wc-blocks-style-featured-category',
            'wc-blocks-style-featured-product',
            'wc-blocks-style-mini-cart',
            'wc-blocks-style-price-filter',
            'wc-blocks-style-product-add-to-cart',
            'wc-blocks-style-product-button',
            'wc-blocks-style-product-categories',
            'wc-blocks-style-product-image',
            'wc-blocks-style-product-image-gallery',
            'wc-blocks-style-product-query',
            'wc-blocks-style-product-results-count',
            'wc-blocks-style-product-reviews',
            'wc-blocks-style-product-sale-badge',
            'wc-blocks-style-product-search',
            'wc-blocks-style-product-sku',
            'wc-blocks-style-product-stock-indicator',
            'wc-blocks-style-product-summary',
            'wc-blocks-style-product-title',
            'wc-blocks-style-rating-filter',
            'wc-blocks-style-reviews-by-category',
            'wc-blocks-style-reviews-by-product',
            'wc-blocks-style-product-details',
            'wc-blocks-style-single-product',
            'wc-blocks-style-stock-filter',
            'wc-blocks-style-cart',
            'wc-blocks-style-checkout',
            'wc-blocks-style-mini-cart-contents',
            'classic-theme-styles-inline'
        ];

        foreach ( $WooCommerceStyles as $WooCommerceStyle ) {
            wp_deregister_style( $WooCommerceStyle );
        }
    }

    public function disableWpBlocksJS()
    {
        $WooCommerceScripts = [
            'wc-blocks-middleware',
            'wc-blocks-data-store'
        ];

        foreach ( $WooCommerceScripts as $WooCommerceScript ) {
            wp_deregister_script( $WooCommerceScript );
        }
    }
}
