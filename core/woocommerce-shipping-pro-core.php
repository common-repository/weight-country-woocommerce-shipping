<?php
class wf_woocommerce_shipping_pro_method extends WC_Shipping_Method {
	function __construct() {
		$plugin_config = wf_plugin_configuration();
		$this->id		   = $plugin_config['id']; 
		$this->method_title	 = __( $plugin_config['method_title'], 'wf_woocommerce_shipping_pro' );
		$this->method_description = __( $plugin_config['method_description'], 'wf_woocommerce_shipping_pro' );
				
		$this->init_settings();
		$this->init_form_fields();

		// for backward compatibility
		if( empty($this->settings) ){
			$this->settings = get_option( 'woocommerce_wf_country_weight_woocommerce_shipping_settings', null );
		}

		$this->title 					= isset($this->settings['title']) ? $this->settings['title'] : $this->method_title;
		$this->enabled 					= isset($this->settings['enabled']) ? $this->settings['enabled'] : 'no';

		$this->tax_status	   			= isset($this->settings['tax_status']) ? $this->settings['tax_status'] :  '';
		$this->rate_matrix	   			= isset($this->settings['rate_matrix']) ? $this->settings['rate_matrix'] : array();
		
		//get_option fill default if doesn't exist. other settings also can change to this
		$this->debug 					= $this->get_option('debug');				
		$this->displayed_columns	  	= $this->get_option('displayed_columns');
		$calculation_mode	   			= 'per_order_max_cost';
		
		$this->and_logic 			= false;
		
		$this->multiselect_act_class	=	'multiselect';
		$this->drop_down_style	=	'chosen_select ';			
		
		$this->drop_down_style.=	$this->multiselect_act_class;
		
		if ( ! class_exists( 'WF_Calc_Strategy' ) )
			include_once 'abstract-wf-calc-strategy.php' ;

		$this->calc_mode_strategy =  WF_Calc_Strategy::get_calc_mode($calculation_mode,$this->rate_matrix);
		$this->row_selection_choice = $this->calc_mode_strategy->wf_row_selection_choice();
		
		$this->col_count = count($this->displayed_columns)+1;
		
		
		//variable to get decimal separator used.
		$separator = stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );
		$this->decimal_separator = $separator ? $separator : '.';
				
		// Save settings in admin
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		
	}

	function wf_debug($error_message){
		if($this->debug == 'yes')
			wc_add_notice( $error_message, 'notice' );
	}
	
		
		function get_inner_sections()
		{
			if(empty($_REQUEST['inner_section'])) $_REQUEST['inner_section']='default';
			$current_tab=$_REQUEST['inner_section'];
			?>
		 <ul class="nav-tab-wrapper">
			 <li></li>
			 <li>
				 <a href="admin.php?page=wc-settings&tab=shipping&section=wf_woocommerce_shipping_pro" class="nav-tab <?php echo $current_tab=="default"?"nav-tab-active":''; ?>">Shipping Rules</a>
			 </li>
			 <li>
				 <a href="admin.php?page=wc-settings&tab=shipping&section=wf_woocommerce_shipping_pro&inner_section=settings" class="nav-tab <?php echo $current_tab=="settings"?"nav-tab-active":''; ?>">Settings</a>
			 </li>
			 
		 </ul>
			<?php
		}

		function admin_options() {
		 ?>
		 <div class="wf-banner below-h2 " style="text-align:left">
		 	<p>
		 		<a href="//www.xadapter.com/product/woocommerce-table-rate-shipping-pro-plugin/" target="_blank" class="button button-primary">Checkout the premium version</a>
		 		<a href="//support.xadapter.com/" target="_blank" class="button button-primary">Got a complex shipping scenario? Contact us</a>
		 	</p>
		 </div>

		 <h2><?php _e($this->method_title,'woocommerce'); ?></h2>
		 <?php echo $this->method_description; ?>
		 </br></br> 
		 <?php  $this->get_inner_sections(); ?>
		 <div class="clear"></div>
		 </br>
		 <table class="form-table">
		 <?php $this->generate_settings_html(); ?>
		 </table> <?php
		 }
		/**
		 * Initialise Settings Form Fields
		 */
		 function init_form_fields() {
			 if(empty($_REQUEST['inner_section'])) $_REQUEST['inner_section']='default';
			 switch ($_REQUEST['inner_section']) {
				 case 'default':
								$this->form_fields  = array(
															'rate_matrix' => array('type' => 'rate_matrix'),									
															);					
					 break;
				 case 'settings':
					 echo "<style>select {	padding: 0px !important;}</style>";
					$this->form_fields  = $this->get_settings_page_fields();					
					 break;
				 
			 }

		} // End init_form_fields()
		

	function get_settings_page_fields(){
		///////code to support old fields value after Updated UI////////
		$mode=$this->get_option('calculation_mode');
		$tmp=explode('_',$mode);
		$min_max=array_pop($tmp);
		$min_max=array_pop($tmp);
		if(empty($min_max)) 
			$min_max='max';
		$calc_min_max=$min_max.'_cost';
		$tmp=implode('_',$tmp);
		$calc_per=$tmp;
		$calc_min_max=$this->get_option('calc_min_max',$calc_min_max);
		$calc_per=$this->get_option('calc_per',$calc_per);
		///////////////////////////////////////////////////////////////////////
		return array(
			'enabled'	=> array(
				'title'   => __( 'Enable/Disable', 'wf_woocommerce_shipping_pro' ),
				'type'	=> 'checkbox',
				'label'   => __( 'Enable this shipping method', 'wf_woocommerce_shipping_pro' ),
				'default' => 'no',
			),						
			'title'	  => array(
				'title'	   => __( 'Method Title', 'wf_woocommerce_shipping_pro' ),
				'type'		=> 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wf_woocommerce_shipping_pro' ),
				'default'	 => __( $this->method_title, 'wf_woocommerce_shipping_pro' ),
			),
			'displayed_columns' => array(
				'title'	   => __( 'Display/Hide matrix columns', 'wf_woocommerce_shipping_pro' ),
				'type'		=> 'multiselect',
				'description' => __( 'Select the columns which are used in the matrix. Please Save changes to reflect the modifications.', 'wf_woocommerce_shipping_pro' ),
				'class'	   => 'chosen_select',
				'css'		 => 'width: 450px;',
				'default'	 => array(
					'shipping_name',
					'weight' , 
					'fee'   ,
					'cost' ,
					'weigh_rounding'   
				), //if change the default value here change 'settings/html-rate-marix.php'
				'options'	 => array(
					'shipping_name' => __( 'Method title', 'wf_woocommerce_shipping_pro' ),
					'country_list'	=> __( 'Country list', 'wf_woocommerce_shipping_pro' ),
					'weight'	=> __( 'Weight', 'wf_woocommerce_shipping_pro' ),
					'fee'	=> __( 'Base cost', 'wf_woocommerce_shipping_pro' ),
					'cost'	=> __( 'Cost/unit', 'wf_woocommerce_shipping_pro' ),
					'weigh_rounding'	=> __( 'Weight Round', 'wf_woocommerce_shipping_pro' )					
				),
				'custom_attributes' => array(
						'data-placeholder' => __( 'Choose matrix columns', 'wf_woocommerce_shipping_pro' )
				)
			),			
			'tax_status' => array(
				'title'	   => __( 'Tax Status', 'wf_woocommerce_shipping_pro' ),
				'type'		=> 'select',
				'description' => '',
				'default'	 => 'none',
				'options'	 => array(
						'taxable' => __( 'Taxable', 'wf_woocommerce_shipping_pro' ),
						'none'	=> __( 'None', 'wf_woocommerce_shipping_pro' ),
				),
			),
			'debug'	=> array(
				'title'   => __( 'Debug', 'wf_woocommerce_shipping_pro' ),
				'type'	=> 'checkbox',
				'label'   => __( 'Debug this shipping method', 'wf_woocommerce_shipping_pro' ),
				'default' => 'no',
			),										
		);
	}
	 
	public function wf_remove_local_pickup_free_label($full_label, $method){
		if( strpos($method->id, $this->id) !== false) $full_label = str_replace(' (Free)','',$full_label);
		return $full_label;
	}
	
	function wf_hidden_matrix_column($column_name){
		return in_array($column_name,$this->displayed_columns) ? '' : 'hidecolumn';	
	}
	
	public function validate_rate_matrix_field( $key ) {
		$rate_matrix		 = isset( $_POST['rate_matrix'] ) ? $_POST['rate_matrix'] : array();
		return $rate_matrix;
	}

	public function generate_rate_matrix_html() {
		include_once('settings/html-rate-marix.php');
	}

	function calculate_shipping( $package = array() ) {
		$rules = $this->wf_filter_rules( '', $package['destination']['country'], '', '', '', '', '', $package );
		$costs = $this->wf_calc_cost($rules, $package);	
		$this->wf_add_rate(apply_filters( 'wf_woocommerce_shipping_pro_shipping_costs', $costs),$package);	
	}

	function wf_get_weight($package_items){
		$total_weight = 0;
		foreach($package_items as $package_item){		
			$_product = $package_item['data'];
			$total_weight += $_product->get_weight() * $package_item['quantity'];
		}
		return $total_weight;
	}
	
	
	function wf_get_item_count($package_items){
		$total_count = 0;
		foreach($package_items as $package_item){		
			$_product = $package_item['data'];
			$total_count += apply_filters( 'wf_shipping_pro_item_quantity', $package_item['quantity'],$_product->id);			
		}
		return $total_count;
	}
	
	function wf_filter_rules( $zone='', $country='', $state='', $city='', $post_code='', $shipping_classes='',$product_category='',$package ) {
		$selected_rules = array();
		if(sizeof($this->rate_matrix) > 0) {
			foreach($this->rate_matrix as $key => $rule ) {
				$satified_general=false;
				if( $this->wf_compare_array_rule_field($rule,'country_list',$country,'rest_world','any_country') ){
						$satified_general=true;	
				}
				
				if($satified_general){
					foreach ( $this->calc_mode_strategy->wf_get_grouped_package($package) as $item_id => $values ) {
						if(	$this->wf_compare_range_field($rule,'weight',$this->wf_get_weight($values))
							&& $this->wf_compare_range_field($rule,'item',$this->wf_get_item_count($values)) ){
								if(!isset($rule['item_ids'])) $rule['item_ids'] = array(); 
								$rule['item_ids'][] = $item_id;
						}												
					}
					if(isset($rule['item_ids'])) $selected_rules[] = $rule;						
				}					
			}					
		}
		return $selected_rules;	 
	}
	

	function wf_compare_array_rule_field( $rule, $field_name, $input_value, $const_rest, $const_any, $item_id=false ){
		//if rule_value is null then shipping rule will be acceptable for all
		global $rule_value;
		if (!empty($rule[$field_name]) && in_array($field_name,$this->displayed_columns) ){
			$rule_value = $rule[$field_name];
			$this->wf_debug("rule_value : $rule_value[0]");
		}
		else	
			return true;
		
		if (is_array($rule_value) && count($rule_value) == 1){
			if($rule_value[0] == $const_rest)	
				return $this->wf_partof_rest_of_the($input_value,$field_name,$item_id,$rule);
			elseif($rule_value[0] == $const_any)
				return true;	
		}
		
		if(!is_array($input_value)){
			return in_array($input_value,$rule_value);
		}
		else{			
			if( $item_id ){
				if( isset($input_value[$item_id]) && is_array($input_value[$item_id]) ){					
					return count( array_intersect($input_value[$item_id],$rule_value) ) > 0;
				}
				else
					return false;
			}else{ //case of zone.
				return count(array_intersect($input_value,$rule_value)) > 0;
			}
		}
	}
	
	function wf_compare_range_field( $rule,$field_name, $totalweight) {
		$weight = $totalweight;
		if (!empty($rule['min_'.$field_name]) && $weight <= $rule['min_'.$field_name]) 
			return false;
		if (!empty($rule['max_'.$field_name]) && $weight > $rule['max_'.$field_name]) 
			return false;					
		return true;	
	}	

	function wf_partof_rest_of_the( $input_value,$field_name,$item_id=false ,$current_rule) {
		global $combined_rule_value;
		$combined_rule_value = array();
		if ( sizeof( $this->rate_matrix ) > 0) {
			foreach ( $this->rate_matrix as $key => $rule ) {
				if(!empty($rule[$field_name]))
					$combined_rule_value = array_merge($rule[$field_name],$combined_rule_value);
				
			}					
		}
		
		if(!is_array($input_value)){
			//county not defined as part of any other rule 
			if(!in_array($input_value,$combined_rule_value))
				return true;
			return false;
		}
		else{
			//returns true if at least one product category doesn't exist combined list.
			if($item_id !== false && isset($input_value[$item_id]) && is_array($input_value[$item_id])){
				//This is a case where product with NO shipping class in the cart. 
				//This will not handle the case where multiple product in the group and some products are not 'NO Shipping Class' 
				//So finally if its NO Shipping Class case we will consider it as matching with Rest of the shipping class. 
				if(empty($input_value[$item_id]))
					return true;
				return count(array_diff($input_value[$item_id],$combined_rule_value)) > 0;
			}
				
			return false;				
		}						
	}
	
	function wf_calc_cost( $rules ,$package) {
		$cost = array();
		if ( sizeof($rules) > 0) {
			$grouped_package = $this->calc_mode_strategy->wf_get_grouped_package($package);
			foreach ( $rules as $key => $rule) {
				$method_group = null;	
				$item_ids = isset($rule['item_ids']) ? $rule['item_ids'] : null;
				if(!empty($item_ids)){
					foreach($item_ids as $item_key => $item_id){
						if(empty($grouped_package[$item_id])) continue;
						$shipping_cost = $this->wf_get_rule_cost($rule,$grouped_package[$item_id]);
						if($shipping_cost !== false){
							if(isset($cost[$method_group]['cost'][$item_id])){
								if($cost[$method_group]['cost'][$item_id] > $shipping_cost && $this->row_selection_choice == 'min_cost'
								|| $cost[$method_group]['cost'][$item_id] < $shipping_cost && $this->row_selection_choice == 'max_cost'){
									 $cost[$method_group]['cost'][$item_id] = $shipping_cost;
									 $cost[$method_group]['shipping_name'] = !empty($rule['shipping_name']) ? $rule['shipping_name'] : $this->title;
								}							   
							}
							else{
								if(!isset($cost[$method_group])) {
									$cost[$method_group] = array();
									$cost[$method_group]['cost'] = array();
								}
								
								$cost[$method_group]['shipping_name'] = !empty($rule['shipping_name']) ? $rule['shipping_name'] : $this->title;
								$cost[$method_group]['cost'][$item_id] = $shipping_cost;																								
							}
						}		   
					}
				}						   
			}
		}	   
		return 	$cost;
	}	

	function wf_get_rule_cost( $rate,$grouped_package) {
		$based_on = 'weight';

		$totalweight = $this->wf_get_weight($grouped_package);		
		
		
		$weight = floatval($totalweight);
		
		if( isset($rate['min_'.$based_on]) ){
			$weight = max(0, $weight - floatval($rate['min_'.$based_on]) );
		}

		$weightStep = isset($rate['weigh_rounding']) ? floatval($rate['weigh_rounding']) : 1;

		if (trim($weightStep)) 
			$weight = floatval(ceil($weight / $weightStep) * $weightStep);

		$rate_fee   = isset($rate['fee']) ? floatval(str_replace($this->decimal_separator, '.', $rate['fee'])) : 0;
		$rate_cost  = isset($rate['cost']) ? floatval(str_replace($this->decimal_separator, '.', $rate['cost'])) : 0;
		$price = $rate_fee + $weight * (float)$rate_cost;
		
		if ( $price !== false) return $price;
		
		return false;		
	}	
	
	function wf_check_all_item_exists($costs,$package_content){
		return count(array_intersect_key($costs,$package_content)) == count($package_content);
	}
	
	function wf_add_rate($costs,$package) {
		if ( sizeof($costs) > 0) {
			$grouped_package = $this->calc_mode_strategy->wf_get_grouped_package($package);
			foreach ($costs as $method_group => $method_cost) {
				if($this->wf_check_all_item_exists($method_cost['cost'],$grouped_package)){
					if(isset($method_cost['shipping_name']) && isset($method_cost['cost'])){
		
						$method_id = sanitize_title( $method_group . $method_cost['shipping_name'] );
						$method_id = preg_replace( '/[^A-Za-z0-9\-]/', '', $method_id ); //Omit unsupported charectors
						$this->add_rate( array(
										'id'		=> $this->id . ':' . $method_id,
										'label'	 => $method_cost['shipping_name'],
										'cost'	  => $method_cost['cost'],
										'taxes'	 => '',
										'calc_tax'  => $this->calc_mode_strategy->wf_calc_tax()));
					}
				}								
			}
		}
	}
	
}
