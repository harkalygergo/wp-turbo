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
        $this->options = get_option( $this->menuSlug );
    }

    private function initFrontend(): void
    {

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
                <a href="?page=<?php echo $this->menuSlug; ?>&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'settings':
                        echo $this->getSettingsHtml();
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

    private function getSettingsHtml(): string
    {
        $html = '';
        $html .= '<form method="post" action="options.php">';
        $html .= '<table>';
        $html .= '<tbody>';

        $options = [
            'enableSearchBySku' => ['false', 'true'],
        ];

        foreach ($options as $optionKey => $optionValue) {
            $input = sprintf('<select name="%s" id="%s">', $optionKey, $optionKey);
            foreach($optionValue as $option) {
                $input .= sprintf('<option value="%s">%s</option>', $option, $option);
            }
            $input .= '</select>';

            $html .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $optionKey, $input);
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= get_submit_button();
        $html .= '</form>';

        return $html;
    }
}
