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
    }

    public function addMetaDescription()
    {
        if (is_product() || is_page() || is_single()) {
            global $post;
            $metaDescription = $post->post_excerpt;
        } else {
            $metaDescription = get_bloginfo( 'description' );
        }

        echo "\n".'<meta name="description" content="' . esc_attr( $metaDescription ) . '" />' . "\n";
    }
}
