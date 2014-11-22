<?php
/** MySQL database name */
define('DB_NAME', 'jaccard');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');
define('DB_CHARSET','utf8');
define('DIR', dirname(__FILE__) . '/');
$hash_func=array(
    //1
    function($x){return j_hash($x,'Jw2YjWure7');},
    //2
    function($x){return j_hash($x,'FdK56YQrQb');},
    //3
    function($x){return j_hash($x,'9zKlCur6Og');},
    //4
    function($x){return j_hash($x,'PDsbTaWPf2');},
    //5
    function($x){return j_hash($x,'Qyut1hfiL6');},
    //6
    function($x){return j_hash($x,'BCjfGqhdBw');},
    //7
    function($x){return j_hash($x,'lgH9BQ1pwF');},
    //8
    function($x){return j_hash($x,'W9Z5lwWr59');},
    //9
    function($x){return j_hash($x,'6MxvGSi7k6');},
    //10
    function($x){return j_hash($x,'vhHftmQEZG');},

);
require_once(DIR . 'common.php');
?>