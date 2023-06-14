<?php declare( strict_types=1 );

namespace App\Core;

class Log
{
    private string $upgrader_process_complete_path = __DIR__.'/../../../log/upgrader_process_complete.csv';

    public function __construct()
    {
        // do nothing
    }

    public function setHooks()
    {
        add_action( 'upgrader_process_complete', [$this, 'action_upgrader_process_complete'], 10, 2 );
        add_action( 'shutdown', [$this, 'SaveQueriesLogger'] );
    }

    public function createWPTurboDirectory()
    {
        // create directory under wp-content/uploads
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $upload_dir = $upload_dir . '/wp-turbo';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
    }

    public function SaveQueriesLogger()
    {
        global $wpdb;

        if (!is_null($wpdb->queries)) {
            $this->createWPTurboDirectory();
            $file = fopen(trailingslashit(WP_CONTENT_DIR) . 'uploads/wp-turbo/sqlLogs.sql', 'a');

            fwrite($file, "\n\n------ NEW REQUEST [" . date("F j, Y, g:i:s a") . "] ------\n\n");

            foreach ($wpdb->queries as $q) {
                fwrite($file, $q[0] . " - ($q[1] s)" . "\n\n");
            }

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
