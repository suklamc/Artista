<?php

/**
* @package 		J2Store
* @copyright 	Copyright (c)2016-19 Sasi varna kumar / J2Store.org
* @license 		GNU GPL v3 or later
*/
defined('_JEXEC') or die;
$total_cols = $params->get('number_of_coloums', 3);
$total_count = count($list); $counter = 0;
?>
<div itemscope itemtype="http://schema.org/ItemList" class="temp-product j2store-product-module j2store-product-module-list row-fluid">
	<?php if( count($list) > 0 ):?>
		<?php foreach ($list as $product_id => $product) : ?>
			<?php  $rowcount = ((int) $counter % (int) $total_cols) + 1; ?>
			<!-- single product -->
			<div class="j2store product-<?php echo $product->j2store_product_id; ?> j2store-module-product">

				<div class="spacer">
					<div class="row">
						<div class="col-md-6">	
							<div class="pro-img"><!-- product image if postion is top -->
								<?php if ($product->image_position == 'top') {
									require( __DIR__.'/default_image.php' );
								} ?>
								<?php if($product->pricing->base_price > $product->pricing->price) : ?>
									<div class="sale-tag">
										<?php echo JText::_('J2STORE_SALE_TAG_FOUND'); ?>
									</div>
								<?php endif;?>
									<?php
												if($product->image_position == 'top'){
													$img_class = ' col-sm-12 ';
												}else {
													$img_class = ' col-sm-6 ';
												}
												?>
												<!-- product image if postion is left -->
												<?php if ($product->image_position == 'left') {
													require( __DIR__.'/default_image.php' );
												} ?>
							</div>
						</div>
						<div class="col-md-6">

							<div class="product-info">
								<!-- product title -->
								<?php if($product->show_title): ?>
									<h4 itemprop="name" class="product-title">
										<?php if( $product->link_title ): ?>
											<a itemprop="url"
											href="<?php echo JRoute::_( $product->module_display_link ); ?>"
											title="<?php echo $product->product_name; ?>" >
										<?php endif; ?>

										<?php echo $product->product_name; ?>
										<?php if($product->link_title ): ?>
										</a>
									<?php endif; ?>
								</h4>
							<?php endif; ?>
							<?php if(isset($product->event->afterDisplayTitle)) : ?>
								<?php echo $product->event->afterDisplayTitle; ?>
							<?php endif;?>
							<!-- end product title -->


							<!-- Product price block-->
							<?php echo J2Store::plugin()->eventWithHtml('BeforeRenderingProductPrice', array($product)); ?>
							<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="product-price-container">
								<?php if($product->show_price && $product->show_special_price):?>
									<?php if($product->pricing->base_price != $product->pricing->price):?>
										<?php $class='';?>
										<?php if(isset($product->pricing->is_discount_pricing_available)) $class='strike'; ?>
										<div class="base-price <?php echo $class?>">
											<?php echo J2Store::product()->displayPrice($product->pricing->base_price, $product, $j2params);?>
										</div>
									<?php endif; ?>
									<div class="sale-price">
										<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
									</div>
									<?php elseif ($product->show_price && !$product->show_special_price):?>
										<?php if($product->pricing->base_price != $product->pricing->price):?>
											<?php $class='';?>
											<?php if(isset($product->pricing->is_discount_pricing_available)) $class=''; ?>
											<div class="base-price <?php echo $class?>">
												<?php echo J2Store::product()->displayPrice($product->pricing->base_price, $product, $j2params);?>
											</div>
											<?php else:?>
												<div class="sale-price">
													<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
												</div>
											<?php endif; ?>
											<?php elseif (!$product->show_price && $product->show_special_price):?>
												<?php if($product->pricing->base_price != $product->pricing->price):?>
													<?php $class='';?>
													<?php if(isset($product->pricing->is_discount_pricing_available)) $class=''; ?>
													<div class="base-price <?php echo $class?>">
														<?php echo J2Store::product()->displayPrice($product->pricing->price, $product, $j2params);?>
													</div>
												<?php endif; ?>
											<?php endif;?>
											<?php if($product->show_price_taxinfo ): ?>
												<div class="tax-text">
													<?php echo J2Store::product()->get_tax_text(); ?>
												</div>
											<?php endif; ?>
										</div>
										<?php echo J2Store::plugin()->eventWithHtml('AfterRenderingProductPrice', array($product)); ?>

										<?php if( $product->show_offers && isset($product->pricing->is_discount_pricing_available) && $product->pricing->base_price > 0): ?>
											<?php $discount =(1 - ($product->pricing->price / $product->pricing->base_price) ) * 100; ?>
											<?php if($discount > 0): ?>
												<div class="discount-percentage">
													<?php  echo round($discount).' % '.JText::_('J2STORE_PRODUCT_OFFER');?>
												</div>
											<?php endif; ?>
										<?php endif; ?>
										<!-- end Product price block-->

										<!-- SKU -->
										<?php if( $product->show_sku ) : ?>
											<?php if(!empty($product->variant->sku)) : ?>
												<div class="product-sku">
													<span class="sku-text"><?php echo JText::_('J2STORE_SKU')?></span>
													<span itemprop="sku" class="sku"> <?php echo $product->variant->sku; ?> </span>
												</div>
											<?php endif; ?>
										<?php endif; ?>

										<!-- STOCK -->
										<?php if($product->show_stock && J2Store::product()->managing_stock($product->variant)): ?>
										<div class="product-stock-container">
											<?php if($product->variant->availability): ?>
												<span class="<?php echo $product->variant->availability ? 'instock':'outofstock'; ?>">
													<?php echo J2Store::product()->displayStock($product->variant, $params); ?>
												</span>
												<?php else: ?>
													<span class="outofstock">
														<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>
													</span>
												<?php endif; ?>
											</div>

											<?php if($product->variant->allow_backorder == 2 && !$product->variant->availability): ?>
												<span class="backorder-notification">
													<?php echo JText::_('J2STORE_BACKORDER_NOTIFICATION'); ?>
												</span>
											<?php endif; ?>
										<?php endif; ?>


	<!-- intro text -->
	<?php if(isset($product->event->beforeDisplayContent) && $product->show_beforedisplaycontent) : ?>
		<?php echo $product->event->beforeDisplayContent; ?>
	<?php endif;?>

	<?php if($product->show_introtext): ?>
		<div class="product-short-description"><?php echo $product->module_introtext; ?></div>
	<?php endif; ?>
	<?php if(isset($product->event->afterDisplayContent) && $product->show_afterdisplaycontent) : ?>
		<?php echo $product->event->afterDisplayContent; ?>
	<?php endif;?>
	<!-- end intro text -->
	

										<div class="product-cart-section">

											<div class="product-cart-left-block " >


												<div class="product_cart_block">

													<?php if($j2params->get('catalog_mode', 0) == 0 ): ?>
														<form action="<?php echo $product->cart_form_action; ?>"
															method="post" class="j2store-addtocart-form"
															id="j2store-addtocart-form-<?php echo $product->j2store_product_id; ?>"
															name="j2store-addtocart-form-<?php echo $product->j2store_product_id; ?>"
															data-product_id="<?php echo $product->j2store_product_id; ?>"
															data-product_type="<?php echo $product->product_type; ?>"
															<?php if(isset($product->variant_json)): ?>
																data-product_variants="<?php echo htmlspecialchars($product->variant_json);?>"
															<?php endif; ?>
															enctype="multipart/form-data">

					<?php $cart_type = $product->list_show_cart;//$this->params->get('list_show_cart', 1);
					$product->display_cart_block = true;
					?>
					<?php

					if ($product->product_type=='configurable') {
						$cart_type = 2	;
					}

					if($cart_type == 1) : ?>
						<?php if ( $product->product_type=='simple' || $product->product_type=='downloadable' ):
							require( __DIR__.'/default_options.php' );
						elseif ($product->product_type=='variable'):
							require( __DIR__.'/default_variableoptions.php' );
						elseif ($product->product_type=='configurable'):
							$product->display_cart_block = false;
						endif;
						?>
						<?php elseif( $product->product_type=='configurable' || ($cart_type == 2 && count($product->options)) || $cart_type==3 ):?>
							<!-- we have options so we just redirect -->
							<a href="<?php echo $product->product_link; ?>" class="view-button <?php echo $params->get('choosebtn_class', 'btn btn-success'); ?>"><?php echo JText::_('J2STORE_VIEW_PRODUCT_DETAILS'); ?></a>
							<?php $product->display_cart_block = false; ?>
						<?php endif; ?>

						<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', array($product, J2Store::utilities()->getContext('default_cart'))); ?>

						<?php if ($product->display_cart_block): ?>
							<!-- cart block -->
							<div class="cart-action-complete" style="display:none;">
								<p class="text-success">
									<?php echo JText::_('J2STORE_ITEM_ADDED_TO_CART');?>
									<a href="<?php echo $product->checkout_link; ?>" class="j2store-checkout-link">
										<?php echo JText::_('J2STORE_CHECKOUT'); ?>
									</a>
								</p>
							</div>

							<div id="add-to-cart-<?php echo $product->j2store_product_id; ?>" class="j2store-add-to-cart">

								<?php if($params->get('show_qty_field', 1)): ?>
						<!--<div class="product-qty">
					 		<input type="number" name="product_qty" value="<?php echo (int) $product->quantity; ?>" class="input-mini form-control" min="<?php echo (int) $product->quantity; ?>" step='1' />
					 	</div>-->
					 	<?php else: ?>
					 		<input type="hidden" name="product_qty" value="<?php echo (int) $product->quantity; ?>" />
					 	<?php endif; ?>

					 	<input type="hidden" name="product_id" value="<?php echo $product->j2store_product_id; ?>" />

					 	<button class="button j2store_cart_button <?php echo $params->get('addtocart_button_class', 'btn btn-primary');?>">
					 		<i class="fa fa-shopping-bag"></i><span><?php echo $product->cart_button_text; ?></span>
					 	</button>

					 </div>
					 <?php elseif( !$product->variant->availability ): ?>
					 	<input value="<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>" type="button" class="j2store_button_no_stock btn btn-warning" />
					 	<!-- end cart block -->
					 <?php endif; ?>

					 <?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($product, J2Store::utilities()->getContext('default_cart'))); ?>

					 <input type="hidden" name="option" value="com_j2store" />
					 <input type="hidden" name="view" value="carts" />
					 <input type="hidden" name="task" value="addItem" />
					 <input type="hidden" name="ajax" value="0" />
					 <?php echo JHTML::_( 'form.token' ); ?>
					 <input type="hidden" name="return" value="<?php echo base64_encode( JUri::getInstance()->toString() ); ?>" />
					 <div class="j2store-notifications"></div>
					 <?php if ($product->product_type == 'variable'): ?>
					 	<input type="hidden" name="variant_id" value="<?php echo $product->variant->j2store_variant_id; ?>" />
					 <?php endif ?>
					</form>
				<?php endif;?>

			</div>
		</div>
		<!-- product image if postion is right -->
		<?php if ($product->image_position == 'right') {
			require( __DIR__.'/default_image.php' );
		} ?>
	</div> <!-- end of product_cart_block -->
</div>
</div>
</div>

<!-- Quick view -->
<?php if($product->show_quickview):?>
	<?php if(strpos('?', $product->module_display_link)):?>
		<?php $product->product_quickview_link = JRoute::_( $product->module_display_link.'&tmpl=component' );?>
		<?php else:?>
			<?php $product->product_quickview_link = JRoute::_( $product->module_display_link ).'?tmpl=component';?>
		<?php endif;?>
		<?php JHTML::_('behavior.modal', 'a.modal'); ?>
		<a itemprop="url" style="display:inline;"
		class="modal j2store-product-quickview-modal btn btn-default"
		rel="{handler:'iframe',size:{x: window.innerWidth-180, y: window.innerHeight-180}}"
		href="<?php echo $product->product_quickview_link; ?>">
		<i class="fa fa-eye"></i> <?php //echo JText::_('J2STORE_PRODUCT_QUICKVIEW');?>
	</a>
<?php endif;?>


</div><!-- end of spacer -->

</div>
<!-- end single product -->
<?php endforeach; ?>
<?php endif; ?>
</div>
