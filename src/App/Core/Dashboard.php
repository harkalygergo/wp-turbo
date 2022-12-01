<?php declare( strict_types=1 );

namespace App\Core;

class Dashboard
{
    private array $config = [];
    private string $menuTitle = 'WP Turbo';
    private string $menuSlug = 'wp-turbo';
    private array|bool $options = false;
    private string $optionGroupName = 'wp-turbo-options-group';
    private string $optionName = 'wp-turbo-options';

    public function __construct()
    {
        // do nothing
    }

    public function init(array $config=[]): void
    {
        $this->config = $config;
        $this->initVariables();
        $this->initFrontend();
        $this->initBackend();
    }

    private function initVariables(): void
    {
        //$this->options = get_option( $this->menuSlug );
        $this->options = get_option( $this->optionName );
    }

    private function initFrontend(): void
    {
        if (!is_admin()) {

            if (isset($this->options['enableSearchBySku']) && $this->options['enableSearchBySku'] === "true") {
                add_filter( 'posts_search', [$this, 'searchBySku'], 999, 2 );
            }

            if (isset($this->options['excludeFeaturedProductsFromLoop']) && $this->options['excludeFeaturedProductsFromLoop'] === "true") {
                add_action( 'woocommerce_product_query', [$this, 'excludeFeaturedProductsFromLoop']);
            }
        }
    }

    public function excludeFeaturedProductsFromLoop($query)
    {
        if ( ! is_admin() && $query->is_main_query() ) {
            // Not a query for an admin page.
            // It's the main query for a front end page of your site.

            if ( is_product_category() ) {
                // It's the main query for a product category archive.
                $tax_query = (array) $query->get( 'tax_query' );

                // Tax query to exclude featured product
                $tax_query[] = array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'featured',
                    'operator' => 'NOT IN',
                );

                $query->set( 'tax_query', $tax_query );
            }

        }
    }

    public function searchBySku( $search, $query_vars )
    {
        global $wpdb;
        if(isset($query_vars->query['s']) && !empty($query_vars->query['s'])) {
            $posts = get_posts([
                'posts_per_page'  => -1,
                'post_type'       => ['product', 'product_variation'],
                'meta_query' => [
                    [
                        'key' => '_sku',
                        'value' => $query_vars->query['s'],
                        'compare' => 'LIKE'
                    ]
                ]
            ]);

            if(empty($posts)) return $search;

            $get_post_ids = [];

            foreach($posts as $post) {
                if ($post->post_parent!==0) {
                    $get_post_ids[] = $post->post_parent;
                } else {
                    $get_post_ids[] = $post->ID;
                }
            }

            if(sizeof( $get_post_ids ) > 0 ) {
                $search = str_replace( 'AND (((', "AND ((({$wpdb->posts}.ID IN (" . implode( ',', $get_post_ids ) . ")) OR (", $search);
            }
        }

        return $search;
    }

    private function initBackend(): void
    {
        if (is_admin()) {
            add_filter( 'pre_option_link_manager_enabled', '__return_true' );
            add_action( 'admin_menu', [$this, 'addAdminMenu']);
            add_action( 'admin_init', [$this, 'adminPageInit'] );
            add_action( 'wp_dashboard_setup', [$this, 'addBuyMeCoffeeWidget'] );
        }
    }

    public function addBuyMeCoffeeWidget()
    {
        wp_add_dashboard_widget(
            'wp-turbo-coffee-widget',
            'Technical support',
            [$this, 'addBuyMeCoffeeWidgetContent'],
            null,
            null,
            'column3'
        );
    }

    public function addBuyMeCoffeeWidgetContent()
    {
        ?>
        <style>
            #wpTurboBuyMeCoffee {
                --img-width: 120px;
            }
            #wpTurboBuyMeCoffee div.description div {
                float: left;
                margin-top: 5px;
                width: calc(100% - var(--img-width));
            }
            #wpTurboBuyMeCoffee img {
                float: right;
                width: var(--img-width);
                height: auto;
                filter: grayscale(0.5);
            }
        </style>
        <div id="wpTurboBuyMeCoffee" class="dashboard-widget-finish-setup" data-current-step="4" data-total-steps="6">
            <div class="description">
                <div>
                    <strong>
                        <?php echo $this->config['contactName']; ?>
                        <br><small><?php echo $this->config['contactPosition']; ?></small>
                    </strong>
                    <!--br><small>PHP web developer</small-->
                    <br><?php echo $this->config['contactPhone']; ?>
                    <br><a href="mailto:<?php echo $this->config['contactEmail']; ?>" target="_blank"><?php echo $this->config['contactEmail']; ?></a>
                    <br><a href="<?php echo $this->config['contactWebsite']; ?>" target="_blank"><?php echo $this->config['contactWebsite']; ?></a>
                    <?php if($this->config['contactSupportLink']!=='') : ?>
                        <div>
                            <a target="_blank" href="<?php echo $this->config['contactSupportLink']; ?>" class="button button-secondary button-small">
                                <span class="dashicons dashicons-coffee"></span> buy me a coffee
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <img src="https://www.harkalygergo.hu/media/uploads/hosts/www-harkalygergo.hu/622fb3a307872099393996.jpg" alt="Harkály Gergő">
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    public function addAdminMenu()
    {
        // add top level menu page
        add_menu_page($this->menuTitle, $this->menuTitle,'manage_options', $this->menuSlug, [$this, 'adminPageHtml'], 'dashicons-superhero',0);

        // add submenus
        add_submenu_page($this->menuSlug, 'WP blocks', 'WP ' . __('Blocks'), 'manage_options', 'edit.php?post_type=wp_block');
        add_submenu_page($this->menuSlug, 'WC product import', 'WC import', 'manage_options', 'edit.php?post_type=product&page=product_importer');
        add_submenu_page($this->menuSlug, 'WC product export', 'WC export', 'manage_options', 'edit.php?post_type=product&page=product_exporter');
        add_submenu_page($this->menuSlug, 'WP options', 'WP options', 'manage_options', 'options.php');
    }

    /**
     * Register and add settings
     */
    public function adminPageInit()
    {
        register_setting($this->optionGroupName, $this->optionName, [$this, 'sanitize']);

        add_settings_section(
            'wp-turbo-settings-contact', // ID
            'Contact', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'phone',
            'Phone',
            [$this, 'generateFormInput'],
            'my-setting-admin',
            'wp-turbo-settings-contact',
            ['type'=>'tel', 'name' => 'phone']
        );

        add_settings_field(
            'facebookURL',
            'Facebook URL',
            [$this, 'generateFormInput'],
            'my-setting-admin',
            'wp-turbo-settings-contact',
            ['type'=>'url', 'name' => 'facebookURL']
        );

        add_settings_field(
            'instagramURL',
            'Instagram URL',
            [$this, 'generateFormInput'],
            'my-setting-admin',
            'wp-turbo-settings-contact',
            ['type'=>'url', 'name' => 'instagramURL']
        );

        add_settings_section(
            'wp-turbo-settings-woocommerce', // ID
            'WooCommerce', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'enableSearchBySku',
            'SKU search is enabled?',
            [$this, 'generateFormSelect'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce',
            ['name' => 'enableSearchBySku']
        );

        add_settings_field(
            'excludeFeaturedProductsFromLoop',
            'Exclude featured products from products loop?',
            [$this, 'generateFormSelect'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce',
            ['name' => 'excludeFeaturedProductsFromLoop']
        );
    }

    public function generateFormInput($args)
    {
        $fieldValue = $this->options[$args['name']];

        printf(
            '<input type="%s" id="%s" name="%s" value="%s" />',
            $args['type'],
            $this->optionName.'['.$args['name'].']',
            $this->optionName.'['.$args['name'].']',
            isset( $fieldValue ) ? $fieldValue : ''
        );
    }










    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = $input;
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['enableSearchBySku'] ) )
            $new_input['enableSearchBySku'] = sanitize_text_field( $input['enableSearchBySku'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function generateFormSelect($args)
    {
        $input = '';

        $options = [
            $args['name'] => ['false', 'true'],
        ];

        foreach ($options as $optionKey => $optionValue) {
            $input .= sprintf('<select name="%s" id="%s">', $this->optionName.'['.$optionKey.']', $this->optionName.'['.$optionKey.']');
            foreach($optionValue as $option) {
                $input .= sprintf('<option value="%s" %s>%s</option>',
                    $option,
                    (isset($this->options[$optionKey]) && $this->options[$optionKey]===$option) ? 'selected' : '',
                    $option
                );
            }
            $input .= '</select>';

            //$html .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $optionKey, $input);
        }

        echo $input;


        /*
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
        */
    }



















    private function showDashboardHTML($content)
    { ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php echo $content; ?>
        </div>
    <?php }

    public function adminPageHtml()
    {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // get the active tab from the $_GET param
        $tab = $_GET['tab'] ?? null;
        ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
            <!-- Print the page title -->
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <!-- Here are our tabs -->
            <nav class="nav-tab-wrapper">
                <?php
                $wpTurboMenus = [
                    null            => $this->menuTitle,
                    'documentation' => 'Documentation',
                    'log'           => 'Log',
                    'readme'        => 'ReadMe',
                    'phpinfo'       => 'PhpInfo',
                ];
                foreach($wpTurboMenus as $wpTurboMenuKey => $wpTurboMenuValue) {
                    echo sprintf('<a href="?page=%s&tab=%s" class="nav-tab %s">%s</a>',
                     $this->menuSlug,
                     (string) $wpTurboMenuKey,
                     ($tab===$wpTurboMenuKey) ? 'nav-tab-active' : '',
                    $wpTurboMenuValue
                    );
                }
                ?>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'readme':
                        echo '<script type="module" src="https://md-block.verou.me/md-block.js"></script>';
                        echo '<md-block>';
                        include_once __DIR__.'/../../../README.md';
                        echo '</md-block>';
                        break;
                    case 'documentation':
                        include_once (__DIR__.'/Documentation.php');
                        break;
                    case 'log':
                        include_once (__DIR__.'/../../../log/upgrader_process_complete.csv');
                        break;
                    case 'phpinfo':
                        echo '<style>table, th, td { border: 1px solid;}</style>';
                        ob_start();
                        phpinfo();
                        echo explode('</body>', explode('<body>', trim (ob_get_clean ()))['1'])['0'];
                        break;
                    default:
                        // Set class property
                        $this->options = get_option( $this->optionName );
                        ?>
                        <div class="wrap">
                            <form method="post" action="options.php">
                                <?php
                                // This prints out all hidden setting fields
                                settings_fields( $this->optionGroupName );
                                do_settings_sections( 'my-setting-admin' );
                                submit_button();
                                ?>
                            </form>
                        </div>
                        <?php

                        break;
                endswitch; ?>
            </div>
        </div>
        <?php
    }
}
