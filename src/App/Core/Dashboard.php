<?php declare( strict_types=1 );

namespace App\Core;

class Dashboard
{
    private string $menuTitle = 'WP Turbo';
    private string $menuSlug = 'wp-turbo';
    private array|bool $options = false;
    private string $optionGroupName = 'wp-turbo-options-group';
    private string $optionName = 'wp-turbo-options';

    public function __construct()
    {
        // do nothing
    }

    public function init(): void
    {
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
        }
    }

    public function searchBySku( $search, $query_vars )
    {
        global $wpdb;
        if(isset($query_vars->query['s']) && !empty($query_vars->query['s'])) {
            $posts = get_posts([
                'posts_per_page'  => -1,
                'post_type'       => 'product',
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

            foreach($posts as $post){
                $get_post_ids[] = $post->ID;
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
            add_action( 'admin_menu', [$this, 'addAdminMenu']);
            add_action( 'admin_init', [$this, 'adminPageInit'] );
        }
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
            'wp-turbo-settings-woocommerce', // ID
            'WooCommerce', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'enableSearchBySku',
            'SKU search is enabled?',
            array( $this, 'title_callback' ),
            'my-setting-admin',
            'wp-turbo-settings-woocommerce'
        );
    }












    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
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
    public function title_callback()
    {
        $input = '';

        $options = [
            'enableSearchBySku' => ['false', 'true'],
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
                <a href="?page=<?php echo $this->menuSlug; ?>" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php echo $this->menuTitle; ?></a>
                <a href="?page=<?php echo $this->menuSlug; ?>&tab=documentation" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Documentation</a>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'documentation':
                        echo 'Dokumentáció hamarosan...';
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
