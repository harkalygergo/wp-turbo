<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

class Backend
{
    private string $menuTitle = 'WP Turbo';
    private string $menuSlug = 'wp-turbo';
    private string $optionGroupName = 'wp-turbo-options-group';
    private string $optionName = 'wp-turbo-options';

    public function __construct()
    {
        // do nothing
    }

    public function init(): void
    {
        if (is_admin()) {
            $this->initVariables();
            $this->initBackend();
        }
    }

    private function initVariables(): void
    {
        //$this->options = get_option( $this->menuSlug );
        //$this->options = get_option( $this->optionName );
    }

    private function initBackend(): void
    {
        add_action( 'admin_menu', [$this, 'addAdminMenu']);
        add_action( 'admin_init', [$this, 'adminPageInit'] );
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

        // general
        add_settings_section(
            'wp-turbo-settings-general', // ID
            'General', // Title
            function() {}, // Callback
            'my-setting-admin' // Page
        );
        add_settings_field(
            'phone',
            'Phone number',
            function() { echo '<input type="text" name="phone" id="phone">'; },
            'my-setting-admin',
            'wp-turbo-settings-general',
            []
        );

        // modules
        add_settings_section(
            'wp-turbo-settings-woocommerce-modules', // ID
            'Modules', // Title
            function() {}, // Callback
            'my-setting-admin' // Page
        );
        add_settings_field(
            'isPrintboxModuleEnabled',
            'Printbox module is enabled?',
            [$this, 'selectOptions'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce-modules',
            ['isPrintboxModuleEnabled' => ['false', 'true']]
        );

        add_settings_field(
            'Checkbox Element',
            'Checkbox Element',
            [$this, 'sandbox_checkbox_element_callback'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce-modules',
        );


        // WooCommerce
        add_settings_section(
            'wp-turbo-settings-woocommerce', // ID
            'WooCommerce', // Title
            function() {}, // Callback
            'my-setting-admin' // Page
        );
        add_settings_field(
            'enableSearchBySku',
            'SKU search is enabled?',
            [$this, 'selectOptions'],
            'my-setting-admin',
            'wp-turbo-settings-woocommerce',
            ['enableSearchBySku' => ['false', 'true']]
        );




        // printbox
        register_setting('wp-turbo-option-group-printbox', 'wp-turbo_printbox', [$this, 'sanitize']);
        add_settings_section(
            'wp-turbo-settings-printbox', // ID
            'Printbox', // Title
            array( $this, 'initWPTurboSettingsPrintbox' ), // Callback
            'wp-turbo-setting-printbox' // Page
        );
        add_settings_field(
            'isPrintboxModuleEnabled',
            'Printbox module is enabled?',
            array( $this, 'selectOptions' ),
            'wp-turbo-setting-printbox',
            'wp-turbo-settings-printbox',
            ['isPrintboxModuleEnabled' => ['false', 'true']]
        );







    }

    function sandbox_checkbox_element_callback() {

        $options = get_option( 'sandbox_theme_input_examples' );

        $html = '<input type="checkbox" id="checkbox_example" name="sandbox_theme_input_examples[checkbox_example]" value="1"' . checked( 1, $options['checkbox_example'], false ) . '/>';
        $html .= '<label for="checkbox_example">This is an example of a checkbox</label>';

        echo $html;

    }


    public function initWPTurboSettingsPrintbox()
    {
        $this->optionName = 'wp-turbo_printbox';
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
     * Get the settings option array and print one of its values
     */
    public function selectOptions($options)
    {
        $input = '';

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

        $tabs = [
            'documentation' => 'Dokumentáció',
            'printbox'      => 'Printbox',
        ];

        ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
            <!-- Print the page title -->
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <!-- Here are our tabs -->
            <nav class="nav-tab-wrapper">
                <a href="?page=<?php echo $this->menuSlug; ?>" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php echo $this->menuTitle; ?></a>
                <?php foreach ($tabs as $tabKey=>$tabValue) {
                    $active = ($tab===$tabKey ? ' nav-tab-active ' : '' );
                    echo '<a href="?page='.$this->menuSlug.'&tab='.$tabKey.'" class="nav-tab '.$active.'">'.$tabValue.'</a>';
                }
                ?>
            </nav>

            <div class="tab-content">
                <div class="wrap">
                    <?php switch($tab) :
                        case 'printbox':
                        {
                            echo '<h2>'.$tabs['printbox'].'</h2>';
                            break;
                        }
                        case 'documentation':
                            echo '<h2>'.$tabs['documentation'].'</h2>';
                            // Set class property
                            $this->options = get_option( 'wp-turbo_printbox' );
                            ?>
                                <p>Dokumentáció hamarosan...</p>
                                <form method="post" action="options.php">
                                    <?php
                                    // This prints out all hidden setting fields
                                    settings_fields( 'wp-turbo-option-group-printbox' );
                                    do_settings_sections( 'wp-turbo-setting-printbox' );
                                    submit_button();
                                    ?>
                                </form>
                            <?php
                            break;
                        default:
                            // Set class property
                            $this->options = get_option( $this->optionName );
                            ?>
                                <form method="post" action="options.php">
                                    <?php
                                    // This prints out all hidden setting fields
                                    settings_fields( $this->optionGroupName );
                                    do_settings_sections( 'my-setting-admin' );
                                    submit_button();
                                    ?>
                                </form>
                            <?php
                            break;
                    endswitch; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
