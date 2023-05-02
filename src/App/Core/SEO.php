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
        global $post;

        $postExcerpt = $post->post_excerpt;

        if ( ! empty( $postExcerpt ) ) {
            $metaDescription = $postExcerpt;
        } else {
            $metaDescription = get_bloginfo( 'description' );
        }

        echo "\n".'<meta name="description" content="' . esc_attr( $metaDescription ) . '" />' . "\n";
    }
}
