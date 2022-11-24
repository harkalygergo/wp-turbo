<?php declare( strict_types=1 );

namespace App\Core;

class Email
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
        // change default e-mail sender name and address
        add_filter('wp_mail_from_name', [$this, 'changeMailFromName']);
        add_filter('wp_mail_from', [$this, 'changeMailFromAddress']);
    }

    public function changeMailFromName($original_email_from)
    {
        return get_bloginfo('name');
    }

    public function changeMailFromAddress($original_email_address)
    {
        return get_bloginfo('admin_email');
    }
}

