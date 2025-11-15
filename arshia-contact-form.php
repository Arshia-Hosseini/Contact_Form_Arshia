<?php
/*
Plugin Name: Arshia Contact Form
Plugin URI: https://example.com
Description:  simple contact form to test my learnings.
Version: 1.0
Author: Arshia
*/

// exit if accessed directly via URL
if (!defined('ABSPATH')) {
    exit;
}
// outputs the contact form
function get_contact_form_data(){

    $feedback_message = '';

    // check if form was submitted
    if (isset($_POST['acf_submit'])) {

        // get and sanitize input values
        $name = isset($_POST['acf_name']) ? sanitize_text_field($_POST['acf_name']) : '';
        $email = isset($_POST['acf_email']) ? sanitize_email($_POST['acf_email']) : '';
        $message = isset($_POST['acf_message']) ? sanitize_textarea_field($_POST['acf_message']) : '';

        // basic validation
        if (!empty($name) && !empty($email) && !empty($message)) {

           

            // send email using send_email function
            $sent = send_email($name,$email,$message);

             //insert the email in database
            global $wpdb;
            $table_name = $wpdb->prefix . 'user_messages';

            $wpdb->insert(
                $table_name,
                array(
                    'name'    => $name,
                    'email'   => $email,
                    'message' => $message,
                    'time'    => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s')
            );

            if ($sent) {
                $feedback_message = 'Thank you!! your message was sent successfully !';
                $_POST = array();
            } else {
                $feedback_message = 'Sorry, there was a problem with sending your message.';
            }

        } else {
            $feedback_message = 'All fields are requiared ';
        }
    }

    
    ob_start();

    // show feedback
    if (!empty($feedback_message)) {
        echo '<p>' . esc_html($feedback_message) . '</p>';
    }
    ?>

    <form method="post">
        <p>
            <label for="acf_name">Your Name</label><br>
            <input type="text" id="acf_name" name="acf_name" required>
        </p>

        <p>
            <label for="acf_email">Your Email</label><br>
            <input type="email" id="acf_email" name="acf_email" required>
        </p>

        <p>
            <label for="acf_message">Your Message</label><br>
            <textarea id="acf_message" name="acf_message" rows="5" required></textarea>
        </p>

        <p>
            <button type="submit" name="acf_submit">Submit</button>
        </p>

    </form>

    <?php

    return ob_get_clean();
}

function send_email( $name, $email, $message) {
   
    $to      = 'test@example.com'; 
    $subject = 'New message from Arshia Contact Form';
    $body    = "Name: $name\nEmail: $email\nMessage:\n$message";
    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    // sends email using WordPress function
    return wp_mail( $to, $subject, $body, $headers );


}

function create_email_database() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_messages';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(200) NOT NULL,
        email varchar(200) NOT NULL,
        message text NOT NULL,
        time datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}


//create database for onetime
register_activation_hook( __FILE__, 'create_email_database' );
// register the shortcode
add_shortcode('arshia_contact_form', 'get_contact_form_data');