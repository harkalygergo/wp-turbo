<?php declare( strict_types=1 );

namespace App\Core;

class Security
{
    private ?int $maximumCommentLength = 13000;

    public function __construct()
    {
        $this->removeVersionsAndGenerators();

        include_once 'Captcha.php';
        new Captcha();

        // a security precaution to stop comments that are too long
        add_filter('pre_comment_content', array(&$this, 'filter_pre_comment_content'), 9999);
    }

    private function removeVersionsAndGenerators()
    {
        // remove generator version from header
        remove_action('wp_head', 'wp_generator');
        // remove version from rss
        add_filter('the_generator', '__return_empty_string');

        // remove query strings from URLs || https://kinsta.com/knowledgebase/remove-query-string-from-url/
        add_filter('script_loader_src', array(&$this, 'filter_script_loader_src_style_loader_src'), 15, 1);
        add_filter('style_loader_src', array(&$this, 'filter_script_loader_src_style_loader_src'), 15, 1);
    }

    public function filter_pre_comment_content($text)
    {
        if (strlen($text) > $this->maximumCommentLength) {
            wp_die(
                __('This comment is longer than the maximum allowed size and has been dropped.', 'wp-turbo'),
                __('Comment Declined', 'wp-turbo'),
                array( 'response' => 413 )
            );
        }

        return $text;
    }

    // remove query strings from URLs || https://kinsta.com/knowledgebase/remove-query-string-from-url/
    public function filter_script_loader_src_style_loader_src($src)
    {
        if (! is_admin()) {
            $src_explode = explode('?ver=', $src);
            $parts_explode = explode('.', $src_explode ['0']);
            if(end($parts_explode)==='css' || end($parts_explode)==='js')
            {
                $src = $src_explode['0'];
            }
        }

        return $src;
    }
}
