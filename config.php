<?php
return array(
    'key' => $_ENV['SECRET_KEY'], // Secret key to allow traversing sitemaps
    'reportProblematicUrls' => strlen($_ENV['REPORT_PROPLEMATIC_URLS_TOMAIL']) > 0 ? true : false,
    'reportProblematicUrlsTo' => $_ENV['REPORT_PROPLEMATIC_URLS_TOMAIL'],
);
