<?php

class xa_sp_rules_table extends WP_List_Table {
	var $data		= array();
	var $displayed_columns	= array();
	var $counter 		= 0;
	var $country_list 	= array();

	function __construct( $args = array() ) {
		parent::__construct($args);
		$this->data 			= $args['data'];
		$this->displayed_columns	= $args['displayed_columns'];
		$this->country_list 		= wf_get_shipping_countries();
		$this->cost_based_on		= array('weight'=>'Weight','item'=>'Item','price'=>'Price');
	}

	function get_columns(){
		$all = array(
			'cb'			=> 'cb',
			'shipping_name' 	=> 'Shipping Name <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">Would you like this shipping rule to have its own shipping service name? If so, please choose a name. Leaving it blank will use Method Title as shipping service name.</span></span>',
			'country_list'		=> 'Countries <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">Select list of countries which this rule will be applicable.  Leave it blank to apply this rule for all the countries.</span></span>',
			'weight'		=> 'Weight <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">If the min value entered is .25 and the order weight is .25 then this rule will be ignored. if the min value entered is .25 and the order weight is .26 then this rule will be be applicable for calculating shipping cost. if the max value entered is .25 and the order weight is .26 then this rule will be ignored. if the max value entered is .25 and the order weight is .25 or .24 then this rule will be be applicable for calculating shipping cost.</span></span>',
			'fee'			=> 'Base Cost <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">Base/Fixed cost of the shipping irrespective of the weight/item count/price.</span></span>',
			'cost'			=> 'Cost Per Unit <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">Per weight/item count/price unit cost. This cost will be added on above the base cost.If select Based on as weight, Total shipping Cost = Base cost + (order weight - minimum weight) * cost per unit.</span></span>',
			'weigh_rounding' 	=> 'Rounding <span class="xa-tooltip"><img src="'.site_url('/wp-content/plugins/woocommerce/assets/images/help.png').'" height="16" width="16" /><span class="xa-tooltiptext">How would you like to round weight/item count/price? Lets take an example with weight. if the value entered is 0.5 and the order weight is 4.4kg then shipping cost will be calculated for 4.5kg, if the value entered is 1 and the order weight is 4.4kg then shipping cost will be calculated for 5kg, if the value entered is 0 and the order weight is 4.4kg then shipping cost will be calculated for 4.4 kg.</span></span>'
		);
		$columns = array( 'cb' => 'cb' );
		foreach($all as $key=>$val){
			if( in_array($key,$this->displayed_columns) ){
				$columns[$key]=$val;
			}
		}
		return $columns;
	}

	function prepare_items() {
		$columns 	= $this->get_columns();
		$hidden 	= array();
		$sortable 	= array();

		$this->_column_headers 	= array($columns, $hidden, $sortable);
		$this->items 			= $this->data;
	}
	
	function column_default( $item, $column_name ) {
		
		$rule_no = $item['ID'];

		switch( $column_name ) { 
			case 'shipping_name':
				$html 	 = "<textarea rows='2'  wrap='soft' readonly class='xa_sp_label typetext' type=text rule_no=$rule_no rule_col_name=$column_name name=rate_matrix[$rule_no][$column_name] value=".$item[ $column_name ]." >".$item[ $column_name ]."</textarea>";
				$html 	.= "<div style='height:20px;margin-bottom:5px;'><a class='button button-primary edit' style='display:none;margin-top: 10px;maring-left:5px;' >Edit</a>";
				$html 	.= "<a class='button-primary delete' style='display:none;margin-top: 10px;maring-left:5px;' >Delete</a>";
				$html 	.= "<a class='button-primary  duplicate_row' style='display:none;margin-top: 20px;maring-left:5px;' >Duplicate</a>";
				$html 	.= "<a class='button-primary edit_mode revert_changes' style='display:none;margin-top: 20px;maring-left:5px;' >Revert changes</a></div>"; 
				return $html;
				break;

			case 'weigh_rounding':
				$tmpval = !empty($item[ $column_name ]) ? $item[ $column_name ] : '';
				return "<textarea rows='2'  wrap='soft' readonly class='xa_sp_label typetext' type=text rule_no=$rule_no rule_col_name=$column_name name=rate_matrix[$rule_no][$column_name] value=".$tmpval." >".$tmpval."</textarea>";
				break;

			case 'weight':
				$max_val = !empty($item['max_weight']) ? $item['max_weight'] : '';
				$min_val = !empty($item['min_weight']) ? $item['min_weight'] : (!empty($max_val) ? 0 : '');
				return "<input autocomplete=off readonly class='xa_sp_label typetext' type=text rule_no=$rule_no rule_col_name=min_weight name=rate_matrix[$rule_no][min_weight] value='".$min_val."' style='width: 45%;text-align: right;'/>-<input autocomplete=off readonly class='xa_sp_label typetext' type=text rule_no=$rule_no rule_col_name=max_weight name=rate_matrix[$rule_no][max_weight] value='".$max_val."' style='width: 45%;'/>";
				break;

			case  'fee'	:
			case  'cost':
				$tmpval=!empty($item[ $column_name ])?$item[ $column_name ]:'';
				return "<input autocomplete=off readonly class='xa_sp_label typetext' type=text rule_no=$rule_no rule_col_name=$column_name name=rate_matrix[$rule_no][$column_name] value='".$tmpval."' />";
				break;
			
			case  'country_list':
				return $this->xa_load_textarea_colomn( $rule_no, 'country_list', $item, $this->country_list );
				break;

			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}

	function xa_load_textarea_colomn( $rule_no, $column_name, $item, $value='' ){
		$output='';
		if(!empty($item[$column_name]) && is_array($item[$column_name])){
			foreach($item[$column_name] as $key=>$val){
				$name = ( !empty($value) && isset( $value[$val] ) ) ? $value[$val] : $val;
				$output = $output. "<textarea readonly class='xa_sp_label typecombo' type=text rule_no=$rule_no rule_col_name=$column_name index_val=$val >$name</textarea>"
					. "<input hidden readonly  type=text rule_no=$rule_no rule_col_name=$column_name name=rate_matrix[$rule_no][$column_name][$key] value='$val' />";
			}
		}elseif(!empty($item[$column_name])){
			$tmpval=!empty($item[ $column_name ])?$item[ $column_name ]:'1';
			return "<textarea readonly class='xa_sp_label typecombo' type=text rule_no=$rule_no rule_col_name=$column_name index_val=$item[$column_name] >".$tmpval."</textarea>"
				. "<input hidden readonly  type=text rule_no=$rule_no rule_col_name=$column_name name=rate_matrix[$rule_no][$column_name] value='".$tmpval."' />";
		}else{
			return "<textarea readonly class='xa_sp_label typecombo' type=text rule_no=$rule_no rule_col_name=$column_name index_val='' >	  ...</textarea>";
		}
		return $output;
	}

	function get_bulk_actions() {
		return array('edit'=>'Edit','delete'=>'Delete','duplicate'=>'Duplicate');
	}

	function column_cb( $item ) {
		return  sprintf('<input id="cb-select-%s" type="checkbox" name="sp_selected_rules[]" value="%s">',$item['ID'],$item['ID']);
	}

	function extra_tablenav( $which ){
		$last_index=!empty($this->data)?max(array_keys($this->data)):0;
		echo '<input type="submit" style="margin: 2px;" class="button bulk_action_btn" value="Apply">';
		echo '<input type="submit" style="margin: 2px;margin-left:10px;" class="button addnewbtn" value="Add New">';
		echo '<input type="hidden" id="last_row_index" value="'.$last_index.'" />';
		echo "<img style='margin: 7px 0px; float: right;' src='".wf_plugin_url()."\includes\img\colorcode.png' />";
	}
}