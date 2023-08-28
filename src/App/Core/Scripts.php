<?php

namespace WPTurbo\App\Core;

class Scripts
{
    private ?array $config = null;
    public function __construct()
    {
        // do nothing
    }

    public function init(array $config)
    {
        $this->config = $config;
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
        // site
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-head.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-head.js';

        if (file_exists($filePath) && filesize($filePath)) {
            wp_enqueue_script('wp-turbo-script-head', $fileUrl, [], $this->config['Version'], false);
        }

        // multisite
        if (is_multisite()) {
            $filePath = Helper::getUploadDirectoryPath().'multisite-head.js';
            $fileUrl = Helper::getUploadDirectoryUrl().'multisite-head.js';

            if (file_exists($filePath) && filesize($filePath)) {
                wp_enqueue_script('wp-turbo-script-multisite-head', $fileUrl, [], $this->config['Version'], false);
            }
        }
    }

    public function addScriptToBody()
    {
        // site
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-body.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-body.js';

        if (file_exists($filePath) && filesize($filePath)) {
            echo '<script src="'.$fileUrl.'?ver='.$this->config['Version'].'"></script>';
        }

        // multisite
        if (is_multisite()) {
            $filePath = Helper::getUploadDirectoryPath().'multisite-body.js';
            $fileUrl = Helper::getUploadDirectoryUrl().'multisite-body.js';

            if (file_exists($filePath) && filesize($filePath)) {
                echo '<script src="'.$fileUrl.'?ver='.$this->config['Version'].'"></script>';
            }
        }
    }

    public function addScriptToFooter()
    {
        // site
        $filePath = Helper::getUploadDirectoryPath().Helper::getSiteId().'-footer.js';
        $fileUrl = Helper::getUploadDirectoryUrl().Helper::getSiteId().'-footer.js';

        if (file_exists($filePath) && filesize($filePath)) {
            wp_enqueue_script('wp-turbo-script-footer', $fileUrl, [], $this->config['Version'], true);
        }

        // multisite
        if (is_multisite()) {
            $filePath = Helper::getUploadDirectoryPath().'multisite-footer.js';
            $fileUrl = Helper::getUploadDirectoryUrl().'multisite-footer.js';

            if (file_exists($filePath) && filesize($filePath)) {
                wp_enqueue_script('wp-turbo-script-multisite-footer', $fileUrl, [], $this->config['Version'], true);
            }
        }
    }

    public function saveScript()
    {
        file_put_contents(Helper::getUploadDirectoryPath().$_POST['prefix'].'-'.$_POST['placement'].'.js', stripcslashes($_POST['script']));
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }


    public static function admin_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        //Get the active tab from the $_GET param
        $tab = $_GET['tab'] ?? null;
        ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
            <!-- Print the page title -->
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <!-- Here are our tabs -->
            <nav class="nav-tab-wrapper">
                <a href="?page=wp-turbo-scripts" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php _e('Documentation'); ?></a>
                <a href="?page=wp-turbo-scripts&tab=head" class="nav-tab <?php if($tab==='head' && !isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">site head</a>
                <a href="?page=wp-turbo-scripts&tab=body" class="nav-tab <?php if($tab==='body' && !isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">site body</a>
                <a href="?page=wp-turbo-scripts&tab=footer" class="nav-tab <?php if($tab==='footer' && !isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">site footer</a>
                <a href="?page=wp-turbo-scripts&tab=head&multisite=true" class="nav-tab <?php if($tab==='head' && isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">multisite head</a>
                <a href="?page=wp-turbo-scripts&tab=body&multisite=true" class="nav-tab <?php if($tab==='body' && isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">multisite body</a>
                <a href="?page=wp-turbo-scripts&tab=footer&multisite=true" class="nav-tab <?php if($tab==='footer' && isset($_GET['multisite'])):?>nav-tab-active<?php endif; ?>">multisite footer</a>
            </nav>

            <div class="tab-content">
                <?php switch($tab) :
                    case 'head':
                        echo (new self)->getTextAreaForm('head', isset($_GET['multisite']));
                        break;
                    case 'body':
                        echo (new self)->getTextAreaForm('body', isset($_GET['multisite']));
                        break;
                    case 'footer':
                        echo (new self)->getTextAreaForm('footer', isset($_GET['multisite']));
                        break;
                    case null:
                    default:
                        ?>
                        <h2>head</h2>
                        <p>Script placed into <code>&lt;head&gt;</code> section.</p>
                        <h2>body</h2>
                        <p>Script placed after <code>&lt;body&gt;</code> start.</p>
                        <h2>footer</h2>
                        <p>Script placed before closing <code>&lt;/body&gt;</code> tag.</p>
                        <h1>Multisite</h1>
                        <p>If multisite is enabled, under multisite tabs code will appear on all sites. On this website multisite is <b><?php echo (is_multisite() ? 'enabled' : 'disabled'); ?></b>.</p>
                        <?php
                        break;
                endswitch; ?>
            </div>
        </div>
        <?php
    }

    private function getTextAreaForm(string $scriptPlacement, bool $isMultisiteScript): string
    {
        $prefix = $isMultisiteScript ? 'multisite' : Helper::getSiteId();
        $fileContent = Helper::getUploadDirectoryPath().$prefix.'-'.$scriptPlacement.'.js';
        if (file_exists($fileContent)) {
            $fileContent = file_get_contents($fileContent);
        } else {
            $fileContent = '';
        }
        return '
        <form method="post" action="'.admin_url( 'admin-post.php' ).'">
            <input type="hidden" name="action" value="save-script" />
            <input type="hidden" name="prefix" value="'.$prefix.'" />
            <input type="hidden" name="placement" value="'.$scriptPlacement.'">
            <textarea name="script" rows="20" style="width: 100%;">'.$fileContent.'</textarea>
            '.get_submit_button().'
        </form>';
    }


}
