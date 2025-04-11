<?php
require 'mail_config.php';

if (sendEmail(
    'test@eventhub.local', 
    'MailHog Test', 
    'This email was sent via MailHog!'
)) {
    echo "Email sent! Check MailHog at <a href='http://localhost:8025'>http://localhost:8025</a>";
} else {
    echo "Failed to send. Check PHP error logs.";
}