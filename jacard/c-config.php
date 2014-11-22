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
    function($x){return 500000-(floor($x*$x/500000+2*$x)+5)%500000;},
    //2
    function($x){return 500000-(3*$x+7)%500000;},
    //3
    function($x){return absint((65536*$x+11))%500000;},
    //4
    function($x){return absint((16777216*$x+110))%500000;},
    //5
    function($x){return (16777216-(101*$x+127)%500000)%500000;},
    //6
    function($x){return (41*$x+250000)%500000;},
    //7
    function($x){return (9*$x+123456)%500000;},
    //8
    function($x){return (15*$x+400000)%500000;},
    //9
    function($x){return (floor($x*$x/500000)+431110)%500000;},
    //10
    function($x){return (256*$x+30000)%500000;},

);
require_once(DIR . 'common.php');
?>