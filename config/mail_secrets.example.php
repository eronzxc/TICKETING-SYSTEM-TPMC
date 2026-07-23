<?php
// ===== EXAMPLE FILE — copy this, don't edit this one directly =====
//
// 1. Duplicate this file in the same folder and rename the copy to:
//        mail_secrets.php
// 2. Fill in your real Gmail address and App Password below (in the
//    COPY, not here).
// 3. mail_secrets.php is already listed in .gitignore, so it will never
//    be committed/pushed — only mail_secrets.example.php (this template)
//    goes to GitHub.
//
// How to get an App Password:
// a. Turn on 2-Step Verification on the Gmail account you'll send from
//    (Google Account → Security → 2-Step Verification).
// b. Go to https://myaccount.google.com/apppasswords
// c. Create an app password (choose "Mail"). Google gives you a
//    16-character code like "abcd efgh ijkl mnop" — spaces don't matter.

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your.tpmc.itdesk@gmail.com'); // <-- palitan
define('MAIL_PASSWORD', 'xxxxxxxxxxxxxxxx');            // <-- palitan (App Password)
define('MAIL_FROM_NAME', 'TPMC IT Concern Desk');
