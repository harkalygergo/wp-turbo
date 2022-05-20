<?php declare( strict_types=1 );

namespace App\Core;

class Security
{

    public function __construct()
    {
        $this->removeGenerators();
        $this->loginFormCaptcha();
    }

    private function removeGenerators()
    {
        // remove generator version from header
        remove_action('wp_head', 'wp_generator');
        // remove version from rss
        add_filter('the_generator', '__return_empty_string');
        //add_action('template_redirect', array(&$this, 'action_template_redirect')); // redirects ?author= URLs to homepage to avoid getting author names
    }

    private function loginFormCaptcha()
    {
        // captcha on login form
        add_action('login_form', array($this, 'action_login_form'));
        add_action('woocommerce_login_form', array($this, 'action_login_form'));
        add_action('wp_authenticate_user', array($this, 'action_wp_authenticate_user'), 10, 2);
    }

    public function action_login_form()
    {
        $this->captcha_key1 = rand(1, 10);
        $this->captcha_key2 = rand(1, 10);
        ?>
        <p>
            <label for="user_captcha">Captcha</label>
            <input type="text" name="user_captcha" id="user_captcha" class="input" placeholder="<?php echo $this->captcha_key1.'+'.$this->captcha_key2.'=?';?>" required>
            <input type="hidden" name="captcha_result" value="<?php echo $this->captcha_key1+$this->captcha_key2; ?>" required>
        </p>
    <?php }
    public function action_wp_authenticate_user($user, $password)
    {
        if(!isset($_POST['user_captcha']) || empty($_POST['user_captcha']) || !isset($_POST['captcha_result']) || empty($_POST['captcha_result']))
        {
            return new \WP_Error('empty_captcha', 'CAPTCHA should not be empty');
        }
        if(isset($_POST['user_captcha']) && isset($_POST['captcha_result']) && $_POST['user_captcha'] != $_POST['captcha_result'])
        {
            return new \WP_Error('invalid_captcha', 'CAPTCHA response was incorrect');
        }
        return $user;
    }

    // remove query strings from URLs || https://kinsta.com/knowledgebase/remove-query-string-from-url/
    public function filter_script_loader_src_style_loader_src($src)
    {
        if(!is_admin())
        {
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
