<?php
return array(
    'key' => getenv('SECRET_KEY'), // Secret key to allow traversing sitemaps
    'reportProblematicUrls' => strlen(getenv('REPORT_PROPLEMATIC_URLS_TOMAIL')) > 0 ? true : false,
    'reportProblematicUrlsTo' => getenv('REPORT_PROPLEMATIC_URLS_TOMAIL'),
    'SMTP_HOST' => getenv('SMTP_HOST'),
    'SMTP_PORT' => getenv('SMTP_PORT'),
    'SMTP_MAIL_FROM' => getenv('SMTP_MAIL_FROM'),
    'ignoreUrls' => explode("\n", getenv('IGNORE_URLS')),
);
