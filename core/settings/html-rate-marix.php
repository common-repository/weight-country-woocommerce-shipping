<?php
	$country_list			= wf_get_shipping_countries();
	$cost_based_on			= array('weight'=>'Weight','item'=>'Item','price'=>'Price');
	$cost_based_on_options	="";
	$country_options		="";
	foreach($country_list as $code=>$name){
		$country_options.="<option value=$code>$name</option>";
	}

	foreach($cost_based_on as $code=>$name){
		$cost_based_on_options.="<option value=$code>$name</option>";
	}
	echo "<style>#the-list{font-size:13px;} "
		//. "tbody tr:hover { background-color: #ffffa5 !important; } "
		. "tbody tr:not(.edit_mode):hover a:not(.edit_mode){ display:inline !important ; margin-left:5px; } "
		. "tbody tr.edit_mode a.edit_mode{ display:inline !important ; margin-left:5px; } "
		. "tbody tr.edit_mode a.new_mode{ display:inline !important ; margin-left:5px; } "
		. "tbody tr.duplicated_row a.edit_mode{ display:none !important ; margin-left:5px; } "
		. "tbody tr.duplicated_row a.delete{  display:inline !important ; margin-left:5px; } "
		. ".wp-list-table .column-id { width: 30px; font-size: 13px;padding: 8px 3px;}"
		. ".wp-list-table .column-shipping_name { width: 120px;font-size: 13px; }"
		. ".wp-list-table .column-country_list { width: 140px; font-size: 13px;}"
		. ".wp-list-table .{width:60px; }"
		. ".wp-list-table .column-weight{ width:100px;font-size:13px;text-align:center; }"

		. ".wp-list-table .column-cost_based_on{width:110px; padding: 8px 0px;font-size: 13px;}"
		. ".wp-list-table .column-fee{width:60px; }"
		. ".wp-list-table .column-cost{width:60px; }"
		. ".wp-list-table .column-weigh_rounding{width:90px; }"
		. ".select2-container{width:100% !important;font-size: 13px;}"
		. ".xa_sp_label{  resize:none;  word-wrap: break-word;  word-break: break-all; display:table-cell !important; word-wrap:break-word !important; border-style:none !important;background:none !important;	 width: 100%;  margin: 0px;box-shadow: none !important;  padding: 0px;} "
		. "#doaction{display:none} #doaction2{display:none} "
		
		//Rule description
		. "tr:hover p.rule_desc{ display:block ! important; background-color : LightSlateGray ! important; color : white ! important} "
		
		//Tooltip style
		. ".xa-tooltip { position: relative;  }"
		. ".xa-tooltip .xa-tooltiptext { visibility: hidden; width: 150px; background-color: black; color: #fff; text-align: center; border-radius: 6px; 
			padding: 5px 0;
			/* Position the tooltip */
			position: absolute; z-index: 1;}"
		. ".xa-tooltip:hover .xa-tooltiptext {visibility: visible;}"
		//End of tooltip
		. "</style>";
	?>
<script>
jQuery( function ($) {
	// This variable will be used to add new rows or duplicate the rows
	var new_row_index= parseInt(jQuery('#last_row_index').val());
	
	$('input.bulk_action_btn').on('click',function(){
		let selected_val=$(this).prev().find('select').val();
		run_bulk_action(selected_val);
		return false;
	});

	$('input.addnewbtn').on('click',function(){ 
		var new_row='<tr><th scope="row" class="check-column"><input id="cb-select-1" type="checkbox" name="sp_selected_rules[]" value="1"></th><td class="shipping_name column-shipping_name has-row-actions column-primary" data-colname="Shipping Name"><textarea rows="2" wrap="soft" readonly="" class="xa_sp_label typetext" type="text" rule_no="" rule_col_name="shipping_name" name="rate_matrix[][shipping_name]" placeholder="Shipping Name" value=""></textarea><div style="height:20px;margin-bottom:5px;"><a class="button-primary delete" style="display:inline !important ; margin-left:5px;">Delete</a></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="country_list column-country_list" data-colname="Countries"><textarea readonly="" class="xa_sp_label typecombo" type="text" rule_no="" rule_col_name="country_list"  placeholder="Select Country" index_val=""></textarea><input hidden="" readonly="" type="text" rule_no="" rule_col_name="country_list" name="rate_matrix[][country_list][0]" value=""></td><td class="weight column-weight" data-colname="Weight" rule_no="" rule_col_name="max_weight"><input autocomplete="off" class="typetext" type="text" rule_no="" rule_col_name="min_weight" name="rate_matrix[][min_weight]" value="" style="width: 100%;"><input autocomplete="off" class="typetext" type="text" rule_no="" rule_col_name="max_weight" name="rate_matrix[][max_weight]" value="" style="width: 100%;"></td><td class="fee column-fee" data-colname="fee"><input autocomplete="off" readonly="" class="xa_sp_label typetext" type="text" rule_no="" rule_col_name="fee" name="rate_matrix[][fee]" placeholder="Fee" value=""></td><td class="cost column-cost" data-colname="cost"><input autocomplete="off" readonly="" class="xa_sp_label typetext" type="text" rule_no="" rule_col_name="cost" name="rate_matrix[][cost]" placeholder="Cost" value=""></td><td class="weigh_rounding column-weigh_rounding" data-colname="Rounding"><textarea rows="2" wrap="soft" readonly="" class="xa_sp_label typetext" type="text" rule_no="" rule_col_name="weigh_rounding" name="rate_matrix[][weigh_rounding]" placeholder="Rounding" value=""></textarea></td></tr>';

		new_row_index++;
		let new_row_element	= $(new_row);
		
		new_row 			='<tr>';
		new_row_element.find('td,th').each(function(key,ele){
			if( $(ele).is('td') )
				new_row+='<td class="'+ele.className+'">';
			
			if($(ele).is('th'))
				new_row+='<th class="'+ele.className+'">';
			
			$(ele).find('select,input,textarea,div').each(function(k,n){
				n=n.outerHTML.replace('rule_no=""', 'rule_no="'+new_row_index+'"');
				n=n.replace('rate_matrix[]', 'rate_matrix['+new_row_index+']');
				new_row+=n;
			});

			if($(ele).is('td'))
				new_row+='</td>';
			
			if($(ele).is('th'))
				new_row+='</th>';
		});
	
		new_row+='</tr>';
		new_row=$(new_row);
		$('#the-list').find('tr').last().after(new_row);
		$('#the-list').find('tr').last().trigger('edit');
		$('#the-list').find('tr').last().find('th').css('background','lightgreen');
		$('#the-list').find('tr').last().addClass('new_mode');
		jQuery('#last_row_index').val(new_row_index);
		
		$(hidden_columns).each(function(index,col){
			$('.'+col).hide();
		});
		
		return false;
	});

	function run_bulk_action(selected_val){
		if(selected_val=='-1'){
			alert('Please select any operation from the drop down list');
		}else if(selected_val=='edit'){
			$selected_rows=jQuery('input:checkbox:checked');
			if($selected_rows.size()<=0) {alert('Please select some rows'); return 0;}
			
			$selected_rows.each(function(index,child){
			$r=jQuery(child).closest('tbody>tr');
			if($r.is('tr')){
				$r.trigger('edit');
			}
			});
		}else if(selected_val=='delete'){ 
			$selected_rows=jQuery('input:checkbox:checked');
			if($selected_rows.size()<=0) {alert('Please select some rows'); return 0;}
			
			$selected_rows.each(function(index,child){
			$r=jQuery(child).closest('tbody>tr');
			if($r.is('tr')){
				$r.trigger('delete');
			}
			});
		}else if(selected_val=='duplicate'){
			$selected_rows=jQuery('input:checkbox:checked');
			if($selected_rows.size()<=0) {alert('Please select some rows'); return 0;}
			
			$selected_rows.each(function(index,child){
				$r=jQuery(child).closest('tbody>tr');
				if($r.is('tr')){
					$r.trigger('duplicate');
				}
			});
		}
	}

	$('#the-list').on('dblclick','tr:not(.edit_mode):not(.new_mode)',function(e){
		if(jQuery(e.srcElement).is('a')) return false;
		let element=$(this);
		let row=element;

		/*---code to save a row copy to revert row changes--*/
		let new_element=row.clone();
		new_element.text(new_element.html());
		new_element.hide();
		row.after(new_element);
		/*------------*/
		row.trigger('edit');
	});
	
	$('#the-list').on('edit','tr',function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		e.preventDefault();

		let row = $(this);
		row.addClass('edit_mode');
		row.find('th').css('background','lightblue');
		row.find('input.typetext').trigger('edit');
		row.find('textarea.typetext').trigger('edit');
		row.find('textarea.typecombo').trigger('edit');
	});

	$('#the-list').on('delete','tr',function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		e.preventDefault();
		let row=$(this);
		row.css('display','none');
		row.text(row.html());
		let html='<tr ><td><a style="display: inline; margin-left: 5px;" class="button undodelete">Undo delete</a></td></tr>';	
		row.after(html);
	});
	
	$('#the-list').on('duplicate','tr',function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		e.preventDefault();
		let row=$(this);
		let new_row_index= jQuery('tbody>tr').size() + 1;
		$duplicate_row=row.clone();
		
		$duplicate_row.find('input,textarea').each(function(key,ele){
			if(jQuery(ele).attr('name')){
				let new_name=jQuery(ele).attr('name').replace(/\d+/, new_row_index);
				jQuery(ele).attr('name',new_name);
			}
			if(jQuery(ele).attr('rule_no')){
				jQuery(ele).attr('rule_no',new_row_index);
			}
			
		});

		$duplicate_row.find('.id').text(new_row_index);
		$duplicate_row.attr('id','duplicate_'+new_row_index);
		$duplicate_row.addClass('duplicated_row');
		row.after($duplicate_row);
		$duplicate_row.addClass('edit_mode');
		$duplicate_row.css('background','#DCDCDC');
		$duplicate_row.trigger('edit');
	});

	var old;
	$("textarea,input").keypress(function(event) {
		if (event.which == 13) {	
			jQuery(this).focusout();
			event.preventDefault();						
		}
	});
	
	$('#the-list').on('click','.edit',function(e){
		let element=$(this);
		let row=element.parent().parent().parent();		
		//////////code to save a row copy to revert row changes
		let new_element=row.clone();
		new_element.text(new_element.html());
		new_element.hide();
		row.after(new_element);
		//////////////////////////////////////////
		row.trigger('edit');
	});

	$('#the-list').on('click','.delete',function(e){
		let element=$(this);
		let row=element.parent().parent().parent(); 
		row.css('display','none');
		row.text(row.html());
		let html='<tr ><td style=""><a style="display: inline; margin-left: 5px;" class="button undodelete">Undo delete</a></td></tr>';	
		row.after(html);
	});		
	
	$('#the-list').on('click','.duplicate_row',function(e){
		let element=$(this);
		let row=element.parent().parent().parent();	
		new_row_index++;
		$duplicate_row=row.clone();
		$duplicate_row.find('input,textarea').each(function(key,ele){
			if(jQuery(ele).attr('name')){
				let new_name=jQuery(ele).attr('name').replace(/\d+/, new_row_index);
				jQuery(ele).attr('name',new_name);
			}
			if(jQuery(ele).attr('rule_no')){
				jQuery(ele).attr('rule_no',new_row_index);
			}
			
		});

		$duplicate_row.find('.id').text(new_row_index);
		$duplicate_row.attr('id','duplicate_'+new_row_index);
		$duplicate_row.addClass('duplicated_row');
		row.after($duplicate_row);
		$duplicate_row.addClass('edit_mode');
		$duplicate_row.find('th').css('background','#DCDCDC');
		$duplicate_row.find('input.typetext').trigger('edit');
		$duplicate_row.find('textarea.typetext').trigger('edit');
		$duplicate_row.find('textarea.typecombo').trigger('edit');
	});
	
	$('#the-list').on('click','.revert_changes',function(e){
		let element=$(this);
		let row=element.parent().parent().parent();	
		let old_row=row.next();
		old_row.html(old_row.text());
		old_row.show();
		row.remove();
		
	});
		
	$('#the-list').on('click','.undodelete',function(e){
		let element=$(this);
		let row=element.parent().parent();	
		let element_to_recover=row.prev();
		element_to_recover.html(element_to_recover.text());
		element_to_recover.show();
		element.parent().parent().remove();
	});	 
		
	$('#the-list').on('edit','textarea.typetext,input.typetext',function(e){	
		e.preventDefault();
		e.stopImmediatePropagation();
		e.preventDefault();
		rule_no=$(this).attr('rule_no');
		rule_col_name=$(this).attr('rule_col_name');				
		$(this).removeAttr('readonly');
		$(this).removeClass('xa_sp_label');
		$(this).css('width','100%');		  
		$(this).parent().attr('rule_no',rule_no);
		$(this).parent().attr('rule_col_name',rule_col_name);
	});

	$('#the-list').on('edit','textarea.typecombo',function(e){	 
		e.preventDefault();
		e.stopImmediatePropagation();
		e.preventDefault();				
		rule_no=$(this).attr('rule_no');
		rule_col_name=$(this).attr('rule_col_name');
		let country_list='<select multiple class="xa_dynamic_select" ><?php echo $country_options; ?></select>';
		let cost_based_on='<select style="width:100%;font-size: 12px;" class="xa_dynamic_select" name=rate_matrix['+rule_no+']['+rule_col_name+'] ><?php echo $cost_based_on_options; ?></select>';
		if($(this).parent().hasClass('country_list'))
		{
			$(this).parent().append(country_list);
		}
		else if($(this).parent().hasClass('cost_based_on')){
			$(this).parent().append(cost_based_on);
		}
						
		let ele=$(this).siblings('select.xa_dynamic_select');
		if($(this).parent().hasClass('cost_based_on')) ele.val(ele.prev().val());
		//ele.focus();	
		$(this).parent().attr('rule_no',rule_no);
		$(this).parent().attr('rule_col_name',rule_col_name);
		//$(this).next('span').focus();  
		let selected_vals=[];
		$(this).parent().find('textarea').each(function(index,e2){
			selected_vals.push($(e2).attr('index_val'));
			$(e2).removeClass('xa_sp_label');
			$(e2).hide();
		});
		  
		if(!$(this).parent().hasClass('cost_based_on'))
		{	
			let placeholder_val=$(this).attr("placeholder")?$(this).attr("placeholder"):'';
			ele.select2({placeholder:placeholder_val}).val(selected_vals).trigger('change');
		}else
		{
			$(this).siblings('input').remove();					
		}
	});
				 
		
		
	$('#the-list').on('change','.xa_dynamic_select',function(e){
		rule_no=$(this).parent().attr('rule_no');
		rule_col_name=$(this).parent().attr('rule_col_name');  
		$(this).siblings('textarea').remove();
		$(this).siblings('input').remove();
		if(!$(this).parent().hasClass('cost_based_on')){
			$(this).find("option:selected").each(function(key,option,val){
				let td_element=$(this).parent().parent();
				while(!td_element.is('td')){
					td_element=td_element.parent();
				}
				td_element.append('<input hidden name=rate_matrix['+rule_no+']['+rule_col_name+']['+key+'] value="'+$(option).val()+'" />');
			});						
		}
	});			  

});
</script>


<?php
$rate_matrix	      = !empty($this->settings['rate_matrix']) ? $this->settings['rate_matrix'] : array(); 
$displayed_columns    = !empty($this->settings['displayed_columns']) ? $this->settings['displayed_columns'] : array('shipping_name','weight','fee','cost','weigh_rounding'); //if change the default value here change 'woocommerce-shipping-pro-core.php -> get_settings_page_fields()'
$all_col=array(
	'shipping_name',
	'country_list',
	'weight',
	'fee',
	'cost',
	'weigh_rounding'
 );
$hidden_col = array_diff($all_col,$displayed_columns);
foreach($rate_matrix as $key=>$val){
	$rate_matrix[$key]['ID']=$key;
}
$tmp = array_values($hidden_col);
echo "<script>";
echo "var hidden_columns=".json_encode($tmp);
echo "</script>";
if(!class_exists('xa_sp_rules_table')){
    include_once('class-xa-shipping-pro-rules-table.php');
}
$list_table = new xa_sp_rules_table( array( 'data' => $rate_matrix, 'displayed_columns' => $displayed_columns ) );
$list_table->prepare_items(); 
$list_table->display();