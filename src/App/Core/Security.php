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
        add_filter('pre_comment_content', [&$this, 'filter_pre_comment_content'], 9999);

        // redirects ?author= URLs to homepage to avoid getting author names
        add_action('template_redirect', [&$this, 'action_template_redirect']);
    }


    // remove query strings from URLs || https://kinsta.com/knowledgebase/remove-query-string-from-url/
    public function filter_script_loader_src_style_loader_src($src)
    {
        if (! is_admin() && gettype($src)==="string") {
            $src_explode = explode('?ver=', $src);
            $parts_explode = explode('.', $src_explode ['0']);
            if(end($parts_explode)==='css' || end($parts_explode)==='js')
            {
                $src = $src_explode['0'];
            }
        }

        return $src;
    }

    // redirects ?author= URLs to homepage to avoid getting author names
    public function action_template_redirect()
    {
        if (is_author())
        {
            wp_redirect(home_url());
            exit();
        }
    }

    private function removeVersionsAndGenerators()
    {
        // remove generator version from header
        remove_action('wp_head', 'wp_generator');
        // remove version from rss
        add_filter('the_generator', '__return_empty_string');

        // remove query strings from URLs || https://kinsta.com/knowledgebase/remove-query-string-from-url/
        //add_filter('script_loader_src', array(&$this, 'filter_script_loader_src_style_loader_src'), 15, 1);
        //add_filter('style_loader_src', array(&$this, 'filter_script_loader_src_style_loader_src'), 15, 1);
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
}
