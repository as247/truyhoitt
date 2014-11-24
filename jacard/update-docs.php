<?php
/**
 * update shingles or doc identifier
 */
require_once 'c-config.php';
if(!isset($_GET['id']))
    result_stop('No id');
$id=$_GET['id'];
$page=$_GET['page'];
$app=new App();
$app->setShingleLength(2);
//$updated=$app->updateShingles($id);
$updated=$app->updateDocIdentifier($id);
if($updated){
    result_success();
}else{
    //echo $cdb->last_query;
    result_notice('already updated');
}