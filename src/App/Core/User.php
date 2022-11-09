<?php declare( strict_types=1 );

namespace App\Core;

class User
{
    private string $lastLoginMetaKey = '_last_login';
    private string $lastLoginText = 'Last login';

    public function __construct()
    {
        // do nothing
    }

    public function setHooks()
    {
        add_action( 'wp_login', [$this, 'action_wp_login'], 10, 2 );
        add_filter('manage_users_columns', [$this, 'filter_manage_users_columns']);
        add_filter('manage_users_custom_column', [$this, 'filter_manage_users_custom_column'], 10, 3);
        add_filter('manage_users_sortable_columns', [$this, 'filter_manage_users_sortable_columns'], 10, 1);
        add_action( 'pre_get_users', [$this, 'action_pre_get_users'] );
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

    public function filter_manage_users_columns($columns) {
        $columns[$this->lastLoginMetaKey] = $this->lastLoginText;
        return $columns;
    }

    public function filter_manage_users_custom_column( $column_output, $column_name, $user_id )
    {
        if ( $this->lastLoginMetaKey == $column_name ) {
            return date('Y-m-d H:i:s', (int)get_user_meta($user_id, $this->lastLoginMetaKey, true));
        }

        return $column_output;
    }

    public function filter_manage_users_sortable_columns( $columns_to_sort ) {
        // make the column sortable
        $columns_to_sort[$this->lastLoginMetaKey] = $this->lastLoginMetaKey;

        return $columns_to_sort;
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

        $screen = get_current_screen();

        if( isset( $screen->id ) && $screen->id !== 'users' ) {
            return $query;
        }

        if( isset( $_GET[ 'orderby' ] ) && $_GET[ 'orderby' ] == $this->lastLoginMetaKey ) {
            $query->query_vars['meta_key'] = $this->lastLoginMetaKey;
            $query->query_vars['orderby'] = 'meta_value';
        }

        return $query;
    }
}
