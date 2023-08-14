<?php

namespace WPTurbo\App\Core;

class Scripts
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
        add_action( 'admin_post_save-script', [$this, 'saveScript'] );
        add_action('wp_enqueue_scripts', [$this, 'addScriptToHead']);
        add_action('wp_body_open', [$this, 'addScriptToBody']);
        add_action('wp_enqueue_scripts', [$this, 'addScriptToFooter']);
    }

    public function addScriptToHead()
    {
        if (file_exists(Helper::getUploadDirectoryPath().Helper::getSiteBaseUrl().'-in-head.js')) {
            wp_enqueue_script('wp-turbo-script-head', Helper::getUploadDirectoryUrl().Helper::getSiteBaseUrl().'-in-head.js', [], false, false);
        }
    }

    public function addScriptToBody()
    {
        if (file_exists(Helper::getUploadDirectoryPath().Helper::getSiteBaseUrl().'-after-body-start.js')) {
            echo '<script src="'.Helper::getUploadDirectoryUrl().Helper::getSiteBaseUrl().'-after-body-start.js'.'"></script>';
        }
    }

    public function addScriptToFooter()
    {
        if (file_exists(Helper::getUploadDirectoryPath().Helper::getSiteBaseUrl().'-before-closing-body.js')) {
            wp_enqueue_script('wp-turbo-script-footer', Helper::getUploadDirectoryUrl().Helper::getSiteBaseUrl().'-before-closing-body.js', [], false, true);
        }
    }

    public function saveScript()
    {
        file_put_contents(Helper::getUploadDirectoryPath().Helper::getSiteBaseUrl().'-'.$_POST['placement'].'.js', stripcslashes($_POST['script']));
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }


    public static function admin_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        //Get the active tab from the $_GET param
        $default_tab = null;
        $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

        ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
            <!-- Print the page title -->
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <!-- Here are our tabs -->
            <nav class="nav-tab-wrapper">
                <a href="?page=wp-turbo-scripts" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Dokumentáció</a>
                <a href="?page=wp-turbo-scripts&tab=in-head" class="nav-tab <?php if($tab==='in-head'):?>nav-tab-active<?php endif; ?>">before /head</a>
                <a href="?page=wp-turbo-scripts&tab=after-body-start" class="nav-tab <?php if($tab==='after-body-start'):?>nav-tab-active<?php endif; ?>">after body start</a>
                <a href="?page=wp-turbo-scripts&tab=before-closing-body" class="nav-tab <?php if($tab==='before-closing-body'):?>nav-tab-active<?php endif; ?>">before closing body</a>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'in-head':
                        echo (new self)->getTextAreaForm('in-head');
                        break;
                    case 'after-body-start':
                        echo (new self)->getTextAreaForm('after-body-start');
                        break;
                    case 'before-closing-body':
                        echo (new self)->getTextAreaForm('before-closing-body');
                        break;
                    default:
                        echo 'dokumentáció';
                        break;
                endswitch; ?>
            </div>
        </div>
        <?php
    }

    private function getTextAreaForm(string $scriptPlacement): string
    {
        $fileContent = Helper::getUploadDirectoryPath().Helper::getSiteBaseUrl().'-'.$scriptPlacement.'.js';
        if (file_exists($fileContent)) {
            $fileContent = file_get_contents($fileContent);
        } else {
            $fileContent = '';
        }
        return '
        <h2>'.$scriptPlacement.'</h2>
        <form method="post" action="'.admin_url( 'admin-post.php' ).'">
            <input type="hidden" name="action" value="save-script" />
            <input type="hidden" name="placement" value="'.$scriptPlacement.'">
            <textarea name="script" rows="20" style="width: 100%;">'.$fileContent.'</textarea>
            '.get_submit_button().'
        </form>';
    }


}
