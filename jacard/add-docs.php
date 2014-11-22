<?php
require_once 'c-config.php';
ini_set('max_execution_time',0);
//sleep(121);
//phpinfo();die;
$app=new App();
$docs=$cdb->get_col("SELECT * FROM `docs` ORDER BY `ID` ASC ");

foreach($docs as $docID){
    $app->jaccard->updateDocIdentifier($docID);
}
