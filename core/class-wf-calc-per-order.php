<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WF_Calc_Per_Order extends WF_Calc_Strategy {
	
	public function wf_get_grouped_package($package){
		$group_key = 'wf_per_order';
		$rule = array();
		$rule[$group_key] = array(); 
		foreach ( $package['contents'] as $item_id => $values ) {
			$values['data'] = $this->wf_load_product( $values['data']  );
			if ( $values['data']->needs_shipping() ) {
				$rule[$group_key][] = $values;																								
			}
		}
		return $rule;		
	}
			
	private function wf_load_product( $product ){
		if( !$product ){
			return false;
		}
		return ( WC()->version < '2.7.0' ) ? $product : new wf_product( $product );
	}

	public function wf_calc_tax(){
		return 'per_order';
	}

	public function wf_get_price($package_items){
		global $woocommerce;
		$cart_total = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total;
		return (float)$cart_total;
	}
}
?>