<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

class User
{
    private string $lastLoginMetaKey = '_last_login';
    private string $lastLoginText = 'Last login';
    private string $lastLoginDateFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        // do nothing
    }

    public function setHooks()
    {
        add_action( 'wp_login', [$this, 'action_wp_login'], 10, 2 );

        if (isset(Dashboard::getOptions()['loggedInUserIdBodyClass']) && Dashboard::getOptions()['loggedInUserIdBodyClass'] === "true") {
            add_action('body_class', [$this, 'addLoggedInUserIdBodyClass']);
        }

        if (is_admin()) {
            add_filter('manage_users_columns', [$this, 'addRegistrationLastLoginColumns']);
            add_filter('manage_users_custom_column', [$this, 'addRegistrationLastLoginColumnsResults'], 10, 3);
            add_filter('manage_users_sortable_columns', [$this, 'filterRegistrationLastLoginColumnsResults'], 10, 1);
            add_action( 'pre_get_users', [$this, 'action_pre_get_users'] );
            add_action( 'admin_init', [$this, 'addSettingsOptions'] );
        }
    }

    public function addSettingsOptions()
    {
        add_settings_section(
            'wp-turbo-settings-user', // ID
            'User', // Title
            _e(''), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'loggedInUserIdBodyClass',
            'Add "logged-in-user-[ID]" class to body?',
            [Dashboard::class, 'generateFormSelect'],
            'my-setting-admin',
            'wp-turbo-settings-user',
            ['name' => 'loggedInUserIdBodyClass']
        );
    }

    /**
     * Add custom field body class(es) to the body classes.
     *
     * It accepts values from a per-page custom field, and only outputs when viewing a singular static Page.
     *
     * @param array $classes Existing body classes.
     * @return array Amended body classes.
     */
    public function addLoggedInUserIdBodyClass( array $classes ): array
    {
        if (is_user_logged_in()) {
            $classes[] = 'logged-in-user-'.get_current_user_id();
        }

        return $classes;
    }

    /**
     * @param string $user_login
     * @param \WP_User $user
     *
     * @return void
     */
    public function action_wp_login( string $user_login, \WP_User $user ): void
    {
        update_user_meta( $user->ID, $this->lastLoginMetaKey, time() );
    }

    public function addRegistrationLastLoginColumns($columns) {
        $columns['registration_date'] = __('Registered');
        $columns[$this->lastLoginMetaKey] = $this->lastLoginText;

        return $columns;
    }

    public function addRegistrationLastLoginColumnsResults( $column_output, $column_name, $user_id )
    {
        switch ($column_name) {
            case 'registration_date' :
            {
                return get_the_author_meta( 'registered', $user_id );
            }
            case $this->lastLoginMetaKey:
            {
                $userLastLoginTimestamp = (int)get_user_meta($user_id, $this->lastLoginMetaKey, true);

                return sprintf("%s (%s)",
                    date($this->lastLoginDateFormat, $userLastLoginTimestamp),
                    human_time_diff($userLastLoginTimestamp, time())
                );
            }
            default:
        }

        return $column_output;
    }

    public function filterRegistrationLastLoginColumnsResults( $columns )
    {
        return wp_parse_args([
            'registration_date'     => 'registered',
            $this->lastLoginMetaKey => $this->lastLoginMetaKey
        ], $columns );
    }

    /**
     * Sort by expire date. Meta key is called 'DATE'.
     *
     * @since  1.0.0
     * @return void
     */
    function action_pre_get_users( $query )
    {
        if( !is_admin() ) {
            return $query;
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();

            if( isset( $screen->id ) && $screen->id !== 'users' ) {
                return $query;
            }
        }

        if( isset( $_GET[ 'orderby' ] ) && $_GET[ 'orderby' ] == $this->lastLoginMetaKey ) {
            $query->query_vars['meta_key'] = $this->lastLoginMetaKey;
            $query->query_vars['orderby'] = 'meta_value';
        }

        return $query;
    }
}
