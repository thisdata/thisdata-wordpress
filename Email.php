<?php

namespace ThisData\WordPress;

class Email {

    static function passwordReset($user_email, $user_login, $reset_key) {

        $message = __('Your password has been reset for the following account:','thisdata-plugin') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('This is an automatic security measure in response to suspicious activity.','thisdata-plugin') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:','thisdata-plugin') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

        if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
        else
        /*
        * The blogname option is escaped with esc_html on the way into the database
        * in sanitize_option we want to reverse this for the plain text arena of emails.
        */
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf( __('[%s] Password Reset','thisdata-plugin'), $blogname );

        $title = apply_filters( 'thisdata/reset_password_title', $title, $user_login);
        $message = apply_filters( 'thisdata/reset_password_message', $message, $reset_key, $user_login);

        \Analog::log('Sending message '.$message, \Analog::DEBUG);

        return wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
    }
}
