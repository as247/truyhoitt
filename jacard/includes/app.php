<?php
class App{
    var $shingleLength=2;
    var $matchThreshold=0.5;
    var $doc;
    /**
     * @var ContentExtract
     */
    var $contentExtract;

    /**
     * @var JACCARD
     */
    var $jaccard;
    /**
     * @var cdb
     */
    var $cdb;

    var $lastMatchedDoc;

    function __construct(){
        global $cdb;
        $this->cdb=$cdb;
        $this->contentExtract=new ContentExtract(null,array('strip_tags'=>true,'text_only'=>true,'lowercase'=>true));
        $this->jaccard=new JACCARD();

    }
    function setShingleLength($length){
        $this->shingleLength=$length;
        return $this;
    }
    function setMatchThreshold($threshold){
        $this->matchThreshold=$threshold;
        return $this;
    }

    function addDocByUri($url){
        $doc=array('url'=>$url);
        if($this->contentExtract->load($url)){
            //$doc['fullcontent']=$this->contentExtract->getContent();
            $doc['content']=$this->contentExtract->getContent(true);
        }
        return $this->addDoc($doc);
    }

    /**
     * Add Doc and update it's jaccard value
     * @param array $doc
     * @return mixed
     */
    function addDoc(array $doc,$check=true){
        if(empty($doc['content'])){
            return false;
        }
        if($check&&$matchedDoc=$this->jaccard->setDoc($doc['content'])->getMatchedDoc($this->matchThreshold,$this->shingleLength)){
            $this->log(sprintf("Matches doc found: ".$matchedDoc['id']));
            $this->lastMatchedDoc=$matchedDoc;
            return false;
        }
        if($doc_id=$this->cdb->insert('docs',$doc)){
            //$this->jaccard->updateDocIdentifier($doc_id);
        }
    }
    function checkDoc($url_or_content){
        $url_or_content=trim($url_or_content);
        if(strpos($url_or_content,'http://')===0||strpos($url_or_content,'https://')===0){
            return $this->checkDocByUrl($url_or_content);
        }
        return $this->checkDocByContent($url_or_content);
    }
    function checkDocByUrl($url){
        if(!$this->contentExtract->load($url)){
            return false;
        }
        $this->doc=$content=$this->contentExtract->getContent(true);
        return $this->checkDocByContent($content);
    }
    function checkDocByContent($content){
        return $this->jaccard->setDoc($content)->getMatchedDoc($this->matchThreshold,$this->shingleLength);
    }
    function log($message){
        file_put_contents(DIR.'add.log',$message.PHP_EOL,FILE_APPEND);
    }
}