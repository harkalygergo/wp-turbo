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
