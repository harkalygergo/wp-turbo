<?php

namespace WPTurbo\App\Core;

class Helper
{
    public static function getUploadDirectoryPath(): string
    {
        $WPTurboDirectory = wp_upload_dir()['basedir'] . '/wp-turbo';
        if (!file_exists($WPTurboDirectory)) {
            mkdir($WPTurboDirectory, 0777, true);
        }

        return $WPTurboDirectory . '/';
    }

    public static function getUploadDirectoryUrl()
    {
        return wp_upload_dir()['baseurl'] . '/wp-turbo/';
    }
    public static function getSiteBaseUrl(): string
    {
        return preg_replace("(^https?://)", "", get_site_url() );
    }

    public static function getSiteId(): int
    {
        return get_current_blog_id();
    }
}
