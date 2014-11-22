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
    </pre>


<?php else:?>
    No doc matched
<?php endif;?>
    <hr>
    Thời gian chạy: <?php timer_stop(1);?> giây