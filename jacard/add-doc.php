<?php
require_once 'c-config.php';
ini_set('max_execution_time',0);
$app=new App();
$url=isset($_POST['url'])?$_POST['url']:'';
if($url){
    if($app->addDocByUri($url)){
        c_redirect(add_query_arg('message','Doc added successfully!','./'));
    }else{
        c_redirect(add_query_arg('message','Failed to add document! Already exists!','./'));
    }
}
c_redirect('./');
