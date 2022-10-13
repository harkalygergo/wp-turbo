<?php

namespace App\Core;

namespace WPTurbo\App\Core;

use WPTurbo\App\App;

class Frontend extends App
{
    public function __construct()
    {
        // do nothing
        //$this->dump($this->options, true);
        parent::__construct();
    }

    public function init()
    {
        if (!is_admin()) {
            $this->initFunction();
        }
    }

    public function initFunction()
    {
        //$this->dump($this->options, true);
        if (isset($this->options['enableSearchBySku']) && $this->options['enableSearchBySku'] === "true") {
            add_filter( 'posts_search', [$this, 'searchBySku'], 999, 2 );
        }
    }

    public function searchBySku( $search, $query_vars )
    {
        global $wpdb;
        if(isset($query_vars->query['s']) && !empty($query_vars->query['s'])) {
            $posts = get_posts([
                'posts_per_page'    => -1,
                'post_type'         => ['product', 'product_variation'],
                'meta_query'        => [
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
}
