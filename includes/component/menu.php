<?php

/**
 * Lean php framework menu component
 */

include_once LEAN_PHP_DIR . 'includes/core/component.php';

class Menu extends Component 
{	 
	public static $links=array(); 
	var $render=true;
	var $state=true;	 
	
	/* width of menu */
	public $width;
	/* height of menu */
	public $height;
	/* class name */
	public $class;

	public $separator = "&middot;";
	
	/**
	 * Construct
	 *
	 * @param string $gridId To be assigned to view
	 */
	public function __construct($gridId) {
		$this->setId($gridId);		 
	}

	/**
	 * Add a link to the menu
	 *
	 * @param string $name Name (or img tags)
	 * @param string $href HREF link
	 * @param string $onclick Onclick javascript action
	 * @param array $agiletabs Array for the agiletabs menu, eg: array('group','element')
	 */
	public function setLink($title, $href="#", $onclick=false, $agiletabs=false) {
		$rel=false;
		$name=false;
		if(is_array($agiletabs)) {
			@$rel = "tab[{$agiletabs[0]}]";
			@$name = $agiletabs[1];
		}
		$this->links[]=array('title'=>$title, 'href'=>$href, 'onclick'=>$onclick, 'rel'=>$rel, 'name'=>$name );
	}	 
	
	/**
	 * Render the grid html
	 */
	public function render() {   
		$style = $this->state ? "":"display:none;";
		$style .= $this->width ? "width:{$this->width};":"";
		$style .= $this->height? "height:{$this->height};overflow:auto;":"";
		if(!empty($style)) $style = " style=\"$style\"";
		$class = (!empty($this->class)) ? " class=\"$this->class\"" : "";
		 	 	
		$out = '<ul id="'.$this->getId().'"'.$style.$class.'>'; 
		foreach($this->links as $key=>$link) 
		{
			$rel = $link['rel'] ? " rel=\"{$link['rel']}\"":"";
			$name = $link['name'] ? " name=\"{$link['name']}\"":"";
			$onc = $link['onclick'] ? " onclick=\"{$link['onclick']}\"":"";
			$hre = $link['href'] ? " href=\"{$link['href']}\"":" href=\"#\"";
			$sep ='';
			if($this->separator && ++$key<count($this->links)) $sep = $this->separator;
			$out .=  
			"\r\n  <li><a{$hre}{$onc}{$rel}{$name}>{$link['title']}</a>&nbsp;{$sep}</li>";
		}		  
		$out .= "\r\n</ul>"; 
		echo $out;	
	}
}
?>