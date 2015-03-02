<?php
/**
 * EActiveRecordAx
 *	Allow a CActiveRecord based class to have extra attributes stored in a blob
 *	see also: README.md in this directory.
 *
 * @author Cristian Salazar H. <christiansalazarh@gmail.com> @salazarchris74 
 * @license FreeBSD {@link http://www.freebsd.org/copyright/freebsd-license.html}
 */
class EActiveRecordAx extends CActiveRecord {
	private $_ax_settings;
	private $_ax_record;

	public function __get($name)
	{
		if($entry = $this->ax_matchEntry($name)){
			list($field_defval) = $entry;
			$this->_ax_settings = $this->ax_settings();
			return $this->ax_getrecord("get", $name, $field_defval);
		}else
		return parent::__get($name);
	}
	public function __set($name, $value)
	{
		if($entry = $this->ax_matchEntry($name)){
			$this->_ax_settings = $this->ax_settings();
			$this->ax_getrecord("set", $name, $value);
			return;
		}else
		return parent::__set($name, $value);
	}
	/**
	 * getAttributes 
	 *	 1. ensure derivated instances to use the declared attributeNames() method 
	 *   2. add the ax_data column to allow db operations such insert or update 
	 * 
	 * @param mixed $names 
	 * @access public
	 * @return array	 
	 */
	public function getAttributes($names=null)
	{
		// about attributeNames: this method should be overrided here
		// to allow attributeNames() to overload using this instance.
		$values=array();
		list($ax_data) = $this->_ax_settings;
		$values[$ax_data] = $this->$ax_data;
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;
		if(is_array($names)){
			$values2=array();
			foreach($names as $name)
				$values2[$name]=isset($values[$name]) ? $values[$name] : null;
			return $values2;
		}else
		return $values;
	}
	/**
	 * setAttributes 
	 *	 1. ensure derivated instances to use the declared attributeNames() method 
	 *	 2. ensure derivated instances to use the declared getSafeAttributeNames() method 
	 * 
	 * @param mixed $values 
	 * @param mixed $safeOnly 
	 * @access public
	 * @return void
	 */
	public function setAttributes($values,$safeOnly=true){
	    if(!is_array($values))
	        return;
	    $attributes=array_flip($safeOnly ? 
			$this->getSafeAttributeNames() : $this->attributeNames());
	    foreach($values as $name=>$value){
			if(isset($attributes[$name]))
				$this->$name = $value;
			elseif($safeOnly)
				$this->onUnsafeAttribute($name,$value);
		}
	}
	public function getSafeAttributeNames(){
		$this->_ax_settings = $this->ax_settings();
		list($ax_data) = $this->_ax_settings;
		$attrs = parent::getSafeAttributeNames();
		$_attrs = array(); $match=false;
		foreach($attrs as $attr){
			if($ax_data == $attr) { $match=true;continue; }
			$_attrs[] = $attr;
		}
		if(true===$match){
			foreach($this->ax_enumAttributes() as $attr=>$data)
				$_attrs[] = $attr;
		}
		return $_attrs;
	}
	/**
	 * attributeNames
	 *	return the attributes list, plus adding those attributes declared
	 *	in config settings, also removing the ax_data attribute (the blob)
	 * 
	 * @access public
	 * @return void
	 */
	public function attributeNames(){
		$this->_ax_settings = $this->ax_settings();
		list($ax_data) = $this->_ax_settings;
		$attrs = parent::attributeNames();
		$_attrs = array(); $match=false;
		foreach($attrs as $attr){
			if($ax_data == $attr) { $match=true;continue; }
			$_attrs[] = $attr;
		}
		if(true===$match){
			foreach($this->ax_enumAttributes() as $attr=>$data)
				$_attrs[] = $attr;
		}
		return $_attrs;
	}
	/**
	 * beforeSave
	 *	save extra fields as a serialize/base64 encoded value into ax_data
	 * 
	 * @access public
	 * @return boolean
	 */
	public function beforeSave(){
		list($ax_data) = $this->_ax_settings;
		$this->$ax_data = base64_encode(serialize($this->_ax_record));
		return parent::beforeSave();
	}
// magic stuff here

	/**
	 * ax_matchEntry
	 *	match if the attribute_name is declared in the config settings.
	 * 
	 * @param string $attribute_name 
	 * @access private
	 * @return bool
	 */
	private function ax_matchEntry($attribute_name){
		$className = get_class($this);
		if(isset(Yii::app()->params['yiiattributex'][$className])){
			$entry = Yii::app()->params['yiiattributex'][$className];
			if(isset($entry[$attribute_name])){
				return $entry[$attribute_name];
			}
		}	
		return null;
	}
	/**
	 * ax_enumAttributes
	 *  return a list of those extra attributes declared in config settings
	 * 
	 * @access private
	 * @return array 
	 */
	private function ax_enumAttributes(){
		$result = array();
		$className = get_class($this);
		if(isset(Yii::app()->params['yiiattributex'][$className])){
			$entry = Yii::app()->params['yiiattributex'][$className];
			foreach($entry as $attribute_name=>$attr_data){
				if('settings' == $attribute_name) continue;
				$result[$attribute_name] = $attr_data;
			}
		}	
		return $result;
	}
	/**
	 * ax_settings
	 *  the settings to be used when this class is used (not instance)
	 *  currently only one value is accepted: the name of the blob field used
	 *	to store all the extra fields.
	 *	
	 *		'settings' => array('ax_data'),
	 *
	 * @access private
	 * @return void
	 */
	private function ax_settings(){
		// expected data: array('attribute_name')
		$className = get_class($this);
		if(isset(Yii::app()->params['yiiattributex'][$className]['settings'])){
			return Yii::app()->params['yiiattributex'][$className]['settings'];
		}
		return array('ax_data');
	}
	/**
	 * ax_getrecord
	 *	returns a lazy object holding the virtual object to be saved in ax_data
	 * 
	 * @param string $mode "get" or "set"
	 * @param string $attr_name 
	 * @param string $attr_value 
	 * @access private
	 * @return array
	 */
	private function ax_getrecord($mode, $attr_name, $attr_value=null){
		if(null === $this->_ax_record){
			list($ax_data) = $this->_ax_settings;
			$this->_ax_record = 
				unserialize(base64_decode(parent::__get($ax_data)));
			if(!is_array($this->_ax_record)){
				$this->_ax_record = array();
				foreach($this->ax_enumAttributes() as $_attr_name=>$attr_data)
					$this->_ax_record[$_attr_name] = "";
			}else{
				// all fine
			}
		}
		$return_value = null;
		if("get"===$mode){
			if(isset($this->_ax_record[$attr_name])){
				$return_value = $this->_ax_record[$attr_name];
			}else
			return $attr_value; // asume $attr_value as default value
		}elseif("set" == $mode){
			if(isset($this->_ax_record[$attr_name]))
				$this->_ax_record[$attr_name] = $attr_value;
		}	
		return $return_value;
	}
}
