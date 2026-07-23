<?php
// ===== Mail (SMTP) configuration =====
// Uses Gmail SMTP + an App Password. To set this up:
//
// 1. Turn on 2-Step Verification on the Gmail account you'll send from
//    (Google Account → Security → 2-Step Verification).
// 2. Go to https://myaccount.google.com/apppasswords
// 3. Create an app password (choose "Mail" as the app). Google will give
//    you a 16-character code like "abcd efgh ijkl mnop".
// 4. Paste that code below as MAIL_PASSWORD (spaces don't matter, but we
//    strip them anyway just in case).
//
// IMPORTANT: don't commit your real app password to a public repo. If
// this project's git repo is public, move these two lines into a
// separate untracked file (e.g. mail_secrets.php, add it to .gitignore)
// and require_once it here instead.

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your.tpmc.itdesk@gmail.com'); // <-- palitan ng sender Gmail address
define('MAIL_PASSWORD', 'xxxxxxxxxxxxxxxx');            // <-- palitan ng 16-char App Password (walang space)
define('MAIL_FROM_NAME', 'TPMC IT Concern Desk');
