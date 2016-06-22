<?php

namespace ThisData\WordPress;

class Email {

    static function passwordReset(\WP_User $user, $reset_key) {

        if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
        else
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $user_email = $user->user_email;
        $user_login = $user->user_login;

        $message = __('Hi '.$user->display_name.',','thisdata-plugin') . "\r\n\r\n";

        $message .= sprintf( __('We\'ve automatically reset your password on %s','thisdata-plugin'), network_home_url('/') ) . "\r\n\r\n";

        $message .= sprintf( __('Username: %s'), $user_login) . "\r\n\r\n";

        $message .= __('We did this to secure your account, in response to suspicious activity.','thisdata-plugin') . "\r\n\r\n";

        $message .= __('Please visit the following address now to complete the password reset process: ','thisdata-plugin') . "\r\n\r\n";

        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user_login), 'login') . ">\r\n\r\n\r\n\r\n";

        $message .= __('Also remember to triple-check any future emails for suspicious looking content. We will never ask you for your password, and you should make sure the address (URL) of any website you do enter your password hasn\'t changed.','thisdata-plugin') . "\r\n\r\n";

        $title = sprintf( __('[%s] Password Reset','thisdata-plugin'), $blogname );

        $title = apply_filters( 'thisdata/reset_password_title', $title, $user_login);
        $message = apply_filters( 'thisdata/reset_password_message', $message, $reset_key, $user_login);

        //\Analog::log('Sending message '.$message, \Analog::DEBUG);

        return wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
    }
}
