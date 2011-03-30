<?php
class Bain_Plist{

	private $_maxDepth;
	private $_types;
	private $_guessType;
	private $_data;
	
	public function __construct($data, $maxDepth=null, $types=array(),$guessType=false){
		if(!is_null($maxDepth)){
			$this->_maxDepth = $maxDepth;
		}
		$this->_types = $types;
		$this->_guessType = false;
		$this->_data = $data;
	}
	
	public function setTypes($types,$merge=false){
		if(true==$merge){
			array_merge($this->_types,$types);
		} else {
			$this->_types = $types;
		}
	}
	
	public function __toString(){
		return $this->toXml($this->_data);
	}
	
	protected function toXml($data,$depth=0,$dict=true){
		if(empty($data)){
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
	
	private function getType($keyName){
		if(array_key_exists($keyName,$this->_types)){
			return $this->_types[$keyName];
		} 
		//@todo make it guess the type if $this->_guessType is true - is a performance issue
		return 'string';
	}

	
	
	

}
