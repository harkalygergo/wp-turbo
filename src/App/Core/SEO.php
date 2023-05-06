<?php declare( strict_types=1 );

namespace App\Core;

class SEO
{
    public function __construct()
    {
        // do nothing
    }
    public function init()
    {
        $this->setHooks();
    }
    public function setHooks()
    {
        if ( ! is_admin() ) {
            $this->setFrontendHooks();
        }
    }

    public function setFrontendHooks()
    {
        add_action( 'wp_head', [$this, 'addMetaDescription'] );
        add_action( 'wp_head', [$this, 'addSchemaPostMetaToHead'] );
    }

    public function addMetaDescription()
    {
        if (is_singular(['post', 'page', 'product'])) {
            global $post;
            $metaDescription = $post->post_excerpt;
        } else {
            $metaDescription = get_bloginfo( 'description' );
        }

        echo "\n".'<meta name="description" content="' . esc_attr( $metaDescription ) . '" />' . "\n";
    }

    public function addSchemaPostMetaToHead()
    {
        if (is_singular(['post', 'page', 'product'])) {
            $schema = get_post_meta(get_the_ID(), 'schema', true);
            if(!empty($schema)) {
                echo $schema;
            }
        }
    }
}
