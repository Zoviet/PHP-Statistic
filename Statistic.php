<?php
namespace Statistic;
/**
 * PHP Statistic: Some base statistical functions and any of my specific methods for data analysis and find abnormal values of periodical series 
 * 
 * @version 0.6
 * @link http://github.com/Zoviet/PHP-Statistic
 * @author Zoviet (Alexandr Pavlov  / @Zoviet)
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Alexandr Pavlov
 * @site http://opendata.org.ru/
 */

class Statistic {	
	
	/**
	* @var int Maximum series lenght
    */
	public $max; 
	
	/**
	* @var int Minimum series lenght
    */
	public $min;
	
	/**
	* @var array The criteria of Irvine table
    */
	public $irvin;  
	
	/**
	* @var array The criteria of Student table
    */
	public $student; 
	
	/**
	* @var array The criteria of Fisher table
    */
	public $fisher; 
	
	/**
	* @var array The exception errors
    */
	private $errors; 
	
	/**
	* @var string Version of this lib (string)
    */
	public $version; 
	
	 // Methods
    // ===================================================================================
			
	/**
    * Constructor
    */     
    public function __construct()
    {     
		$this->version = 'PHP Statistic: Under development';
								
		$this->errors = array(
            'ERR00' => 'Unsupported data format.',
            'ERR01' => 'It\'s not array.',
    		'ERR02' => 'It\'s not time series.',    		
    		'ERR03' => 'Can\'t find Fisher\'s table file.',
    		'ERR04' => 'Series size over max: '.$this->max,
    		'ERR05' => 'Covariation error: series sizes are not equal.',
    		'ERR06' => 'Quartilization error: The lenght of series must be more then 8.',
        );
		
		$this->set_defaults(2,9999); //set defaults constants
    }
        
    // assistive methods
    
    //===============================================================================================
    
    /**
    * Set defaults
    * 
    * @param int max - Maximum lenght of series
    * @param int min - Minimum lenght of series    
    * 
    * @return NONE
    */	
	public function set_defaults($min,$max) 
	{
		$this->min = (int)$min;
		$this->max = (int)$max;
		$this->irvin = array(  //the criteria of Irvine table
			1=>100,
			2=>2.8,
			3=>2.2,
			4=>2,
			5=>1.5,
			6=>1.5,
			7=>1.5,
			8=>1.5,
			9=>1.5,
			10=>1.5,
			20=>1.3,
			30=>1.2,
			40=>1.2,
			50=>1.1,
			100=>1.0,
			200=>0.9,
			300=>1.9,
			400=>0.9,
			500=>0.8,
			600=>0.8,
			700=>0.8,
			800=>0.8,
			900=>0.8,
			1000=>0.8
		);
	
		$this->student = array( //the criteria of Student table
			1=>12.706,
			2=>4.3027,
			3=>3.1825,
			4=>2.7764,
			5=>2.5706,
			6=>2.4469,
			7=>2.3646,
			8=>2.3060,
			9=>2.2622,
			10=>2.2281,
			11=>2.2010,
			12=>2.1788,
			13=>2.1604,
			14=>2.1448,
			15=>2.1315,
			16=>2.1199,
			17=>2.1098,
			18=>2.1009,
			19=>2.0930,
			20=>2.0860,
			21=>2.0796,
			22=>2.0739,
			23=>2.0687,
			24=>2.0639,
			25=>2.0595,
			26=>2.0555,
			27=>2.0518,
			28=>2.0484,
			29=>2.0452,
			30=>2.0423,
			40=>2.0211,
			60=>2.0003,
			120=>1.9799,
			10000=>1.9600
		);
		
		
		// the criteria of Fisher table load from csvfile
		
		try {
			$this->fisher = $this->fisherload(__DIR__."/fisher.csv");
		} catch (\Exception $error) {
			$this->nofisher = TRUE;
		}
	}
		
	private function fisherload($filename) 
	{		
		if (($handle = fopen($filename, "r")) !== FALSE) {			
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {																	
				$header = (strlen($data[0]==0) and strlen($data[1]>0)) ? $data : $header;													
				$fisher[$data[0]] = array_combine ($header , $data);			
			}		
		} else {
			throw new \Exception($this->version.': '.$this->errors['ERR03']);
		}		
		fclose($handle);	
		return $fisher;
	}
	
	/**
     * Test loaded data series
     * 
     * @param array Series of value     
     * 
     * @return array Data series 
     */
	public function test($data) 
	{	
		static $series;	
		try {
			if (!is_array($data)) {		
				throw new \Exception($this->version.': '.$this->errors['ERR00'].':'.$this->errors['ERR01']);
			} else {			
				$recount = count($data,COUNT_RECURSIVE);
				if ($recount< $this->max and $recount>$this->min) {
					if (count($data)!==$recount or $this->array_test===FALSE) {
						throw new \Exception($this->version.': '.$this->errors['ERR00'].':'.$this->errors['ERR02']);
					} else {
						$series = $data;
					}						
				} else {
					throw new \Exception($this->version.': '.$this->errors['ERR01'].':'.$this->errors['ERR04'].':'.'Serial lenght is '.$recount.' but only '.$min.'-'.$max.' lenght supported');
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
			error_log($e->getMessage(), 0);
			$series = FALSE;
		} 
		return $series;
	}
	
	private function array_test($array) //test the array dimension
	{
		$test = TRUE;
		foreach ($array as $value) {
			if (is_array($value)) {
				$test = FALSE;
			}
		}
		return $test;
	}
	
	private function array_key_first(array $arr) //for PHP < 7.3.0
	{ 
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
    
    private function array_delete(array $array, array $symbols = array(0)) //delete null values
	{
		return array_diff($array, $symbols);
	}
	
	private function array_random($lenght,$max) 
	{
		$i=0;
		while ($i<$lenght) {
			$data[$i++]=rand(0,$max);
		}		
		return $data;
	}
	
	private function array_series($data)
	{
		 foreach ($data as $key=>$value) {
			 $values[$key+1]=$value;
		}
		return $values;
	}
		
	/**
     * Unit testing
     * 
     * @param int Lenght of test data series (max and min intervals unsupported)     
     * 
     * @return array Data series
     */
	public function methods($lenght=FALSE) //main test all methods	
	{
		$methods = get_class_methods($this);
		if (is_numeric($lenght)) {
			$data1 = $this->array_random($lenght,100);
			$data2 = $this->array_random($lenght,100);		
			$methods = array_slice($methods,10);
			foreach ($methods as $method) {
				$gr = new \ReflectionMethod($this, $method);
				$params = $gr->getParameters();
				$doc = $gr->getDocComment();
				$return[$method.': '.$doc] = (count[$params]==1)? $this->$method($data1) : $this->$method($data1,$data2);
			}
			return $return;
		} else {
			return $methods;
		}
	}
	
	// main methods 
	
	//===============================================================================================
	
	/**
     * Last key of data series    
     */
	public function n($data) 
	{
		static $n;
		$data = $this->test($data);
		$n = ($data)? count($data)-1 : FALSE;
		return $n;
	}
	
	/**
     * Last value of data series    
     */
	public function Yn($data) //Yn (last element) of series
	{
		static $Yn;
		$n = $this->n($data);
		$Yn = ($n)? $data[$n] : FALSE;
		return $Yn;
	}
	
	/**
     * First value of data series    
     */
	public function Y0($data) //Y0 (first element) of series
	{
		static $Y0;
		$data = $this->test($data);
		$Y0 = ($data)? $data[0] : FALSE;
		return $Y0;
	}
	
	/**
     * Lenght of data series    
     */
	public function lenght($data) //lenght of time series
	{
		static $lenght;
		$series = $this->test($data);		
		$lenght = (is_array($series))? count($series) : FALSE;
		return $lenght;
	}
	
	/**
     * Value's summa of data series    
     */
	public function sum($data) //time series summa
	{	
		static $sum;
		$series = $this->test($data);
		$sum = (is_array($series))? array_sum($series) : FALSE;
		return $sum;
	}
	
	/**
     * The power mean of data series   
     */
	private function power($data,$d=1) 
	{ 			
		static $power;
		$lenght = $this->lenght($data);
		if ($lenght) {
			$sum = 0;
			foreach ($data as $value) {
				$sum = $sum + pow($value,$d);
			}
		$power = pow($sum/$lenght,$d);		
		} else {
			$power = FALSE;
		}
		return $power;
	}
	
	/**
     * The arithmetic mean of data series (old name: middle)   
     */
	public function mean($data) // arithmetic mean of the series (middle)
	{ 			
		static $mean;
		$mean = $this->power($data,1);
		return $mean;
	}
	
	/**
     * The harmonic mean of data series   
     */
	public function harm($data)
	{
		static $harm;
		$harm = $this->power($data,-1);
		return $harm;
	}
	
	/**
     * The geometric mean of data series   
     */
	public function geom($data)
	{
		static $geom;
		$lenght = $this->lenght($data);
		if ($lenght) {						
			$geom = pow(array_product($data),1/$lenght);
		} else {
			$geom = FALSE;
		}
		return $geom;
	}
	
	/**
     * The numeric values of data series [1..n]
     */
	public function values($data) //numeric values of the series [1..n]
	{
		static $values;
		$data = $this->test($data);
		if ($data) {	
			$values = $this->array_series(array_values($data));					 			
		} else {
			$values=FALSE;
		}
		return $values;
	}
	
	/**
     * Average absolute increase of data series (old name: sap)
     */			
	public function absinc($data) // average absolute increase (sap)
	{ 
		static $absinc;
		$data = $this->values($data);
		if ($data) {			
			$yn = $this->Yn($data);
			$y0 = $this->Y0($data);
			$absinc = ($yn-$y0)/$this->n($data);
		} else {
			$absinc = FALSE;
		}
		return $absinc;		
	}
	
	/**
     * Average growth rate of data series (old name: skr)
     */	
	public function averate($data) // average growth rate (skr)
	{
		$kp=1;		
		$sample = $this->values($data);
		if ($sample) {
			$absinc = $this->absinc($sample);
			$n = $this->n($sample);
			foreach ($sample as $key=>$y) {
				$kpy=$kp*pow($y,1/($key+1));								
			}
			$kp = (abs($absinc)==$absinc) ? pow($kpy,1/($n)): 1-pow($kpy,1/($n)); 
		} else {
			$kp = FALSE;
		}
		return $kp;
	}
	
	/**
     * Average grown rate of data series (%) (old name: str)
     */	
	public function pagr($data) // average grown rate (percentage) (str)
	{ 
		static $pagr;
		$averate = $this->averate($data);
		$pagr = ($averate) ? $averate*100 : FALSE;
		return $pagr;
	}
	
	/**
     * Average growth rate of data series (old name: stp)
     */	
	public function agr($data) // average growth rate (stp)
	{ 
		static $agr;
		$averate = $this->averate($data);
		$agr = ($averate) ? $averate-100 : FALSE;
		return $agr;
	}
	
	/**
     * Average level of data series (old name: sur)
     */	
	public function alevel($data) // series average level (sur)
	{ 	
		static $alevel;	
		$data=$this->values($data);
		if ($data) {
			$yn = $this->Yn($data);
			$y0 = $this->Y0($data);					
			$ssum = array_sum(array_slice($data,1,count($data)-2)) + $y0/2 + $yn/2;
			$alevel = $ssum/($this->n($data));		
		} else {
			$alevel = FALSE;
		}
		return $alevel;
	}
	
	/**
     * The average value of the absolute increase of 1% of the series value (old name: proc)
     */	
	public function proc($data) //The average value of the absolute increase of 1% of the series value (proc)
	{ 
		static $proc;
		$agr = $this->agr($data);
		$proc = ($agr)? $this->absinc($data)/$agr : FALSE;		
		return $proc;
	}
	
	/**
     * The moment of the series
     */		
	public function moment($data,$order=1) 
	{	
		$order = (is_numeric($order))? $order : 1;
		$n = $this->lenght($data);
		$ds = 0;
		if ($n) {
			$test = $this->testvar($data);
			$sm = $this->mean($data);
			foreach ($data as $key=>$y) {
				$ds=($test)? $ds+pow(($y-$sm)*$key,$order): $ds+pow(($y-$sm),$order); 
			}				
			$moment = ($test)? $ds/$this->sum(array_keys($data)): $ds/$n; 
		} else {
			$moment = FALSE;
		}
		return $moment;
	}
	
	/**
     * Variance 
     * Working with varionational and non-varionational series
     * @param array Numeric data series
     * @param bool Type of variance (standart deviation (1-n) or standart error (n)
     * @return Variance
     */
	public function variance($data,$standart=TRUE) //variance with standard deviation
	{ 
		static $variance;
		$n = $this->lenght($data);				
		$moment = $this->moment($data,2);
		if ($moment) {			
			$test = $this->testvar($data);
			$z = ($standart!==FALSE) ? $n-1 : $n;									
			$variance = ($test)? $moment: $moment*($n/$z); 		
		} else {
			$variance = FALSE;
		}
		return $variance;	
	}
	
	/**
     * Variance with standart error   
     */
	public function evar($data) //variance with standard error
	{ 
		return $this->variance($data,FALSE);
	}
	
	/**
     * Standart deviation (with standart deviation)
     */
	public function sd($data) //standard deviation
	{
		return pow($this->variance($data),0.5);
	}
	
	/**
     * Mean linear deviation
     */	
	public function linear($data) //Mean linear deviation	
	{
		static $linear;
		$mean = $this->mean($data);
		if ($mean) {
			$test = $this->testvar($data);
			$sum = 0;			
			foreach ($data as $f=>$value) {
				$sum = ($test)? $sum+abs($value-$mean)*$f: $sum+abs($value-$mean);
			}
			$linear = ($test)? $sum/$this->sum(array_keys($data)) : $sum/$this->n($data);
		} else {
			$linear=FALSE;
		}
		return $linear;
	}
	
	/**
     * Test for variational or not variational series
     */	
	public function testvar($data) //variational or not variational series
	{		
		$data = $this->test($data);
		if ($data) { 
			while ($now = key($data) !== null) {
				$test = ($nextvalue<$value) ? FALSE : TRUE;	 
				$value = current($data); 				    				
				$nextvalue = next($data); 																
			} 
			reset($data);						
		} else {
			$test = FALSE;
		}
		return $test;
	}
	
	/**
     * Variationalising data series [1..n]
     */	
	public function variat($data) //variational series [1..n]
	{		
		$data = $this->test($data);
		if ($data) {
			$data = $this->values($data);
			sort($data);
			$data = $this->array_series($data);
		} else {
			$data = FALSE;
		} 
		return $data;
	}
	
	/**
     * Scope of variation 
     * @param array Numeric varionational series, variationaled other series
     * @return float Scope of variation
     */	
	public function rv($data) //scope of variation
	{
		static $rv;
		$series = $this->variat($data);
		$rv = ($series)? $this->Yn($series)-$this->Y0($series) : FALSE;
		return (float)$rv;
	}	
	
	/**
     * Quartiles
     * @param array Varionational series with lenght more then 8, variationaled other series
     * @return array Quartiles (3)
     */	
	public function quart($data) //quartiles
	{	
		static $quartil;
		$n = $this->lenght($data);
		if ($n<8) {
			$quartil = FALSE;
			throw new \Exception($this->version.': '.$this->errors['ERR06']);
		} else {
			$test = $this->testvar($data);							
				if ($test) {
					$data=array_chunk($data, ceil($n/4));
					$i=0;					
					foreach ($data as $value) {
						$quartil[++$i]=$value[count($value)-1];
					}				
					$quartil = array_slice($quartil,0,3,TRUE);															
				} else {					
					$this->quart($this->variat($data));
				}
		}
		return $quartil;
	}
	
	/**
     * Quartile deviation
     * @param array Varionational series with lenght more then 4, variationaled other series
     * @return float Quartile deviation
     */	
	public function dk($data) //Quartile deviation
	{
		static $dk;
		$quart = $this->quart($data);
		$dk =($quart[3]-$quart[1])/2;
		return $dk;
	}
	
	/**
     * Coefficient of oscillation (%)
     */
	public function kr($data) //coefficient of oscillation
	{
		static $kr;
		$rv = $this->rv($data);
		$kr = ($rv)? ($rv/$this->mean($data))*100 : FALSE;
		return $kr;
	}
	
	/**
     * Coefficient of variation 
     */
	public function kv($data) //coefficient of variation
	{
		static $kv;
		$variance = $this->variance($data);
		$kv = ($variance)? ($variance/$this->mean($data))*100 : FALSE;
		return $kv;
	}
			
	/**
     * Linear coefficient of variation(%)
     * Characterizes the degree of series: <30%: homogeneous, 30-60% - the average homogeneity, >60% - heterogeneous
     */
	public function lv($data) //linear coefficient of variation
	{
		static $lv;
		$linear = $this->linear($data);
		$lv = ($linear)? ($linear/$this->mean($data))*100 : FALSE;
		return $lv;
	}
	
	/**
     * Pirson's coefficient of asymmetrical (%) 
     * Characterizes the series asymmetric: >0: right asymmetric <0 - left asymmetric 0- symmetric series
     */
	public function apv($data) 
	{
		static $apv;
		$variance = $this->variance($data);
		$apv = ($variance)? ($this->mean($data)-$this->moda($data))/$variance : FALSE;		
		return $apv;
	}
	
	/**
     * Coefficient of asymmetrical (moments method) (%) 
     * Characterizes the series asymmetric: >0: right asymmetric <0 - left asymmetric 0- symmetric series
     */
	public function av($data) 
	{
		static $av;
		$variance = $this->variance($data);
		$av = ($variance)? $this->moment($data,3)/pow($variance,3) : FALSE;		
		return $av;
	}
	
	/**
     * Frequency of series values
     * @param any Data series
     * @return array Frequency of values: key->frequency(int)
     */
	public function table($data) //frequency of series values, array (freq)
	{   
		static $table;
		$data = $this->test($data);
		if ($data) {
			$table = array();
			foreach ($data as $key=>$value) {
				if (array_key_exists($value,$table)) { 
					$table[$value] = $table[$value]+1;
				} else {
					$table[$value] =1;
				}						
			}
			arsort($table);
		} else {
			$table = FALSE;
		}
		return $table;
	}
	
	/**
     * Moda   
     */
	public function moda($data,$nulled=FALSE) //moda
	{	
		static $moda;
		$data = $this->test($data);
		if ($data) {
			$data = ($nulled!==FALSE) ? $this->array_delete($data) : $data;
			$return = $this->table($data);						
			$moda = $this->array_key_first($return);
		} else {
			$moda = FALSE;
		}
		return $moda;
	}
	
	/**
     * Moda of data series without nulled values
     */
	public function nmoda($data) { //moda without nulled values
		return $this->moda($data,TRUE);
	}
	
	/**
     * Median
     */
	public function median($data,$nulled=FALSE) //median
	{		
		static $median;
		$data = $this->variat($data);
		if ($data) {
			$data = ($nulled!==FALSE) ? $this->array_delete($data) : $data;	
			$n = $this->lenght($data);										
			if (($n/2)!=ceil($n/2)) {				
				$median = $data[ceil($n/2)];
			} else {				
				$median = array_sum(array_slice($data,ceil($n/2)-1,2))/2;
			}
		} else {
			$median = FALSE;
		}		
		return $median;
	}
	
	/**
     * Median of data series without nulled values
     */
	public function nmedian($data) //median without nulled values in series
	{
		return $this->median($data,TRUE);
	}
	
	/**
     * Series covariation 
     * @param array Data series 1
     * @param array Data series 2
     * @return Covariation coefficient
     */
	public function cov($data1,$data2) //covariation
	{ 
		static $cov;
		$lenght1 = $this->lenght($data1);
		$lenght2 = $this->lenght($data2);							
		if ($lenght1==$lenght2) {
			$cov =0;
			$data1=$this->values($data1); 
			$data2=$this->values($data2);
			$mean1 = $this->mean($data1);
			$mean2 = $this->mean($data2);
			foreach ($data1 as $key=>$value) {
					$cov = $cov + ($data1[$key]-$mean1)*($data2[$key]-$mean2);					
			}
			$cov = (1/$lenght1)*$cov;			
		} else {
			throw new \Exception($this->version.': '.$this->errors['ERR05']);
			$cov = FALSE;
		}
		return $cov;
	}
	
	/**
     * Observation Irwin test	 
     * @param array Data series     
     * @return array Array of abnormal coefficients or FALSE if series don't include abnormal values
     */
	public function irwin($data) { //Observation Irwin test							
		$sd = $this->sd($data);
		if ($sd) {	
			$n =$this->lenght($data);
			$irwin = array();			
			if ($n<10) {
				$goodirvin=$this->irvin[$n];}
			else {
				if ($n<=50) {
					$goodirvin = $this->irvin[ceil($n/10) * 10];
				} else {
					if ($n<100) {
						$goodirvin = $this->irvin[50];
					} else {
						$goodirvin = $this->irvin[ceil($n/100) * 100];
					}
				}
			}			
			foreach ($data as $key=>$y) {
				$irv=abs($y-$sd)/($n-1);			
				if ($irv>$goodirvin) {
					$irwin[$key]=$irv;
				}
			}
		} else {
			$irwin = FALSE;
		}
		return $irwin;		
	}
	
	/**
     * Pearson correlation	 
     * @param array Data series 1    
     * @param array Data series 2  
     * @return Pearson correlation coefficient (0 if no correlation)
     */
	public function pear($data1,$data2) //Pearson correlation
	{   
		static $pear;
		$var1 = $this->variance($data1);
		$var1 = $this->variance($data2);
		if ($this->lenght($data1) == $this->lenght($data2)) {
			$dispers = $var1*$var2;	
			if ($dispers>0) {			
				$pear = $this->cov($data1,$data2)/$dispers;
			} else {
				$pear = 0;		
			}		
		} else {
			throw new \Exception($this->version.': '.$this->errors['ERR05']);
			$pear = FALSE;
		}
		return $pear;
	}	
	
	/**
     * Trend by sort in Ascending and Descending Order  
     * @param array Data series 1     
     * @return Number of series with sign: if <0 then series has no trend; if >0 - trend
     */
	public function serial($data) //Trend by sort in Ascending and Descending Order 
	{ 	
		static $serial;
		$i=0;
		$ser[0]=0;
		$n=$this->lenght($data);		
		if ($n) {
			$data=$this->values($data);
			while ($now = key($data) !== null) { 
				$value = current($data); 						    
				$nextvalue = next($data);  					    																
				$data[$now] = ($nextvalue>=$value) ? 1 : -1;														
			}		
			reset($data);
			while (key($data) !== null) { 
				$value = current($data); 						    
				$nextvalue = next($data);  			
				if (($value+$nextvalue)==0) {				
					$i=$i+1;								
				}	
				$ser[$i]=(isset($ser[$i])) ? $ser[$i]+1 : 1;														
			}
			$vn = count($ser);//число серий			
			$tmax = max($ser);//число подряд идущих плюсов или минусов в самой длинной серии
			$tn = intval(1.43*log($n-1));
			$vvn = intval(0.5*($n+2-1.96*pow($n-1,0.5)));		
			if (($tmax<$tn) or ($vn>$vvn)) {
				$serial = 0+$vn;//тренд есть
				} else {
				$serial = 0-$vn;//тренда нет
			}
		} else {
			$serial = FALSE;
		}
		return $serial;
	}
	
	/**
     * Combine of Fisher and Student methods
     * @param array Data series     
     * @return t-Student coefficient with sign (if <0 then series has no trend; if >0 - trend) or 0 if series is not in normal distribution. FALSE if Fisher's datatable file can't be founded
     */
	public function fish($data) { //combine of Fisher and Student methods	
		static $trend;	
		$dis = array();		
		$n=$this->lenght($data);		
		if ($n and $this->nofisher==FALSE) {			
			$data=$this->values($data);
			$data=array_chunk($data,ceil($n/2));			
			foreach ($data as $key=>$item) {
				$dis[$key]=$this->variance($item);
			}			
			if (pow($dis[0],2)>pow($dis[1],2)) {
				$ff=($dis[1]>0) ? pow($dis[0],2)/pow($dis[1],2) : 0;
				$k1=count($data[0])-1;
				$k2=count($data[1])-1;
			} else {
				$ff=($dis[0]>0) ? pow($dis[1],2)/pow($dis[0],2) : 0;
				$k1=count($data[1])-1;
				$k2=count($data[0])-1;
			}
			//Fishers's method
			if ($k2>30) {$k2= ($k2<100) ? ceil($k2/10) * 10 : ceil($k2/100) * 100;}
			if ($k1>8 and $k1<100) {$k1= ($k1<24) ? 12 : 24;}
			$k1=($k1>100) ? 10000: $k1;
			$k2=($k2>1000) ? 10000: $k2;
			$goodfish=$this->fisher[$k2][$k1];
			if ($ff<$goodfish) { //если дисперсии равны, используем критерий стьюдента			
				$omega = pow(($k1*pow($dis[0],2)+$k2*pow($dis[1],2))/($k1+$k2), 0.5); //среднеквадратическое отклонение разности средних			
				$stud = ($omega>0) ? abs($this->middle($data[0])-$this->middle($data[1]))/($omega*pow((1/($k1+1))+(1/($k2+1)),0.5)) : 0;			
				if ($n<=30) {
					$goodstud=$this->student[$n];
					} else {
						$goodstud=($n>30 and $n<=40) ?	$this->student[40] : $this->student[10000];				
						$goodstud=($n>40 and $n<=60) ?	$this->student[60] : $this->student[10000];	
						$goodstud=($n>60 and $n<=120) ?	$this->student[120] : $this->student[10000];	
					}			
				if ($stud<$goodstud) {
					$trend = 1-$stud; //тренда не выявлено
				} else {
					$trend = $stud; //тренд выявлен
				}			
			} else {
			$trend = 0;
			}
		} else {
			$trend = FALSE;
		}
		return $trend;
	}
	
	/**
     * Complex trend analise
     * @param array Data series      
     * @return If series have normal distribution returns t-Student with trend sign (<0 - no trend, >0 - trend). In another case return result of trend by sort by order with sign
     */	
	public function trend($data) //complex trend analize
	{ 
		static $trend;		
		$base = $this->fish($data);
		if ($base!==FALSE) {
			if ($base==0) {
				$trend = $this->serial($data); 
			} else {
				$trend =$base;				
			}		
		} else {
			$trend = FALSE;
		}
		return $trend;
	}
	

}
