<?php declare( strict_types=1 );

namespace App\Core;

class Captcha
{
    private ?int $captchaKey1 = null;
	private ?int $captchaKey2 = null;

    public function __construct()
    {
        $this->loginFormCaptcha();
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
        $this->captchaKey1 = rand(1, 10);
        $this->captchaKey2 = rand(1, 10);
        ?>
        <p>
            <label for="user_captcha">Captcha</label>
            <input type="text" name="user_captcha" id="user_captcha" class="input" placeholder="<?php echo $this->captchaKey1.'+'.$this->captchaKey2.'=?';?>" required>
            <input type="hidden" name="captcha_result" value="<?php echo $this->captchaKey1+$this->captchaKey2; ?>" required>
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
}
