<?php

/*
 * @package A Billplz for Hikashop Payment Plugin
 * @version 3.0.0
 * @author wanzul-hosting.com
 */

// You need to extend from the hikashopPaymentPlugin class which already define lots of functions in order to simplify your work
class plgHikashoppaymentBillplz extends hikashopPaymentPlugin {

    //List of the plugin's accepted currencies. The plugin won't appear on the checkout if the current currency is not in that list. You can remove that attribute if you want your payment plugin to display for all the currencies
    var $accepted_currencies = array("MYR", "RM");
    // Multiple plugin configurations. It should usually be set to true
    var $multiple = true;
    //Payment plugin name (the name of the PHP file)
    var $name = 'billplz';
    // This array contains the specific configuration needed (Back end > payment plugin edition), depending of the plugin requirements.
    // They will vary based on your needs for the integration with your payment gateway.
    // The first parameter is the name of the field. In upper case for a translation key.
    // The available types (second parameter) are: input (an input field), html (when you want to display some custom HTML to the shop owner), textarea (when you want the shop owner to write a bit more than in an input field), big-textarea (when you want the shop owner to write a lot more than in an input field), boolean (for a yes/no choice), checkbox (for checkbox selection), list (for dropdown selection) , orderstatus (to be able to select between the available order statuses)
    // The third parameter is the default value.
    var $pluginConfig = array(
        // User's API Secret Key
        'billplzapikey' => array("API Secret Key", 'input'),
        // User's Collection ID
        'billplzcollectionid' => array("Collection ID", 'input'),
        // To allow Billplz Payment Notification
        'billplzdeliver' => array('Enable Email & SMS Notification', 'boolean', '0'),
        // Billplz Payment Verification Mode. 0 for Callback. 1 for Return
        'billplznotification' => array('Verification Type', 'list', array(
                'Callback' => 'Callback',
                'Return' => 'Return'
            )),
        'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean', '1'),
        //Billplz Mode: Production or Staging
        'mode' => array('Mode', 'list', array(
                'Production' => 'Production',
                'Staging' => 'Staging'
            )),
        // Write some things on the debug file
        'debug' => array('DEBUG', 'boolean', '0'),
        // The URL where the user is redirected after a fail during the payment process
        //'cancel_url' => array('CANCEL_URL_DEFINE','html',''),
        // The URL where the user is redirected after the payment is done on the payment gateway. It's a pre determined URL that has to be given to the payment gateway
        //'return_url_gateway' => array('RETURN_URL_DEFINE', 'html',''),
        // The URL where the user is redirected by HikaShop after the payment is done ; "Thank you for purchase" page
        //'return_url' => array('RETURN_URL', 'input'),
        // The URL where the payment platform the user about the payment (fail or success)
        //'notify_url' => array('NOTIFY_URL_DEFINE','html',''),
        // Invalid status for order in case of problem during the payment process
        'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
        // Valid status for order if the payment has been done well
        'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
    );

    /**
     * The constructor is optional if you don't need to initialize some parameters of some fields of the configuration and not that it can also be done in the getPaymentDefaultValues function as you will see later on
     */
    function __construct(&$subject, $config) {
        $this->pluginConfig['notification'][0] = JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X', 'billplz');

        // This is the cancel URL of HikaShop that should be given to the payment gateway so that it can redirect to it when the user cancel the payment on the payment gateway page. That URL will automatically cancel the order of the user and redirect him to the checkout so that he can choose another payment method
        //$this->pluginConfig['cancel_url'][2] = HIKASHOP_LIVE . "index.php?option=com_hikashop&ctrl=order&task=cancel_order";
        // This is the "thank you" or "return" URL of HikaShop that should be given to the payment gateway so that it can redirect to it when the payment of the user is valid. That URL will reinit some variables in the session like the cart and will then automatically redirect to the "return_url" parameter
        //$this->pluginConfig['return_url'][2] = HIKASHOP_LIVE . "index.php?option=com_hikashop&ctrl=checkout&task=after_end";
        // This is the "notification" URL of HikaShop that should be given to the payment gateway so that it can send a request to that URL in order to tell HikaShop that the payment has been done (sometimes the payment gateway doesn't do that and passes the information to the return URL, in which case you need to use that notification URL as return URL and redirect the user to the HikaShop return URL at the end of the onPaymentNotification function)


        return parent::__construct($subject, $config);
    }

    /**
     * This function is called at the end of the checkout. That's the function which should display your payment gateway redirection form with the data from HikaShop
     */
    function onAfterOrderConfirm(&$order, &$methods, $method_id) {
        // This is a mandatory line in order to initialize the attributes of the payment method
        parent::onAfterOrderConfirm($order, $methods, $method_id);

        // Here we can do some checks on the options of the payment method and make sure that every required parameter is set and otherwise display an error message to the user
        // The plugin can only work if those parameters are configured on the website's backend
        if (empty($this->payment_params->billplzapikey)) {
            // Enqueued messages will appear to the user, as Joomla's error messages
            $this->app->enqueueMessage('You have to configure an API Secret Key for the Billplz plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
            return false;
        } elseif (empty($this->payment_params->billplzcollectionid)) {
            $this->app->enqueueMessage('You have to configure a Collection ID for the Billplz plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
            return false;
        }
        //elseif (empty($this->payment_params->payment_url))
        //{
        //	$this->app->enqueueMessage('You have to configure a payment url for the Example plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
        //	return false;
        //}
        else {
            // Here, all the required parameters are valid, so we can proceed to the payment platform
            // The order's amount, here in cents and rounded with 2 decimals because of the payment platform's requirements
            // There is a lot of information in the $order variable, such as price with/without taxes, customer info, products... you can do a var_dump here if you need to display all the available information
            // Wan Code start here.. hehe

            $address = $this->app->getUserState(HIKASHOP_COMPONENT . '.billing_address');



            if (!empty($address)) {
                $amout = round($order->cart->full_total->prices[0]->price_value_with_tax, 2);
                //$this->pluginConfig['notify_url'][2] = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=' . $this->name . '&tmpl=component&lang=' . $this->locale . $this->url_itemid;
                $notify_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=billplz&tmpl=component&lang=' . $this->locale . $this->url_itemid . '&orderid=' . $order->order_id;
                $return_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&orderid=' . $order->order_id;
                $vars = array(
                    'name' => @$order->cart->billing_address->address_firstname . " " . @$order->cart->billing_address->address_lastname,
                    'email' => $this->user->user_email,
                    'phone' => @$order->cart->billing_address->address_telephone,
                    'apikey' => $this->payment_params->billplzapikey,
                    'collectionid' => $this->payment_params->billplzcollectionid,
                    'description' => "Order Number: " . $order->order_number,
                    'reference_1_label' => "Order ID",
                    'reference_1' => $order->order_id,
                    'amount' => $amout,
                    'deliver' => $this->payment_params->billplzdeliver,
                    'mode' => $this->payment_params->mode,
                    'callback_url' => $notify_url,
                    'return_url' => $notify_url
                );
            }

            // Wan Code end here.. hehe
            // This array contains all the required parameters by the payment plateform
            // Not all the payment platforms will need all these parameters and they will probably have a different name.
            // You need to look at the payment gateway integration guide provided by the payment gateway in order to know what is needed here
            //$vars = array(
            // User's identifier on the payment platform
            //'IDENTIFIER' => $this->payment_params->identifier,
            //The id of the customer
            //'CLIENTIDENT' => $order->order_user_id,
            // Order's description
            //'DESCRIPTION' => "order number : ".$order->order_number,
            // The id of the order which will be given back by the payment gateway when it will notify your plugin that the payment has been done and which will allow you to know the order corresponding to the payment in order to confirm it
            //'ORDERID' => $order->order_id,
            // The platform's API version, needed by the payment platform
            //'VERSION' => 2.0,
            // The amount of the order
            //'AMOUNT' => $amout
            //);
            // To certifiate the values integrity, payment platform use to ask a hash
            // This hash is generated according to the platform requirements
            //$vars['HASH'] = $this->example_signature($this->payment_params->password, $vars);

            $this->vars = $vars;

            // Ending the checkout, ready to be redirect to the plateform payment final form
            // The showPage function will call the example_end.php file which will display the redirection form containing all the parameters for the payment platform
            return $this->showPage('end');
        }
    }

    /**
     * To set the specific configuration (back end) default values (see $pluginConfig array)
     */
    function getPaymentDefaultValues(&$element) {
        $element->payment_name = 'Billplz';
        $element->payment_description = 'Pay using <strong>Maybank2u, CIMB Clicks, Bank Islam, RHB, Hong Leong Bank, Bank Muamalat, Public Bank, Alliance Bank, Affin Bank, AmBank, Bank Rakyat, UOB, Standard Chartered</strong>';
        $element->payment_images = '';
        $element->payment_params->billplzdeliver = false;
        $element->payment_params->notification = "Callback";
        $element->payment_params->mode = "Production";
        $element->payment_params->currency = $this->accepted_currencies[0];
        $element->payment_params->notification = true;
        $element->payment_params->invalid_status = 'cancelled';
        $element->payment_params->verified_status = 'confirmed';
    }

    /**
     * After submiting the platform payment form, this is where the website will receive the response information from the payment gateway servers and then validate or not the order
     */
    function onPaymentNotification(&$statuses) {


        // We first create a filtered array from the parameters received
        $vars = array();

        $filter = JFilterInput::getInstance();

        // A loop to create an array $var with all the parameters sent by the payment gateway with a POST method, and loaded in the $_REQUEST
        foreach ($_REQUEST as $key => $value) {
            $key = $filter->clean($key);
            $value = JRequest::getString($key);
            $vars[$key] = $value;
        }
        include_once 'billplzapi.php';

        //$order_id = (int) @$vars['orderid'];
        $order_id = filter_var($_GET['orderid'], FILTER_SANITIZE_STRING);
        $dbOrder = $this->getOrder($order_id);
        $this->loadPaymentParams($dbOrder);
        $this->loadOrderData($dbOrder);
        $billplz = new billplz;

        if (isset($_GET['billplz']['id'])) {
            $billid = filter_var($_GET['billplz']['id'], FILTER_SANITIZE_STRING);
            $data = $billplz->check_bill($this->payment_params->billplzapikey, $billid, $this->payment_params->mode);

            if ($order_id != $data['reference_1']) {
                return false;
            }

            if (empty($this->payment_params))
                return false;

            // Here we are configuring the "succes URL" and the "fail URL". After checking all the parameters sent by the payment gateway, we will redirect the customer to one or another of those URL (not necessary for our example platform).
            $return_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id . $this->url_itemid;
            $cancel_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order_id . $this->url_itemid;
            if ($data['paid']) {
                $this->app->redirect($return_url);
            } else {
                $this->app->redirect($cancel_url);
            }
        } elseif (isset($vars['id'])) {
            $data = $billplz->check_bill($this->payment_params->billplzapikey, $vars['id'], $this->payment_params->mode);
            // The load the parameters of the plugin in $this->payment_params and the order data based on the order_id coming from the payment platform
            if ($order_id != $data['reference_1']) {
                return false;
            }

            // With the order, we can load the payment method, and thus all the payment parameters
            if (empty($this->payment_params))
                return false;

            // Debug mode activated or not
            if ($this->payment_params->debug) {
                // Here we display debug information which will be catched by HikaShop and stored in the payment log file available in the configuration's Files section.
                echo print_r($vars, true) . "\n\n\n";
                echo print_r($dbOrder, true) . "\n\n\n";
                echo print_r($data['id'], true) . "\n\n\n";
                echo print_r($data['status'], true) . "\n\n\n";
            }

            // The payment platform returns a code, corresponding to the state of the operation. Here, the "success" code is 0000. It means that any other code correspond to a payment failure > process aborted
            elseif (!$data['paid']) {
                // Here we display debug information which will be catched by HikaShop and stored in the payment log file available in the configuration's Files section.
                if ($this->payment_params->debug)
                    echo 'payment ' . $vars['status'] . "\n\n\n";

                // This function modifies the order with the id $order_id, to attribute it the status invalid_status.
                $this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);

                //To redirect the user, if needed. Here the redirection is useless : we are on server side (and not user side, so the redirect won't work), and the cancel url has been set on the payment platform merchant account
                // $this->app->redirect($cancel_url);
                return false;
            }
            //If everything's OK, the payment has been done. Order is validated -> success
            elseif ($data['paid']) {
                $this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);

                // $this->app->redirect($return_url);
                return true;
            }
        } else {
            echo 'gila';
            return false;
        }
    }

    function onPaymentConfigurationSave(&$element) {
        if (empty($element->payment_params->currency))
            $element->payment_params->currency = $this->accepted_currencies[0];
        return true;
    }

}
