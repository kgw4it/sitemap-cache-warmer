<?php
return array(
    'key' => $_ENV['SECRET_KEY'], // Secret key to allow traversing sitemaps
    'reportProblematicUrls' => strlen($_ENV['REPORT_PROPLEMATIC_URLS_TOMAIL']) > 0 ? true : false,
    'reportProblematicUrlsTo' => $_ENV['REPORT_PROPLEMATIC_URLS_TOMAIL'],
    'SMTP_HOST' => $_ENV['SMTP_HOST'],
    'SMTP_PORT' => $_ENV['SMTP_PORT'],
    'SMTP_MAIL_FROM' => $_ENV['SMTP_MAIL_FROM'],
);
