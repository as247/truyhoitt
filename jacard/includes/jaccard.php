<?php
class JACCARD{
    var $m;
    var $hashFunc=array(
    );
    /**
     * @var Shingle
     */
    var $shingle;
    var $doc;
    /**
     * @var cdb
     */
    var $cdb;
    function __construct(){
        global $cdb;
        $this->shingle=new Shingle();
        $this->cdb=$cdb;
        $this->getHashFunc();
    }

    /**
     * get top of matching doc with current doc
     * @param int $length Shingle length
     * @param int $limit number of documents
     * @return array array of docId=>jaccard value
     */
    function getTopMatchedDocs($length,$limit=10){
        $length=absint($length);
        $limit=absint($limit);
        $docIdentifies=$this->getDocIdentifier($length);
        $queries=array();
        for($i=0;$i<$this->m;$i++){
            $index=$i+1;
            $queries[]="(i{$length}$index=".$docIdentifies[$i].')';
        }
        $jselect=join('+',$queries);
        $query="select ID,$jselect as jaccard from docs order by jaccard desc limit $limit";
        //echo $query;
        $jaccardValues=$this->cdb->get_results($query);
        $results=array();
        if($jaccardValues){
            foreach($jaccardValues as $j){
                $results[$j->ID]=$j->jaccard/$this->m;
            }
        }
        return $results;
    }
    function getMatchedDoc($jvalue=0.5,$shingleLength=2){
        $topMatchedDocs=$this->getTopMatchedDocs($shingleLength,1);
        foreach($topMatchedDocs as $id=>$j){
            if($j>=$jvalue){
                return array('id'=>$id,'jaccard'=>$j);
            }
        }
        return false;

    }
    function createDocIdentifierColumns($length=2){
        $this->getHashFunc();
        $columns=array();
        $docCols=$this->cdb->get_results("SHOW COLUMNS FROM docs");
        $existsColumns=array();
        foreach((array)$docCols as $col){
            $existsColumns[]=$col->Field;
        }
        foreach((array)$length as $l){
            for($i=1;$i<=$this->m;$i++){
                $field="i$l$i";
                if(!in_array($field,$existsColumns)) {
                    $columns[] = $field;
                }
            }
        }
        if(!$columns){
            return false;
        }
        $add_cols=array();
        $add_index=array();
        foreach($columns as $col){
            $add_cols[]="ADD `$col` INT UNSIGNED NOT NULL";
            $add_index[]="`$col`";
        }
        $query='ALTER TABLE `docs`'.join(', ',$add_cols).', ADD INDEX('.join(', ',$add_index).')';
        return $this->cdb->query($query);

    }
    /**
     * update doc identifies
     * @param $docID
     * @param $length
     * @return int
     */
    function updateDocIdentifier($docID,$length=2){
        $doc=$this->cdb->get_var($this->cdb->prepare("select content from docs where ID=%d limit 1",$docID));
        if(!$doc){
            return false;
        }
        $this->setDoc($doc);
        $data=array();
        foreach((array)$length as $shingleLength){
            $docIdentifies=$this->getDocIdentifier($shingleLength);
            for($i=0;$i<$this->m;$i++){
                $index=$i+1;
                $data["i{$length}$index"]=$docIdentifies[$i];
            }
        }
        return $this->cdb->update('docs',$data,array('ID'=>$docID));
    }

    /**
     * get jaccard identifies value of document
     * @return array
     */
    function getDocIdentifier($length=2){
        $shingles=$this->shingle->getDocShingles($this->doc,$length);
        $shingle_values=array_unique($shingles);//remove duplicate value
        $hashfuncs=$this->getHashFunc();
        $jaccard=array();
        for($i=0;$i<$this->m;$i++){
            foreach($shingle_values as $shingle_value){
                //echo $shingle_value .' ';
                $value=call_user_func($hashfuncs[$i],$shingle_value);
                if(!isset($jaccard[$i])){
                    $jaccard[$i]=$value;
                }else{
                    $jaccard[$i]=min($jaccard[$i],$value);
                }
            }
        }
        return $jaccard;
    }
    function calculateIdentifier($shingles,$hashFuncs=array()){
        $shingle_values=array_unique($shingles);//remove duplicate value
        $hashfuncs=$this->getHashFunc();
        $jaccard=array();
        for($i=0;$i<$this->m;$i++){
            foreach($shingle_values as $shingle_value){
                //echo $shingle_value .' ';
                $value=call_user_func($hashfuncs[$i],$shingle_value);
                if(!isset($jaccard[$i])){
                    $jaccard[$i]=$value;
                }else{
                    $jaccard[$i]=min($jaccard[$i],$value);
                }
            }
        }
        return $jaccard;
    }
    function setDoc($doc){
        $this->doc=$doc;
        return $this;
    }
    function setHashFunc($functions){
        $this->hashFunc=$functions;
    }

    function getHashFunc(){
        if(!is_array($this->hashFunc)||count($this->hashFunc)<2){
            global $hash_func;
            $this->setHashFunc($hash_func);
        }
        $this->m=count($this->hashFunc);
        return $this->hashFunc;
    }

}