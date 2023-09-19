<?php declare( strict_types=1 );

namespace WPTurbo\App\Core;

use WPTurbo\App\App;

class Log extends App
{
    private string $upgrader_process_complete_path = __DIR__.'/../../../log/upgrader_process_complete.csv';

    public function __construct()
    {
        // do nothing
        //parent::__construct();
    }

    public function setHooks()
    {
        add_action( 'wp', [$this, 'saveVisitorData'] );
        add_action( 'upgrader_process_complete', [$this, 'action_upgrader_process_complete'], 10, 2 );
        add_action( 'shutdown', [$this, 'SaveQueriesLogger'] );
    }

    public function saveVisitorData(): void
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (is_user_logged_in() && current_user_can('administrator') && current_user_can( 'manage_options' )) {
            return;
        }

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];

        // Get server related info
        $user_ip_address = $_SERVER['REMOTE_ADDR'];
        $referrer_url = $_SERVER['HTTP_REFERER'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $log = sprintf('"%s";"%s";%d;"%s";"%s";"%s"',
            date_i18n('Y-m-d H:i:s'), // Time
            $user_ip_address, // IP Address
            get_current_user_id(), // User ID
            $referrer_url, // Reffer URL
            $currentURL, // Requested URL
            $user_agent // User Agent
        );

        $this->stream = fopen(wp_upload_dir()['basedir']."/wp-turbo/visitorlog-".date('Ymd').".csv", "a+");
        fwrite($this->stream, $log . "\n");
        fclose($this->stream);
    }

    public function SaveQueriesLogger()
    {
        global $wpdb;

        if (!is_null($wpdb->queries)) {
            $file = fopen(Helper::getUploadDirectoryPath().Helper::getSiteId().'-sqlLogs.sql', 'a');

            fwrite($file, "\n\n------ NEW REQUEST [" . date("F j, Y, g:i:s a") . "] ------\n");

            foreach ($wpdb->queries as $q) {
                fwrite($file, $q[0] . " - ($q[1] s)" . "\n");
            }

            fwrite($file, "\n");
            fclose($file);
        }
    }

    /**
     * This function runs when WordPress completes its upgrade process
     * It iterates through each plugin updated to see if ours is included
     * @link https://stackoverflow.com/a/61062331
     *
     * @param array $upgrader_object
     * @param array $options
     *
     * @return void
     */
    public function action_upgrader_process_complete( $upgrader_object, $options ): void
    {
        $data = date('Y-m-d H:i:s').', '.json_encode($options, JSON_UNESCAPED_UNICODE)."\n";
        file_put_contents($this->upgrader_process_complete_path, $data, FILE_APPEND | LOCK_EX);
    }
}
