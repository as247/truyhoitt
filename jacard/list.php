<?php
require_once 'c-config.php';
header('Content-Type: text/html; charset=utf-8');
$page=isset($_GET['page'])?absint($_GET['page']):1;
$allDoc=$cdb->get_results("select content,url from docs");
?>
<ol>
<?php
foreach($allDoc as $doc):?>
    <li><a href="<?php echo $doc->url;?>"><?php echo $doc->url;?></a></li>
<?php endforeach;?>
</ol>