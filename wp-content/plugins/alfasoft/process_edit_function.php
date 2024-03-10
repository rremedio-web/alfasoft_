<?php
require_once('wp-load.php');

global $wpdb;
$people_table = $wpdb->prefix . 'people';

if (isset($_POST['edit_person'])) {
    $id = intval($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    // Validate name length
    if (strlen($name) <= 5) {
        // Redirect back with an error message
        wp_redirect(add_query_arg('error', 'name', '/manage-person-page'));
        exit;
    }

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Redirect back with an error message
        wp_redirect(add_query_arg('error', 'email', '/manage-person-page'));
        exit;
    }

    $wpdb->update($people_table, array(
        'name' => $name,
        'email' => $email
    ), array('id' => $id));

    // Redirect to the manage person page upon successful update
    wp_redirect('/manage-person-page');
    exit;
}
?>
