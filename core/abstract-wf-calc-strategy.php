<?php
abstract class WF_Calc_Strategy {

	public $calculation_mode = "";
	public $rate_matrix = "";
	
	function __construct(){
		$a = func_get_args();
	   $this->calculation_mode = $a[0];
	   $this->rate_matrix = $a[1];
	}
	
	public function wf_row_selection_choice(){
		return 'max_cost';
	}
	
	public function wf_get_price($package_items){
		$total_price = 0;
		foreach($package_items as $package_item){		
			$_product = $package_item['data'];
			$total_price += $_product->get_price() * $package_item['quantity'];
		}
		return $total_price;
	}

	public static function get_calc_mode( $calculation_mode = 'per_order_max_cost',$rate_matrix = null ) {
		if ( ! class_exists( 'WF_Calc_Per_Order' ) )
			include_once 'class-wf-calc-per-order.php' ;

		$calc_strategy = new WF_Calc_Per_Order($calculation_mode,$rate_matrix);
		return 	$calc_strategy;
	}
}	
?>