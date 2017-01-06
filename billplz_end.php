<!-- Here is the ending page, called at the end of the checkout, just before the user is redirected to the payment platform -->
<div class="hikashop_billplz_end" id="hikashop_billplz_end">
  <!-- Waiting message -->
	<span id="hikashop_billplz_end_message" class="hikashop_billplz_end_message"><?php
	  echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');
  ?></span>
	<span id="hikashop_billplz_end_spinner" class="hikashop_billplz_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<?php
	//Create Bills API
	
	include_once 'billplzapi.php';
			
			$billplz = new billplz;
			$billplz->setCollection($this->vars['collectionid'])
			->setName($this->vars['name'])
			->setEmail($this->vars['email'])
			->setMobile($this->vars['phone'])
			->setAmount($this->vars['amount'])
			->setDeliver($this->vars['deliver'])
			->setReference_1($this->vars['reference_1'])
			->setReference_1_Label($this->vars['reference_1_label'])
			->setDescription($this->vars['description'])
			->setPassbackURL($this->vars['return_url'], $this->vars['callback_url'])
			->create_bill($this->vars['apikey'], $this->vars['mode']);
			$url = $billplz->getURL();
			?>
	
	<!-- To send all requiered information, a form is used. Hidden input are setted with all variables, and the form is auto submit with a POST method to the payment plateform URL -->
	<form id="hikashop_billplz_form" name="hikashop_billplz_form" action="<?php echo $url;?>" method="get">
		<div id="hikashop_billplz_end_image" class="hikashop_billplz_end_image">
			<input id="hikashop_billplz_button" class="btn btn-primary" type="submit" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
<?php
	//foreach( $this->vars as $name => $value )
	//{
	//		echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
	//}
	
	$doc = JFactory::getDocument();
	// We add some javascript code
	$doc->addScriptDeclaration("window.hikashop.ready(function(){ document.getElementById('hikashop_billplz_form').submit(); });");
	JRequest::setVar('noform',1);
?>
	</form>
</div>
