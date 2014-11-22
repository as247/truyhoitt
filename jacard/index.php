<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors',true);
require_once 'c-config.php';
$app=new App();
$url=isset($_GET['url'])?$_GET['url']:'';
$input_content=isset($_POST['content'])?$_POST['content']:'';
$doc=$url;
if($input_content){
    $doc=$input_content;
}
$check=$app->checkDoc($doc);
$content=$app->doc;

?>
<form action="" method="GET">
    <label for="url">Url:</label>
    <input id="url" type="text" style="width:100%;" name="url" value="<?php echo $url;?>"/>
    <input type="submit" value="check">
</form>

<form action="index.php" method="POST">
    <label for="content">Content:</label><br/>
    <textarea id="content" rows="10" cols="100" name="content"><?php echo $input_content;?></textarea>
    <input type="submit" value="check">
</form>
<h1>Extracted Content:</h1>
<?php echo $content;?>
<h2>Check result</h2>
<?php if($check):
    $matchedDoc= $cdb->get_row($cdb->prepare("select content,url from docs where ID=%d",$check['id']));
    $content2=$matchedDoc->content;
    $shingle1=array_unique($app->jaccard->shingle->getDocShingles($content));
    $shingle2=array_unique($app->jaccard->shingle->getDocShingles($content2));
    ?>
Matched ID:<?php echo $check['id'];?><br/>
Matched jaccard:<?php echo $check['jaccard'];?><br/>
Matched content: <a target="_blank" href="<?php echo $matchedDoc->url;?>"><?php echo $matchedDoc->url;?></a> <br/>
<?php
    echo $content2;
?>
    <hr>
    Shingle 1:
    <textarea style="width: 100%; height: 100px;" readonly><?php echo join(',',$shingle1);?></textarea>

    <br/>
    Shingle 2: <textarea style="width: 100%; height: 100px;" readonly><?php echo join(',',$shingle2);?></textarea>
    <hr>
    Doc identifier1:<?php echo join(',',$app->jaccard->calculateIdentifier($shingle1));?><br/>
    Doc identifier2:<?php echo join(',',$app->jaccard->calculateIdentifier($shingle2));?>
    <hr>
    Hash functions:
    <pre>
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
    </pre>


<?php else:?>
    No doc matched
<?php endif;?>
    <hr>
    Thời gian chạy: <?php timer_stop(1);?> giây