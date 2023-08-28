<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

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

    public static function getOptions(): array
    {
        $dashboard = new Dashboard();
        $dashboard->initVariables();

        return $dashboard->options;
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
        add_action('login_head', [$this, 'action_login_head']);
        if (is_admin()) {
            add_filter( 'mime_types', [$this, 'modifyUploadMimeTypes'] );
            add_action( 'admin_menu', [$this, 'addAdminMenu']);
            add_action( 'admin_init', [$this, 'adminPageInit'] );
        }
    }

    /*
     * @link https://developer.wordpress.org/reference/hooks/mime_types/
     */
    public function modifyUploadMimeTypes($mimeTypes): array
    {
        $mimeTypes['csv'] = 'text/csv';

        return $mimeTypes;
    }

    public function action_login_head()
    {
        // custom login
        ?>
        <style>
            body.login { background-image:url("<?php echo $this->config['pluginURL']; ?>src/img/bg<?php echo date('m'); ?>.jpg"); -webkit-background-size: cover; background-size: cover; }
            body.login h1 { display:none; }
            body.login div#login form#loginform { border-radius:5px; }
            body.login p#nav a, body.login p#backtoblog a { background-color:white; padding:5px; border-radius:5px; }
        </style>
    <?php }

    public function addAdminMenu()
    {
        // add top level menu page
        add_menu_page($this->menuTitle, $this->menuTitle,'manage_options', $this->menuSlug, [$this, 'adminPageHtml'], 'dashicons-superhero',0);

        // add submenus
        add_submenu_page($this->menuSlug, 'Style CSS', 'Style CSS', 'manage_options', 'style-css', [$this, 'adminCss']);
        add_submenu_page($this->menuSlug, 'WP Turbo / Scripts', 'Scripts', 'manage_options', 'wp-turbo-scripts', [Scripts::class, 'admin_page_html']);
        add_submenu_page($this->menuSlug, 'Editor: style.css', 'Editor: style.css', 'manage_options', (is_multisite()?'/network':'').'/plugin-editor.php?file=wp-turbo/local/style.css&plugin=wp-turbo/wp-turbo.php');
        add_submenu_page($this->menuSlug, 'Editor: script.js', 'Editor: script.js', 'manage_options', (is_multisite()?'/network':'').'/plugin-editor.php?file=wp-turbo/local/script.js&plugin=wp-turbo/wp-turbo.php');
        add_submenu_page($this->menuSlug, 'WP blocks', 'WP ' . __('Blocks'), 'manage_options', 'edit.php?post_type=wp_block');
        add_submenu_page($this->menuSlug, 'WP options', 'WP options', 'manage_options', 'options.php');
        add_submenu_page($this->menuSlug, 'WC product import', 'WC import', 'manage_options', 'edit.php?post_type=product&page=product_importer');
        add_submenu_page($this->menuSlug, 'WC product export', 'WC export', 'manage_options', 'edit.php?post_type=product&page=product_exporter');
    }

    public function adminCss()
    {
        // https://css-tricks.com/creating-an-editable-textarea-that-supports-syntax-highlighted-code/
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wpturbo-css' );
                do_settings_sections( 'admin-css-textarea' );
                submit_button();
                ?>
            </form>
        </div>

    <?php }

    /**
     * Register and add settings
     */
    public function adminPageInit()
    {
        // TODO még nincs kész
        register_setting('wpturbo-css', 'wpturbocss');
        add_settings_section(
            'css-textarea', // ID
            'CSS', // Title
            array( $this, 'print_section_info' ), // Callback
            'admin-css-textarea' // Page
        );

        add_settings_field(
            'wp-style',
            'Style',
            [$this, 'generateFormTextarea'],
            'admin-css-textarea',
            'css-textarea',
            ['name' => 'cscs']
        );




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

    public static function generateFormInput($args)
    {
        $dashboard = new Dashboard();
        $dashboard->initVariables();
        printf(
            '<input type="%s" id="%s" name="%s" value="%s" />',
            $args['type'],
            $dashboard->optionName.'['.$args['name'].']',
            $dashboard->optionName.'['.$args['name'].']',
            $dashboard->options[$args['name']] ?? ''
        );
    }

    public function generateFormTextarea($args)
    {
        $fieldValue = $this->options[$args['name']];

        printf(
            '<textarea id="%s" name="%s" rows="10" style="%s">%s</textarea>',
            $this->optionName.'['.$args['name'].']',
            $this->optionName.'['.$args['name'].']',
            'width:100%',
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
    public static function generateFormSelect($args)
    {
        $dashboard = new Dashboard();
        $dashboard->initVariables();
        $input = '';

        $options = [
            $args['name'] => ['false', 'true'],
        ];

        foreach ($options as $optionKey => $optionValue) {
            $input .= sprintf('<select name="%s" id="%s">', $dashboard->optionName.'['.$optionKey.']', $dashboard->optionName.'['.$optionKey.']');
            foreach($optionValue as $option) {
                $input .= sprintf('<option value="%s" %s>%s</option>',
                    $option,
                    (isset($dashboard->options[$optionKey]) && $dashboard->options[$optionKey]===$option) ? 'selected' : '',
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
                    'printbox'      => 'Printbox',
                    'documentation' => 'Documentation',
                    'log'           => 'Log',
                    'readme'        => 'ReadMe',
                    'phpinfo'       => 'PhpInfo',
                    'credits'       => 'Credits',
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
                    case 'printbox':
                    { ?>
                        <p><a class="button button-primary" href="https://paperstories-eu-pbx2.getprintbox.com/pb-admin/order/?per-page=500&page=&o=-create_time" target="_blank">1. Check highest order ID</a></p>
                        <form method="POST" action="/wp-json/printbox/actions/5/91ab845d-4c0c-4126-b76a-4fe20e28a09e/bulk/" target="_blank">
                            <input type="hidden" name="bulk" id="bulk">
                            <p><input name="orderID" id="orderID" class="regular-text" type="number" placeholder="order ID" value="" required></p>
                            <p><textarea name='projectHashes' id='projectHashes' class='large-text' placeholder="projekt hash-ek egymás alá" style='height:100px;' required></textarea></p>
                            <p>
                                <!--button name="actionButton" id="actionButton" value="update" class="button button-secondary">2. set projects customer</button-->
                                <button name="actionButton" id="actionButton" value="createbulkorder" class="button button-secondary">2. create new order</button>
                                <button name="actionButton" id="actionButton" value="orderupdate" class="button button-secondary">3. set order to paid</button>
                                <span class="button button-secondary" onclick="window.open('https://paperstories-eu-pbx2.getprintbox.com/pb-admin/order/?per-page=500&page=&o=-create_time', '_blank');">4. check is order changed to Rendered from Paid</span>
                                <button name="actionButton" id="actionButton" value="downloadMultiplePDFs" class="button button-secondary">5. download multiple PDFs</button>
                            </p>
                        </form>
                        <p><a class="button button-primary" href="/printbox/" target="_blank">6. open Printbox folder</a></p>
                        <?php
                        break;
                    }
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
                        echo '<pre>';
                        include_once (__DIR__.'/../../../log/upgrader_process_complete.csv');
                        echo '</pre>';
                        break;
                    case 'phpinfo':
                        echo '<style>table, th, td { border: 1px solid;}</style>';
                        ob_start();
                        phpinfo();
                        echo explode('</body>', explode('<body>', trim (ob_get_clean ()))['1'])['0'];
                        break;
                    case 'credits':
                        echo '<style>img{float:left;margin:0 12px;width:120px;height: auto;filter: grayscale(0.5);}</style><img src="https://www.harkalygergo.hu/media/uploads/hosts/www-harkalygergo.hu/622fb3a307872099393996.jpg" alt="Harkály Gergő">';
                        echo '<p><b>Harkály Gergő</b><br><small>web developer</small></p>';
                        echo '<p><a target="_blank" href="//www.harkalygergo.hu" class="button button-primary">www.harkalygergo.hu</a></p>';
                        echo '<p><a target="_blank" href="https://www.buymeacoffee.com/harkalygergo" class="button button-secondary"><span class="dashicons dashicons-coffee"></span> buy me a coffee</a></p>';
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
