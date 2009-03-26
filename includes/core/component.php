<?php
/**
 * Base component class for lean php framework
 *  
 */
class Component
{ 
	public $id;	 
	public $class;
	public $width;
	public $height; 
	public $align;
	public $state=true;
	public $refresh;
	
	/**
	 * Construct
	 */
	public function __construct($id) {
		$this->setId($id); 
	}
		
	/**
	 * Set the component id
	 */
	public function setId($id){
		$this->id=$id;
	}
	
	/**
	 * Get the component Id 
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * Set the class name to use
	 */
	public function setClass($c) {
		$this->class=$c;
	}
		
	/**
	 * Make visible
	 */
	public function show() {
		$this->state=false;
	}
	
	/**
	 * Make invisible
	 */
	public function hide() {
		$this->state=false;
	}
	
	/**
	 * Set the refresh
	 */
	public function setRefresh($r) {
		$this->refresh=$r;
	}
	
	/**
	 * Set width
	 */
	public function setWidth($w){
		$this->width=$w;
	}
	
	/**
	 * Set height
	 */
	public function setHeight($h) {
		$this->height=$h;
	}	
	
	/**
	 * Set alignment
	 */
	public function setAlign($a) {
		$this->align=$a;
	} 
}
?>