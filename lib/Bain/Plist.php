<?php
/**
 * Bain_Plist - Convert an associate array into a Plist representation
 * 
 * @author Paul Bain
 * @package Bain
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Bain_Plist{

    private $_maxDepth;
    private $_types;
    private $_guessType;
    private $_data;
   
    /**
     * 
     * @param $data
     * @param $maxDepth
     * @param $types
     * @param $guessType
     */
    public function __construct($data, $maxDepth=null, $types=array(),$guessType=false){
        if(!is_null($maxDepth)){
            $this->_maxDepth = $maxDepth;
        }
        $this->_types = $types;
        $this->_guessType = false;
        $this->_data = $data;
    }

    /**
     * Set the mappings used when checking field types in key=>type paris
     * @param $types
     * @param $merge
     */
    public function setTypes($types,$merge=false){
        if(true==$merge){
            array_merge($this->_types,$types);
        } else {
            $this->_types = $types;
        }
    }

    /**
     * Magic method to convert the object into a string
     * @retun string 
     */
    public function __toString(){
        return $this->toXml($this->_data);
    }

    /**
     * Do the conversion recursively, check for a max depth
     * @param mixed $data
     * @param int $depth
     * @param boolean $dict - whether we should add a dict wrapper
     */
    protected function toXml($data,$depth=0,$dict=true){
        if(empty($data) || !$this->isAllowedDepth($depth)){
            return '';
        }
        if($depth>0){
            $xml='';
            foreach($data as $k=>$v){
                if(is_array($v)){
                    if(empty($v) || is_numeric(key($v))){
                        $xml.='<key>'.$k.'</key>';
                        $xml.='<array>'.$this->toXml($v,$depth+1,false).'</array>';
                    } else {
                        $xml.='<key>'.$k.'</key>';
                        $xml.=$this->toXml($v,$depth+1);
                    }
                } else {
                    $xml.='<key>'.$k.'</key>';
                    $type = $this->getType($k);
                    switch($type){
                        case 'boolean':
                            $xml.='<'.(($v)?'true':'false').'/>';
                            break;
                        case 'real':
                        case 'integer':
                            if(empty($v)){
                                $v=0;
                            }
                        default:
                            $xml.='<'.$type.'>'.htmlspecialchars($v,null,'UTF-8').'</'.$type.'>';
                    }
                }
            }
            if($dict){
                $xml='<dict>'.$xml.'</dict>';
            }        
            return $xml;
        }
        return '<?xml version="1.0" encoding="UTF-8"?><plist version="1.0">'.$this->toXml($data, $depth+1).'</plist>';
    }
    
    /**
     * Check for max depth based on current depth
     * @param $depth
     */
    private function isAllowedDepth($currentDepth){
        if(!is_null($this->_maxDepth) && $currentDepth > $this->_maxDepth){
            return false;
        }
        return true;
    }
    

    /**
     * Get they type of the based on key, guess at string by default
     * for the moment as it's muuuch faster.
     * @param $keyName
     */
    private function getType($keyName){
        if(array_key_exists($keyName,$this->_types)){
            return $this->_types[$keyName];
        } 
        //@todo make it guess the type if $this->_guessType is true - is a performance issue
        return 'string';
    }
}
