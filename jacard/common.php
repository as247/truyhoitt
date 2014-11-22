<?php
error_reporting(E_ALL^E_NOTICE);
include(DIR.'includes/functions.php');
include(DIR.'includes/html-dom-parser.php');
include(DIR.'includes/array2xml.php');
include(DIR .'includes/readability.php');
include(DIR .'includes/ContentExtract.php');
include(DIR . 'includes/Shingle.php');
include(DIR . 'includes/http.php');
include(DIR . 'includes/jaccard.php');
include(DIR . 'includes/app.php');
require_db();

timer_start();