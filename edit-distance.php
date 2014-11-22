<?php
function edit_distance($s1,$s2){
    $s1=trim($s1);
    $s2=trim($s2);
    $m=array();

    $l1=strlen($s1);
    $l2=strlen($s2);
    for($i=0;$i<=$l1;$i++){
        for($j=0;$j<=$l2;$j++){
            $m[$i][$j]=0;
        }
    }
    for($i=1;$i<=$l1;$i++){
        $m[$i][0]=$i;
    }
    for($j=1;$j<=$l2;$j++){
        $m[0][$j]=$j;
    }

    for($i=1;$i<=$l1;$i++){
        for($j=1;$j<=$l2;$j++){
            $v1=$m[$i-1][$j-1]+($s1[$i-1]!=$s2[$j-1]);
            $v2=$m[$i-1][$j]+1;
            $v3=$m[$i][$j-1]+1;
            $m[$i][$j]=min($v1,$v2,$v3);
            //printf('m[%s][%s]=%s(%s,%s,%s)<br>',$i,$j,$m[$i][$j],$v1,$v2,$v3);
        }
    }

    return $m[$l1][$l2];
}
header('Content-Type: text/html; charset=utf-8');
$title='Khoảng cách chỉnh sửa';
?>
<html>
<head>
    <title><?php echo $title;?></title>
    <meta name="og:title" value="<?php echo $title;?>"/>
</head>
<form method="post">
    Word 1: <input style="width:60%" type="text" name="encode" value="<?php echo isset($_POST['encode'])?$_POST['encode']:'';?>"/>

    <br>

    Word 2: <input style="width:60%" type="text" name="decode" value="<?php echo isset($_POST['decode'])?$_POST['decode']:'';?>"/>
    <hr>
    <?php if(!empty($_POST['encode'])&&!empty($_POST['decode'])):?>
        Distance= <?php echo edit_distance($_POST['encode'],$_POST['decode']);?>
    <?php endif;?>



    <br>
    <input type="submit" value="Submit" name="action"/>

</form>
</html>