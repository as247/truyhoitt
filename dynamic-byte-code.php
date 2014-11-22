<?php
function vbEncodeNumber($number){
    $bytes=array();
    while(true){
        array_unshift($bytes,$number%128);
        if($number<128){
            break;
        }
        $number=floor($number/128);
    }
    $bytes[count($bytes)-1]+=128;
    $encoded='';
    $stop=count($bytes);
    for ($i=0;$i<$stop;$i++) {
        $_byte=str_pad(decbin($bytes[$i]),8,'0',STR_PAD_LEFT);
        if($i==$stop-1){//last byte
            $_byte[0]=1;
        }

        $encoded.=' '. $_byte;
    }

    return trim($encoded);

}
function vbEncode($numbers){
    if(!is_array($numbers)){
        $numbers=str_replace(array(',',';','|'),' ',$numbers);
        $numbers=preg_replace('/\s+/',' ',$numbers);
        $numbers=trim($numbers);
        $numbers=explode(' ',$numbers);
    }
    $encoded='';
    foreach($numbers as $number){
        $encoded.=' '.vbEncodeNumber($number);
    }
    return trim($encoded);
}
function vbDecode($byte){
    $byte=str_replace(' ','',$byte);
    $length=strlen($byte);
    if($length%8!=0){
        return -1;
    }
    $numbers=array();
    $byteStreams=array();
    foreach(str_split($byte,8) as $b){

        if($b[0]==1){//last byte
            $b[0]='0';
            $byteStreams[]=bindec($b);
            $theNumber=0;
            foreach($byteStreams as $numInByte){
                if($numInByte<128){
                    $theNumber=$theNumber*128+$numInByte;
                }else{
                    $theNumber=$theNumber*128+($numInByte-128);
                }
            }
            $numbers[]=$theNumber;
            $byteStreams=array();
        }else{
            $byteStreams[]=bindec($b);
        }
    }
    return join(' ',$numbers);
}
header('Content-Type: text/html; charset=utf-8');
?>
<html>
<head>
    <title>Mã byte động</title>
    <meta name="og:title" value="Mã byte động"/>
</head>
<form method="post">
    Number to encode(eg:5 131 120): <input style="width:60%" type="text" name="encode" value="<?php echo isset($_POST['encode'])?$_POST['encode']:'';?>"/>

    <?php if(!empty($_POST['encode'])):?>
        <br/>
        Encoded: <?php echo vbEncode($_POST['encode']);?>
    <?php endif;?>
    <hr>

    Number to decode: <input style="width:60%" type="text" name="decode" value="<?php echo isset($_POST['decode'])?$_POST['decode']:'';?>"/>

    <?php if(!empty($_POST['decode'])):?>
        <br/>
        Decoded: <?php $decoded= vbDecode($_POST['decode']);
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