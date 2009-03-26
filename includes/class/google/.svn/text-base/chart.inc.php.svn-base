<?php

class gChart{
	private $baseUrl = "http://chart.apis.google.com/chart?";
	private $scalar = 1;

	public $types = array ("lc","lxy","bhs","bvs","bhg","bvg","p","p3","v","s");
	public $type = 1;
	public $dataEncodingType = "t";
	public $values = Array();
	protected $scaledValues = Array();
	public $valueLabels;
	public $dataColors;
	public $width = 200; //default
	public $height = 200; //default
	private $title;
 
	public $labelsBottom=array();
	public $labelsLeft=array();

	public function setTitle($newTitle){
		$this->title = str_replace("\r\n", "|", $newTitle);
		$this->title = str_replace(" ", "+", $this->title);
	}

	public function showPercentages() {
		return false;
	}

	protected function encodeData($data, $encoding, $separator){
		switch ($this->dataEncodingType){
			case "s":
				return $this->simpleEncodeData();
			case "e":
				return $this->extendedEncodeData();
			default:{
				$retStr = $this->textEncodeData($data, $separator, "|");
				$retStr = trim($retStr, "|");
				return $retStr;
			}
		}
	}

	private function textEncodeData($data, $separator, $datasetSeparator){
		$retStr = "";
		if(!is_array($data))
		return $data;
		foreach($data as $currValue){
			if(is_array($currValue))
			$retStr .= $this->textEncodeData($currValue, $separator, $datasetSeparator);
			else
			$retStr .= $currValue.$separator;
		}

		$retStr = trim($retStr, $separator);
		$retStr .= $datasetSeparator;
		return $retStr;
	}

	public function addDataSet($dataArray){
		array_push($this->values, $dataArray);
	}
	public function clearDataSets(){
		$this->values = Array();
	}

	private function simpleEncodeData(){
		return "";
	}

	private function extendedEncodeData(){
		return "";
	}

	protected function prepForUrl(){
		$this->scaleValues();
	}
	protected function concatUrl(){
		$fullUrl = $this->baseUrl;
		$fullUrl .= "cht=".$this->types[$this->type];
		$fullUrl .= "&chs=".$this->width."x".$this->height;
		$fullUrl .= "&chd=".$this->dataEncodingType.":".$this->encodeData($this->scaledValues,"" ,",");
		if(isset($this->valueLabels))
		$fullUrl .= "&chdl=".$this->encodeData($this->getApplicableLabels($this->valueLabels),"", "|");
		$fullUrl .= "&chco=".$this->encodeData($this->dataColors,"", ",");
		if(isset($this->title))
		$fullUrl .= "&chtt=".$this->title;

		return $fullUrl;
	}
	protected function getApplicableLabels($labels){
		$trimmedValueLabels = $labels;
		return array_splice($trimmedValueLabels, 0, count($this->values));
	}
	public function getUrl(){
		$this->prepForUrl();
		return $this->concatUrl();
	}

	public function printIt(){
		print "<br>Scalar:$this->scalar <br>";
		print "<br>Values:".print_r($this->values) ."<br>";
		print "<br>Values:".print_r($this->scaledValues) ."<br>";
	}

	protected function scaleValues(){
		$this->setScalar();
		$this->scaledValues = utility::getScaledArray($this->values, $this->scalar);
	}


	function setScalar(){
		$maxValue = 100;
		$maxValue = max($maxValue, utility::getMaxOfArray($this->values));
		if($maxValue <100)
		$this->scalar = 1;
		else
		$this->scalar = 100/$maxValue;
	}
}

class gPieChart extends gChart{
	function __construct(){
		$this->type = 6;
		#$this->width = $this->height * 1.5;
	} 
	public function getUrl(){
		parent::setScalar();
		$retStr = parent::getUrl();
		$retStr .= "&chl=".$this->encodeData($this->valueLabels,"", "|");
		return $retStr;
	}
	private function getScaledArray($unscaledArray, $scalar){
		return $unscaledArray;
	}
	public function set3D($is3d){
		if($is3d){
			$this->type = 7;
			#$this->width = $this->height * 2;
		}
		else{
			$this->type = 6;
			#$this->width = $this->height * 1.5;
		}
	}
	
	public function formatLabels($percent=true, $totals=true, $decimals=0) {
		if(!$percent && !$totals) return false;
		
		$total = 0; 
		if($percent) 
			foreach($this->values[0] as $val) 
				$total += $val;
				
		if($total==0) $percent=false;
		 
		foreach($this->valueLabels as $key => $val) {
			
			// add percentages
			if($percent) {
		 		$percent = round( ($this->values[0]["$key"] / $total) * 100, 0); 
				$val = $val . " ({$percent}%)";
			}
			
			// add totals
			if($totals) { 
				if($this->values[0]["$key"] > 1000) 
					$num = round($this->values[0]["$key"] / 1000, 1) . "K";
				else 
					$num = number_format($this->values[0]["$key"], $decimals); 
				$val = $num . " " . $val;
			}
			
			// append public var
			$this->valueLabels["$key"] = $val; 
		} 
	} 	
}

class gLineChart extends gChart{
	function __construct(){
		$this->type = 0;
	}
	
	public function setLabelsBottom($labels) {
		$this->labelsBottom=$labels;
	}
	
	public function setLabelsLeft($labels) {
		$this->labelsLeft=$labels;
	}
	
	public function getUrl(){
		parent::setScalar();
		$retStr = parent::getUrl();
		#$retStr .= "&chl=".$this->encodeData($this->valueLabels,"", "|");
		
		#if($this->labelsBottom || $this->labelsLeft) {
			
		$Ydiv=15;
		$Xdiv=20;
			
		 
		$min = null;
		$max = null; 
		foreach($this->values as $values) {
			foreach($values as $val) {						
				if($val > $max || $max == null) $max=$val;
				if($val < $min || $min == null) $min=$val;						
			}
		}  	 
		$retStr .= "&amp;chxr=0,$min,$max";
		  
		
		// X (bottom labels)
		if($this->labelsBottom) { 
			$retStr .= "&amp;chxt=y,x&amp;chxl=|1:|"; 
			$retStr .= implode('|', $this->labelsBottom); 
			$Xdiv = round(100 / (count($this->labelsBottom)-1), 0);
		} else {
			$retStr .= "&amp;chxt=y";
		}  
			
		$retStr .= '&amp;chg=' . $Xdiv  . ',' . $Ydiv . ',1,3';
		
		// line styles 
		if(!empty($this->lineStyles)) 
			$retStr .= "&amp;chls=$this->lineStyles";
			 
		return $retStr;
	}
		
}

class gBarChart extends gChart{
	public $barWidth;
	public $groupSpacerWidth = 1;
	protected $totalBars = 1;
	private $isHoriz = false;
	public function getUrl(){
		$this->scaleValues();
		$retStr = parent::concatUrl();
		$this->setBarWidth();
		$retStr .= "&chbh=$this->barWidth,$this->groupSpacerWidth";
		return $retStr;
	}

	function setBarCount(){
		$this->totalBars = utility::count_r($this->values);
	}

	private function setBarWidth(){
		if(isset($this->barWidth))
		return;
		$this->setBarCount();
		$totalGroups = utility::getMaxCountOfArray($this->values);
		$chartSize = $this->width - 50;
		if($this->isHoriz)
		$chartSize = $this->height - 50;
		$chartSize -= $totalGroups * $this->groupSpacerWidth;
		$this->barWidth = round($chartSize/$this->totalBars);
	}

}
class gGroupedBarChart extends gBarChart{
	function __construct(){
		$this->type = 5;
	}

	public function setHorizontal($isHorizontal){
		if($isHorizontal){
			$this->type = 4;
		}
		else{
			$this->type = 5;
		}
		$this->isHoriz = $isHorizontal;
	}

}
class gStackedBarChart extends gBarChart{
	function __construct(){
		$this->type = 3;
	}

	function setBarCount(){
		$this->totalBars = utility::getMaxCountOfArray($this->values);
	}

	public function setHorizontal($isHorizontal){
		if($isHorizontal){
			$this->type = 2;
		}
		else{
			$this->type = 3;
		}
		$this->isHoriz = $isHorizontal;
	}

	protected function scaleValues(){
		$this->setScalar();
		$this->scaledValues = utility::getScaledArray($this->values, $this->scalar);
	}

	function setScalar(){
		$maxValue = 100;
		$maxValue = max($maxValue, utility::getMaxOfArray(utility::addArrays($this->values)));
		if($maxValue <100)
		$this->scalar = 1;
		else
		$this->scalar = 100/$maxValue;
	}

}

	
class utility{
	public static function count_r($mixed){
		$totalCount = 0;
		
		foreach($mixed as $temp){
			if(is_array($temp)){
				$totalCount += utility::count_r($temp);
			}
			else{
				$totalCount += 1;
			}
		}
		return $totalCount;
	}
	
	public static function addArrays($mixed){
		$summedArray = array();
		
		foreach($mixed as $temp){
			$a=0;
			if(is_array($temp)){
				foreach($temp as $tempSubArray){
					$summedArray[$a] += $tempSubArray;
					$a++;
				}
			}
			else{
				$summedArray[$a] += $temp;
			}
		}
		return $summedArray;
	}
	public static function getScaledArray($unscaledArray, $scalar){
		$scaledArray = array();
		
		foreach($unscaledArray as $temp){
			if(is_array($temp)){
				array_push($scaledArray, utility::getScaledArray($temp, $scalar));
			}
			else{
				array_push($scaledArray, round($temp * $scalar, 2));
			}
		}
		return $scaledArray;
	}
	
	public static function getMaxCountOfArray($ArrayToCheck){
		$maxValue = count($ArrayToCheck);
		
		foreach($ArrayToCheck as $temp){
			if(is_array($temp)){
				$maxValue = max($maxValue, utility::getMaxCountOfArray($temp));
			}
		}
		return $maxValue;
		
	}

	public static function getMaxOfArray($ArrayToCheck){
		$maxValue = 1;
		
		foreach($ArrayToCheck as $temp){
			if(is_array($temp)){
				$maxValue = max($maxValue, utility::getMaxOfArray($temp));
			}
			else{
				$maxValue = max($maxValue, $temp);
			}
		}
		return $maxValue;
	}
	
}

?>