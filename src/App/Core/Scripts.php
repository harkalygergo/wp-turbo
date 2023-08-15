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
        add_action('admin_post_save-script', [$this, 'saveScript']);
        add_action('wp_enqueue_scripts', [$this, 'addScriptToHead']);
        add_action('wp_body_open', [$this, 'addScriptToBody']);
        add_action('wp_enqueue_scripts', [$this, 'addScriptToFooter']);
    }

    public function addScriptToHead()
    {
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-head.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-head.js';

        if (file_exists($filePath) && filesize($filePath)) {
            wp_enqueue_script('wp-turbo-script-head', $fileUrl, [], false, false);
        }
    }

    public function addScriptToBody()
    {
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-body.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-body.js';

        if (file_exists($filePath) && filesize($filePath)) {
            echo '<script src="'.$fileUrl.'?ver='.date('yW').'"></script>';
        }
    }

    public function addScriptToFooter()
    {
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-footer.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-footer.js';

        if (file_exists($filePath) && filesize($filePath)) {
            wp_enqueue_script('wp-turbo-script-footer', $fileUrl, [], false, true);
        }
    }

    public function saveScript()
    {
        file_put_contents(Helper::getUploadDirectoryPath().Helper::getSiteId().'-'.$_POST['placement'].'.js', stripcslashes($_POST['script']));
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
                <a href="?page=wp-turbo-scripts" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php _e('Documentation'); ?></a>
                <a href="?page=wp-turbo-scripts&tab=head" class="nav-tab <?php if($tab==='head'):?>nav-tab-active<?php endif; ?>">head</a>
                <a href="?page=wp-turbo-scripts&tab=body" class="nav-tab <?php if($tab==='body'):?>nav-tab-active<?php endif; ?>">body</a>
                <a href="?page=wp-turbo-scripts&tab=footer" class="nav-tab <?php if($tab==='footer'):?>nav-tab-active<?php endif; ?>">footer</a>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'head':
                        echo (new self)->getTextAreaForm('head');
                        break;
                    case 'body':
                        echo (new self)->getTextAreaForm('body');
                        break;
                    case 'footer':
                        echo (new self)->getTextAreaForm('footer');
                        break;
                    default:
                        echo '';
                        ?>
                        <h2>head</h2>
                        <p>Script placed into <code>&lt;head&gt;</code> section.</p>
                        <h2>body</h2>
                        <p>Script placed after <code>&lt;body&gt;</code> start.</p>
                        <h2>footer</h2>
                        <p>Script placed before closing <code>&lt;/body&gt;</code> tag.</p>
                        <?php
                        break;
                endswitch; ?>
            </div>
        </div>
        <?php
    }

    private function getTextAreaForm(string $scriptPlacement): string
    {
        $fileContent = Helper::getUploadDirectoryPath().Helper::getSiteId().'-'.$scriptPlacement.'.js';
        if (file_exists($fileContent)) {
            $fileContent = file_get_contents($fileContent);
        } else {
            $fileContent = '';
        }
        return '
        <form method="post" action="'.admin_url( 'admin-post.php' ).'">
            <input type="hidden" name="action" value="save-script" />
            <input type="hidden" name="placement" value="'.$scriptPlacement.'">
            <textarea name="script" rows="20" style="width: 100%;">'.$fileContent.'</textarea>
            '.get_submit_button().'
        </form>';
    }


}
