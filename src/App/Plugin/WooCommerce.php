<?php declare( strict_types=1 );

namespace App\Plugin;

class WooCommerce
{
    private ?string $productCategoryTitle = null;
    private ?string $productCategoryTitleKey = null;
    private ?string $productCategoryDescription = null;
    private ?string $productCategoryDescriptionKey = null;
    private ?string $productCategoryKeywords = null;
    private ?string $productCategoryKeywordsKey = null;

    public function __construct()
    {
        // do nothing
    }

    public function init()
    {
        $this->setVariables();
        $this->setHooks();
    }

    public function setVariables()
    {
        $this->productCategoryTitleKey = '_meta_title';
        $this->productCategoryDescriptionKey = '_meta_description';
        $this->productCategoryKeywordsKey = '_meta_keywords';
    }

    public function setHooks()
    {
        add_action('product_cat_add_form_fields', [$this, 'addProductCategoryCustomFields'], 10, 1);
        add_action('product_cat_edit_form_fields', [$this, 'editProductCategoryCustomFields'], 10, 1);

        add_action('create_product_cat', [$this, 'actionSaveCustomFields'], 10, 1);
        add_action('edited_product_cat', [$this, 'actionSaveCustomFields'], 10, 1);
    }

    //Product Cat Create page
    public function addProductCategoryCustomFields(): void
    {
        ?>
        <div class="form-field">
            <label for="<?php echo $this->productCategoryTitleKey; ?>">Meta Title</label>
            <input type="text" name="<?php echo $this->productCategoryTitleKey; ?>" id="<?php echo $this->productCategoryTitleKey; ?>">
            <p class="description"><?php _e('Enter a meta title, <= 60 character', 'wp-turbo'); ?></p>
        </div>
        <div class="form-field">
            <label for="<?php echo $this->productCategoryDescriptionKey; ?>">Meta Description</label>
            <textarea name="<?php echo $this->productCategoryDescriptionKey; ?>" id="<?php echo $this->productCategoryDescriptionKey; ?>"></textarea>
            <p class="description"><?php _e('Enter a meta description, <= 160 character', 'wp-turbo'); ?></p>
        </div>
        <div class="form-field">
            <label for="<?php echo $this->productCategoryKeywordsKey; ?>">Meta Keywords</label>
            <input type="text" name="<?php echo $this->productCategoryKeywordsKey; ?>" id="<?php echo $this->productCategoryKeywordsKey; ?>">
            <p class="description"><?php _e('Enter a meta title, <= 60 character', 'wp-turbo'); ?></p>
        </div>
        <?php
    }

    //Product Cat Edit page
    public function editProductCategoryCustomFields($term): void
    {
        //getting term ID
        $term_id = $term->term_id;

        // retrieve the existing value(s) for this meta field.
        $_meta_title = get_term_meta($term_id, $this->productCategoryTitleKey, true);
        $_meta_desc = get_term_meta($term_id, $this->productCategoryDescriptionKey, true);
        $_meta_keywords = get_term_meta($term_id, $this->productCategoryKeywordsKey, true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $this->productCategoryTitleKey; ?>">Meta Title</label></th>
            <td>
                <input type="text" name="<?php echo $this->productCategoryTitleKey; ?>" id="<?php echo $this->productCategoryTitleKey; ?>" value="<?php echo esc_attr($_meta_title) ? esc_attr($_meta_title) : ''; ?>">
                <p class="description"><?php _e('Enter a meta title, <= 60 character', 'wp-turbo'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $this->productCategoryDescriptionKey; ?>">Meta Description</label></th>
            <td>
                <textarea name="<?php echo $this->productCategoryDescriptionKey; ?>" id="<?php echo $this->productCategoryDescriptionKey; ?>"><?php echo esc_attr($_meta_desc) ? esc_attr($_meta_desc) : ''; ?></textarea>
                <p class="description"><?php _e('Enter a meta description', 'wp-turbo'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $this->productCategoryKeywordsKey; ?>">Meta Keywords</label></th>
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
        update_term_meta($term_id, $this->productCategoryTitleKey, filter_input(INPUT_POST, $this->productCategoryTitleKey));
        update_term_meta($term_id, $this->productCategoryDescriptionKey, filter_input(INPUT_POST, $this->productCategoryDescriptionKey));
        update_term_meta($term_id, $this->productCategoryKeywordsKey, filter_input(INPUT_POST, $this->productCategoryKeywordsKey));
    }

}
