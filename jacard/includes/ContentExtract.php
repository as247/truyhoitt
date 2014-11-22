<?php
class ContentExtract{
    var $url;
    var $options=array();
    var $originalContent;
    var $title;
    var $content;
    var $ready=false;
    var $errors=array();
    /**
     * @var MCHTTP
     */
    var $httpClient;

    function __construct($url=null,$options=array()){
        $this->setUrl($url);
        $this->setOption($options);
        $this->httpClient=new MCHTTP();
    }
    function load($url=null){
        if($url) {
            $this->setUrl($url);
        }
        $this->read();
        if(!$this->ready){
            $this->errors[]='failed-read-url';
            return false;
        }
        return $this->parse();

    }
    function getContent($process=false){
        if($process) {
            return $this->processContent($this->content);
        }
        return $this->content;
    }

    function processContent($content){

        if($this->getOption('strip_tags')||$this->getOption('text_only')){
            $content=strip_tags($content);
            //$content=$this->getContentText($content);
        }
        if(($signs=$this->getOption('remove_signs'))||$this->getOption('text_only')){
            $content=htmlspecialchars_decode($content);
            $content=html_entity_decode($content);
            if(!is_array($signs)){
                $signs=array('.',',','(',')','"',"'",'-',
                    ':','!','?',';',
                    "\n","\r","\n\r","\r\n");
            }
            $content=str_replace($signs,' ',$content);
            $content=preg_replace('#\s+#u',' ',$content);

        }
        if($this->getOption('lowercase')){
            $content=mb_strtolower($content,'UTF-8');
        }
        if($this->getOption('text_only')){
            $content=$this->_getContentText($content);
        }


        return trim($content);
    }
    function _getContentText($content){

        //$content = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $content);
        $chars = preg_split('//u', $content, -1, PREG_SPLIT_NO_EMPTY);
        $chars=array_map(function($char){if(ord($char)==194)return chr(32);return $char;},$chars);
        $content=join('',$chars);
        $content=preg_replace('#\s+#u',' ',$content);
        return trim($content);
    }
    function getTitle(){
        return $this->title;
    }
    function parse(){
        $mode=$this->getOption('mode','auto');
        switch($mode){
            case 'auto':
                $reader=new Readability($this->originalContent,$this->url);
                if($reader->init()){
                    $this->title=$reader->getTitle()->innerHTML;
                    $this->content=$reader->getContent()->innerHTML;
                    return true;
                }else{
                    $this->errors[]='auto-parse-failed';
                }
                break;
            case 'regex':
                $before = $this->getOption('before_content');
                $after = $this->getOption('after_content');
                if(!$before||!$after){
                    return false;
                }
                preg_match('#'.$before.'(.*?)'.$after.'#is',$this->originalContent,$match);
                $this->content=$match[1];
                if($this->content){
                    return true;
                }
                break;
            case 'dom':
                $find=$this->getOption('dom');
                $index=$this->getOption('dom_index');
                $index=abs(intval($index));
                $html=str_get_html($this->originalContent);
                if($find&&$content=$html->find($find,$index)) {
                    $this->content = $content->innertext();
                }
                if($this->content){
                    return true;
                }
                return false;

        }
        return false;
    }
    function read($url=false){
        if(!$url){
            $url=$this->url;
        }
        $this->originalContent=$this->httpClient->read($url);
        if($this->originalContent){
            $this->ready=true;
        }

    }
    function setUrl($url){
        $this->url=$url;
        return $this;
    }
    function setMode($mode){
        return $this->setOption('mode',$mode);
    }
    function setOption($name,$value=null){
        if(is_array($name)){
            $this->options=array_merge($this->options,$name);
        }
        if(is_scalar($name)) {
            $this->options[$name] = $value;
        }
        return $this;
    }
    function getOption($name,$default=false){
        return isset($this->options[$name])?$this->options[$name]:$default;
    }
    function reset(){
        $this->url=null;
        $this->originalContent=null;
        $this->title=null;
        $this->content=null;
        $this->originalContent=null;
        $this->ready=false;
    }
}