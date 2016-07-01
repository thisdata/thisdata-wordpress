=== Login Security by ThisData ===
Contributors: timfield, thisdata
Donate link: https://thisdata.com/upgrade
Tags: security, phishing, login, authentication
Requires at least: 4.5
Tested up to: 4.5.3
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ThisData monitors logins to your site and notifies the admin/user if a suspicious login is detected.

== Description ==

ThisData is a security service that monitors for suspicious logins to your WordPress websites.

When suspicious logins are detected ThisData will immediately notify the user by email, asking if the login was actually them.

If the user responds to the email indicating it was not them, then the user session can be automatically terminated and a password reset email will be sent.

It relies on the ThisData anomaly detection algorithms which take into account many behavioral factors including:

*   Location & Velocity
*   Devices
*   Time of day
*   Tor usage
*   Risky IP addresses
*   And more...

You will also benefit from having a beautiful real-time security dashboard for your WordPress website which shows who is accessing, what devices they're using and where they're located.

== Installation ==

To complete the installation of this plugin you will need a ThisData account.

1. Get a free ThisData account at [https://thisdata.com/sign-up](https://thisdata.com/sign-up)
1. Go to your email and confirm your ThisData account using the link in the email that was just sent to you.
1. You will now see a Getting Started screen.
1. Take a copy of your ThisData API Key and Javascript Write Key. You will use them in the plugin setup.

= Now install the plugin. =

1. Upload the plugin files to the `/wp-content/plugins/thisdata` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->ThisData screen to configure the plugin
1. Copy your ThisData API Key and Javascript Write Key into the boxes provided and click "Save Changes"

At this stage you will now be tracking log-in, log-out, password reset and plugin installation related events to ThisData. If you log-out of WordPress and log back in you will see your login attempt in your ThisData account.

= Advanced setup =

To create a really tight security integration you can enable this plugin to end a user session and reset password when the user confirms that the suspicious activity was not them. This requires the use of webhooks from ThisData so you will need to [upgrade to a paid account](https://thisdata.com/upgrade).

1. From your WordPress->Settings->ThisData screen copy the Alert Webhooks Path which should look like `http://[your domain]/thisdata/webhook`
1. In your ThisData account click on API Settings from the top nav bar and near the bottom of the screen you will see an "Internal Notifications" section.
1. Enter your Alert Webhooks Path into the ThisData Webhook Url box and click "Save API Settings"
1. Note: You don't need to enter anything for the "Secret for Webhook Signatures"

Congrats! You now have a really sophisticated anomaly detection system protecting your WordPress website.

Thanks for using ThisData.

== Frequently Asked Questions ==

= How do I get started with ThisData? =

Before installing this plugin, you'll need to sign up for a free ThisData account at [https://thisdata.com/sign-up](https://thisdata.com/sign-up)

= Is this service free? =

Yes, for up to 250 users the basic monitoring and notification service is free. If you want to implement the **advanced** setup  that uses webhooks to disable user access when a threat is confirmed then you will need a paid account, but we offer a 30 day trial, so have a go!

= What are alert webhooks? =

The webhooks let you create an automated security workflow that will shutdown the access to a confirmed attacker. This happens as soon as the user responds to the email notification indicating that
"it was not them".

= I'm not seeing events in ThisData =

Chances are you have the API key wrong. Double check this and try again. If you're still having trouble contact support at thisdata dot com and we will help.

== Screenshots ==

1. Get a complete audit history of logins and other security related events
2. When suspicious logins are detected an email like this is sent to the user
3. If you click "no it wasnt me" the user session will be terminated immediately
4. Your ThisData security dashboard helps you keep track of who is doing what and where they're doing it from.
5. The plugin is quick to set up and get started.

== Changelog ==

= 1.0 =
* Yay! ThisData is finally available for WordPress users!

== Upgrade Notice ==

Version 1 is now available

== Arbitrary section ==
