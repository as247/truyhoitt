<?php
/**
 * @var cdb $cdb
 */
/**
 * Class Shingle
 */
class Shingle{
    var $n=2;
    var $doc;
    var $docWords=array();
    var $docShingles=array();
    var $shingleTable='shingle';
    function __construct(){

    }

    /**
     * @param string $doc
     * @param int $length
     * @return int[] shingle indexes
     */
    function getDocShingles($doc='',$length=2){
        $wordShingles=$this->getDocWordShingles($doc,$length);
        return $this->addShingles($wordShingles,$length);

    }

    /**
     * Add shingles to database if it's not exists
     * @param $words
     * @param int $length
     * @return int[]
     */
    function addShingles($words,$length=2){
        global $cdb;
        $table=$this->getShingleTable($length);
        if(!$table){
            c_exit('could not get shingle table '.$table);
        }
        $shingles=array();
        foreach((array)$words as $word){
            if(!$id=$cdb->get_var($cdb->prepare("select ID from $table where word=%s",$word))){
                $id=$cdb->insert($table,array('word'=>$word));
            }
            $shingles[]=$id;
        }
        return $shingles;
    }

    function getShingleTable($length){
        global $cdb;
        $table= $this->shingleTable.$length;
        $charLength=10*$length+5;
        if($cdb->get_var("SHOW TABLES LIKE '$table'")==$table) {
            return $table;
        }else{
            $sql=array();
            $sql[]="CREATE TABLE `$table` (
`ID` bigint(20) unsigned NOT NULL auto_increment,
  `word` varchar($charLength) NOT NULL,
  PRIMARY KEY (ID),KEY (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

            foreach ($sql as $q) {
                $cdb->query($q);
            }


        }
        if($cdb->get_var("SHOW TABLES LIKE '$table'")==$table) {
            return $table;
        }
        return false;

    }

    /**
     * Get doc shingle as word
     * @param string $doc
     * @param int $length
     * @return mixed
     */
    function getDocWordShingles($doc='',$length=0){
        if($doc){
            $this->setDoc($doc);
        }
        if($length>0){
            $this->setLength(($length));
        }
        if($length<2){
            return $this->getDocWords();
        }
        if(!$this->docShingles[$this->n]){
            $words=$this->getDocWords();
            $total=count($words);
            $stop=$total-$this->n+1;
            for($i=0;$i<$stop;$i++){
                $shingle='';
                $stop2=$i+$this->n;
                for($j=$i;$j<$stop2;$j++){
                    $shingle.=$words[$j].' ';
                }
                $this->docShingles[$this->n][]=trim($shingle);
            }
        }
        return $this->docShingles[$this->n];
    }
    function &getDocWords(){
        if(empty($this->docWords)){
            $this->docWords=explode(' ',$this->doc);
            $this->docWords=array_filter($this->docWords);
        }
        return $this->docWords;
    }
    function setDoc($doc){
        if($doc!=$this->doc){
            $this->clean();
        }
        $this->doc=$doc;
        return $this;
    }
    function setLength($n){
        $n=abs(intval($n));
        $this->n=$n;
        return $this;
    }
    function clean(){
        $this->docWords=array();
        $this->docShingles=array();
    }
}