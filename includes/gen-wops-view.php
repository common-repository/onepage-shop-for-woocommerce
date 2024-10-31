<?php if(!defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_shortcode('gen_wops_onepage_shop', 'gen_wops_product');

function gen_wops_addtocart() {
 
  global $woocommerce;
  if(check_ajax_referer('usts_gen_wops_addtocart')){
	  //$vid=$_POST['wops_prod_var_id'];
	  $pid=sanitize_text_field($_POST['wops_prod_id']);
	  $vid=sanitize_text_field($_POST['wops_prod_var_id']);
	  $pqty=sanitize_text_field($_POST['wops_prod_qty']);
	
	 
	  if($vid==0){
		
		$product = get_product($pid);
		
		$bool=$product->is_sold_individually();
		if($bool==1){
		  $chk_cart=gen_wops_check_cart_item_by_id($pid);
		  if($chk_cart==0){
			echo 'Already added to cart';
			exit;
		  }
		}
	  }else{
		
		$product = get_product($vid);
		$bool=$product->is_sold_individually();
		if($bool==1){      
		  $chk_cart=gen_wops_check_cart_item_by_id($vid);
		  if($chk_cart==0){
			echo 'Already added to cart';
			exit;
		  }
		}
	  }
	
	  $stock=$product->get_stock_quantity();
	  $availability = $product->get_availability();
	  
	  if($availability['class']=='out-of-stock'){
		echo 'Out of stock';
		exit;
	  }
		   
	  if($stock!=''){
			foreach($woocommerce->cart->cart_contents as $cart_item_key => $values ) {
			$c_item_id='';
			$c_stock='';
			if($values['variation_id']!=''){
			  $c_item_id=$values['variation_id'];
			}else{
			  $c_item_id=$values['product_id'];
			}
			$c_stock=$values['quantity']+$pqty;
			
			if($vid==0 && $pid==$c_item_id && $c_stock>$stock){
			  
			  $product = get_product($pid);
			  
			  echo 'You have cross the stock limit';
			  exit;
			}else if($vid==$c_item_id && $c_stock>$stock){
			  $product = get_product($vid);
			  
			  echo 'You have cross the stock limit';
			  exit;
			}        
		   }    
	  }
	
	  if($vid==0){
		$z=$woocommerce->cart->add_to_cart($pid,$pqty,null, null, null );
	  }else{    
		$z=$woocommerce->cart->add_to_cart($pid, $pqty, $vid, $product->get_variation_attributes(),null);
	  }
	  
	  echo '1';
	  
	  exit;
  }
}


function gen_wops_check_cart_item_by_id($id) { 
	global $woocommerce;
	
	foreach($woocommerce->cart->get_cart() as $cart_item_key => $values ) {
		$_product = $values['data'];
	
		if($id == $_product->id) {
			return 0;
		}
	}	
	return 1;
}

function gen_wops_cart_amount(){
  global $woocommerce;
  echo $woocommerce->cart->get_cart_total();  
  exit;
}

function gen_wops_product($val) {
  global $woocommerce;
  if (!class_exists('Woocommerce')) {
    echo '<div id="message" class="error"><p>Please Activate Wp WooCommerce Plugin</p></div>';
    return false;
  }
  

  $wops_img_size=40;
?>
<form method="post" id="wops_options">
  <?php
    echo woocommerce_product_dropdown_categories( array(), 1, 0, '' );
    //die('okzzz');
  ?>  
  <select name="wops_front_order_by">
      <option value="date" <?php if (isset($_POST['wops_front_order_by']) && $_POST['wops_front_order_by']=='date'):?> selected="selected"<?php endif;?>>Date</option>
      <option value="name" <?php if (isset($_POST['wops_front_order_by']) && $_POST['wops_front_order_by']=='name'):?> selected="selected"<?php endif;?>>Name</option>

  </select>
  <select name="wops_front_order">
      <option value="ASC" <?php if (isset($_POST['wops_front_order']) && $_POST['wops_front_order']=='ASC'):?> selected="selected"<?php endif;?>>ASC</option>
      <option value="DESC" <?php if (isset($_POST['wops_front_order']) && $_POST['wops_front_order']=='DESC'):?> selected="selected"<?php endif;?>>DESC</option>                
  </select>

  <input type="hidden" value="1" name="wops_hval" />
  <input type="submit" class="wops_search" name="wops_btn_search" value="Search"/>
</form> <br /> 
  <?php
  
  
  $cart_url = $woocommerce->cart->get_cart_url();  
  ?>
<div class="span4 alertAdd" style="opacity: 1; display: block;">
  <div class="alert alert-info"id="wops_alert_info" style="display: none;"> Added to your cart </div>
</div>

<script>  
  //jQuery('#dropdown_product_cat option[value=]').text('All products');
  function gen_wops_add_prod(pid,vid){
    //alert(pid);
    var qty= jQuery('#product_qty_'+vid).val();   
    if(qty==0 || qty==''){
      jQuery('#wops_alert_info').text('Quantity can not be less than 1');
      jQuery('#wops_alert_info').show()
      setTimeout(function(){jQuery('#wops_alert_info').hide()}, 1500);      
      return false;
    }
    if(qty>1000){
      jQuery('#wops_alert_info').text('You have cross the quantity limit');
      jQuery('#wops_alert_info').show()
      setTimeout(function(){jQuery('#wops_alert_info').hide()}, 1500);      
      return false;
    }
    if(vid==0){
      qty= jQuery('#product_qty_'+pid).val();
    }
  
    
    var ajax_url = '<?php echo wp_nonce_url(esc_url(admin_url( 'admin-ajax.php' )),'usts_gen_wops_addtocart'); ?>';
        jQuery.ajax({
          type: "POST",
          url:ajax_url,
          data : {
                  'action':          'gen_wops_addtocart',
                  'wops_prod_id':     pid,
                  'wops_prod_var_id': vid,
                  'wops_prod_qty':    qty
          },
          success: function(response){          
            if(response==1){
              jQuery('#wops_alert_info').text('Added to your cart');
            }else{
              jQuery('#wops_alert_info').text(response);
            }
            
            jQuery.ajax({
              type: "POST",
              url:ajax_url,
              data : {'action': 'gen_wops_cart_amount'},
              success: function(data){                
                jQuery('#wops_cart_price').html(data);
              }
            });
            
             jQuery('#wops_alert_info').show()
             setTimeout(function(){jQuery('#wops_alert_info').hide()}, 2000);          
          }
        });
  }
  
  jQuery(document).ready(function(){
    jQuery(".ajax").colorbox();
  });
  
</script>  
<?php
  $ordby='date';
  $ord='DESC';
  if(isset($_POST['wops_hval']) && isset($_POST['product_cat']) && $_POST['product_cat']==''){
    $ordby=sanitize_text_field($_POST['wops_front_order_by']);
    $ord=sanitize_text_field($_POST['wops_front_order']);
  }
  
   if(!isset($_POST['wops_hval'])){
    if($val){
      $id= $val['category_id'];
      $product_category =  get_term_by( 'id', $id, 'product_cat', 'ARRAY_A' );
      if(!empty($product_category )){
          ?>
            <script>
              jQuery(".dropdown_product_cat option[value='" + '<?php echo $product_category['slug']?>' + "']").attr('selected', 'selected');
            </script>  

          <?php
      }
    }
  }

  if(isset($_POST['wops_hval']) && isset($_POST['product_cat']) && $_POST['product_cat']!=''){
   
    $args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',			
			'orderby' 				=> esc_attr($_POST['wops_front_order_by']),
			'order' 				=> esc_attr($_POST['wops_front_order']),
      'type' => 'numeric',
			'posts_per_page' 		=> 1000,
			'meta_query' 			=> array(
				array(
					'key' 			=> '_visibility',
					'value' 		=> array('catalog', 'visible'),
					'compare' 	=> 'IN'
				)
			),
			'tax_query' 			=> array(
            array(
            'taxonomy' 		=> 'product_cat',
            'terms' 		=> array( esc_attr($_POST['product_cat']) ),
            'field' 		=> 'slug',
            'operator' 		=> 'IN'
          )
		    )
		);
    
  }else{
    $args = array(
        'post_status'         => 'publish',
        'post_type'           => 'product',
        /*'ignore_sticky_posts'	=> 1,*/
        'orderby' 				    => $ordby,
        'type' => 'numeric',
			  'order' 				      => $ord,
        'posts_per_page' 		=> -1
   );
  }

    $loop = new WP_Query( $args );
    
      if ($loop->have_posts()){
        echo '<table><tr><th>Name</th><th>Image</th><th>Price</th><th>Quantity</th><th></th></tr>';
        foreach($loop->posts as $val){
          
          $product = get_product($val->ID );          
            $variation_display=false;
            $variation=false;
            /*if (get_option('wops_display_variation')=='1'){
              $variation_display= true;
            }  */          
            
            if ($variation_display == true){
                $variation_query = new WP_Query();
                $args_variation = array(
                  'post_status' => 'publish',
                  'post_type' => 'product_variation',
                  'posts_per_page'   => -1,  
                  'post_parent' => $val->ID
                );                
                $variation_query->query($args_variation);

                if ($variation_query->have_posts()){
                  $variation=true;
                }
            }
             ini_set('display_errors','Off');                     
            if($variation==true && $product->is_type( 'variable' )){
              $product_name_org=$val->post_title;
              
              
              foreach($variation_query->posts as $var_data){
                 $product = get_product($var_data->ID);
                 
                 $max_stock=1000;                                  
                 if($product->variation_has_stock==1){
                   //$max_stock=$product->total_stock;
                   $max_stock=$product->get_stock_quantity();
                 }
                 $availability=$product->get_availability();
                  if($availability['class']=='out-of-stock'){
                    $max_stock=0;
                  }
                 
                  $prod_att=woocommerce_get_formatted_variation($product->get_variation_attributes(),true);
                  if($prod_att){
                    $product_name='<a href="" class="ajax">'.$product_name_org.'('.$prod_att.')</a>';
                  }else{
                    $product_name='<a href="" class="ajax">'.$product_name_org.'</a>';
                  }
                  
                  $product_price=woocommerce_price($product->get_price());
                  $img_url = GEN_WOPS_BASE_URL. '/images/placeholder.png';
                  if (has_post_thumbnail($var_data->ID)){
                    $img_url2 = wp_get_attachment_url( get_post_thumbnail_id($var_data->ID) );                    
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($var_data->ID), 'thumbnail' );
                    $img_url = $thumb['0'];
                    
                  } else if (has_post_thumbnail($val->ID)){
                    $img_url2 = wp_get_attachment_url( get_post_thumbnail_id($val->ID) );                    
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($val->ID), 'thumbnail' );
                    $img_url = $thumb['0'];                   
                  }
                  echo '<tr><td>'.$product_name.'</td><td><img src="'.$img_url.'" height="'.$wops_img_size.'" width="'.$wops_img_size.'" /></td><td>'.$product_price.'</td>';
                  ?>
                    <td>
                                                                   
                        <?php
                        if($max_stock!=0){                            
                          ?><input type="number" style="width:70px;" value="1" min="1"  max="<?php echo esc_attr($max_stock);?>" name="product_qty_<?php echo $var_data->ID?>" id="product_qty_<?php echo $var_data->ID?>" /><?php                            
                        }else{                            
                           ?><input type="number" style="width:70px;" value="0" min="0" max="0" name="product_qty_<?php echo $var_data->ID?>" id="product_qty_<?php echo $var_data->ID?>" /><?php
                        }
                        ?>
                      
                    </td>  
                  <?php
                  
                  if($product->regular_price && $max_stock!=0){  
                  echo '<td><div class="wops_add_btn"><a onclick="gen_wops_add_prod('.$val->ID.','.$var_data->ID.');"><div class="wops_add_cart"></div></a></div></td></tr>';
                  }  else {
                    echo '<td></td></tr>';
                  }
              
              }//end foreach              
            }else{
                gen_wops_show_prod($val->ID,$wops_img_size, $val->post_title);
            }
        }//end foreach
          echo '</table>';
      }//if  
}

function gen_wops_show_prod($id, $wops_img_size, $post_title){
    $max_stock=500;
    ini_set('display_errors','Off');
    $product=wc_get_product( $id );
    
    if($product->get_stock_quantity()!=''){
      $max_stock=$product->get_stock_quantity();
    }
    $availability=$product->get_availability();
    if($availability['class']=='out-of-stock'){
      $max_stock=0;
    }
    //$product_url = get_permalink($id);                
    $product_name='<a href="" class="ajax">'.esc_attr($post_title).'</a>';
    $product = get_product($id);
    $product_price =$product->get_price_html();
    
    if (has_post_thumbnail($id)){
        $img_url2 = wp_get_attachment_url( get_post_thumbnail_id($id,'thumbnail'));
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($id), 'thumbnail' );
        $img_url = $thumb['0'];

    } else {
        $img_url=GEN_WOPS_BASE_URL. '/images/placeholder.png';
        $img_url2=$img_url;
    }
    echo '<tr><td>'.$product_name.'</td><td><img src="'.esc_url($img_url).'" height="'.esc_attr($wops_img_size).'" width="'.esc_attr($wops_img_size).'" /></td><td>'.$product_price.'</td>';
    ?>
      <td>
          <?php
          if($product->regular_price && $max_stock!=0){
            ?><input type="number" style="width:70px;" value="1" min="0" max="0<?php echo esc_attr($max_stock);?>" name="product_qty_<?php echo $id;?>" id="product_qty_<?php echo $id;?>" /><?php
          }else{
            ?><input type="number" style="width:70px;" value="0" min="0" max="0" name="product_qty_<?php echo $id;?>" id="product_qty_<?php echo $id;?>" /><?php
          }
          ?>        
      </td>  
    <?php
    if($product->regular_price && $max_stock!=0){
      echo '<td><div class="wops_add_btn"><a onclick="gen_wops_add_prod('.$id.', 0);"><div class="wops_add_cart"></div></a></div></td></tr>';
    }else{
      echo '<td></td></tr>';
    }
    
}
add_action( 'wp_ajax_nopriv_gen_wops_addtocart','gen_wops_addtocart' );
add_action( 'wp_ajax_gen_wops_addtocart', 'gen_wops_addtocart' );

add_action( 'wp_ajax_nopriv_gen_wops_cart_amount','gen_wops_cart_amount' );
add_action( 'wp_ajax_gen_wops_cart_amount', 'gen_wops_cart_amount' );