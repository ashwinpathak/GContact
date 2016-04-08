<?php

include_once '../config.php';
include_once '../class.GContacts.php';

/*
 * Before running this example please make sure that you have
 * setted the redirect URL to this file. So that when authentication
 * is done then we'll be seeing the emails on this page.
 */

$g = new GContacts($google['id'], $google['secret'], $google['redirect'], $google['max_results']);

if (isset($_GET['code'])) {
    $code = htmlentities($_GET['code']);

    // getting the email list, make sure to pass the code as the parameter for GContacts::getList() function.
    if ($list = $g::getList($code)) {
        // checking whether it's true or not.

        // Print the number or email imported
        echo '<h2>Total Emails: ' . $g::countEmails() . '</h3>';

        foreach ($list as $email) {
            // Sending email code should go in the loop. So that we send email to all.
            echo $email . '<br />';
        }

        // CHECK WHETHER EMAIL IS IN OR OUT!
        if ($g::emailExists('abc@gmail.com')) {
            echo '<h2>example@example.com already exists!</h2>';
        }

    } else {
        die('An unkown error occured!');
    }
} else {
    // we are using GContacts::getURL() static function to get the authentication URL.
    echo '<a href="' . $g::getURL() . '"><h3>Click here to import emails</h3></a>';
}
