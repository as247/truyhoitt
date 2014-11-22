<?php
function gamma_encode($numbers){
    if(!is_array($numbers)){
        $numbers=str_replace(array(',',';','|'),' ',$numbers);
        $numbers=preg_replace('/\s+/',' ',$numbers);
        $numbers=trim($numbers);
        $numbers=explode(' ',trim($numbers));
    }
    $encoded=array();
    foreach($numbers as $number){
        $bin=decbin($number);
        $offset=substr($bin,1);
        $length=strlen($offset);
        $encoded[]=int_one_encode($length).$offset;
    }
    return join('',$encoded);
}
function gamma_decode($encoded){
    $numbers=array();
    $encoded=str_replace(' ','',$encoded);
    if($encoded[0]!=1){
        //return -1;
    }
    $length=0;
    $stop=strlen($encoded);
    for($i=0;$i<$stop;$i++){
        $bit=$encoded[$i];

        if($bit==1){
            $length+=1;
        }else{//end length
            $offset=substr($encoded,$i+1,$length);
            $numbers[]=bindec('1'.$offset);
            $i=$i+$length;
            $length=0;
        }
    }
    return join(' ',$numbers);
}

function int_one_encode($n){
    $n=abs(intval($n));
    return str_repeat('1',$n).'0';

}
header('Content-Type: text/html; charset=utf-8');
$title='Mã gamma';
?>
<html>
<head>
    <title><?php echo $title;?></title>
    <meta name="og:title" value="<?php echo $title;?>"/>
</head>
<form method="post">
    Number to encode(eg:5 131 120): <input style="width:60%" type="text" name="encode" value="<?php echo isset($_POST['encode'])?$_POST['encode']:'';?>"/>

    <?php if(!empty($_POST['encode'])):?>
        <br/>
        Encoded: <?php echo gamma_encode($_POST['encode']);?>
    <?php endif;?>
    <hr>

    Number to decode: <input style="width:60%" type="text" name="decode" value="<?php echo isset($_POST['decode'])?$_POST['decode']:'';?>"/>

    <?php if(isset($_POST['decode'])):?>
        <br/>
        Decoded: <?php $decoded= gamma_decode($_POST['decode']);
        if($decoded==-1){
            echo 'Failed to decode! Invalid input';
        }else{
            echo $decoded;
        }
        ?>
    <?php endif;?>
    <br>
    <input type="submit" value="Submit" name="action"/>

</form>
</html>