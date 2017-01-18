# Billplz for Hikashop 2.6
Integrate Billplz in Hikashop 2.6

# Requirement

  * Tested with Joomla 3.6
  * Tested with Hikashop Starter 2.6.4
  * Compatible with PHP 7.0 and 7.1
  * Billplz organization account

# Installation Instruction

  * Download this repository: https://codeload.github.com/wzul/Billplz-for-Hikashop-2.6/zip/master
  * Go to Joomla Administration >> Extension >> Manage >> Install
  * Upload package file >> Install
  * Enable the plugin >> Extension >> Plugin >> Hikashop Billplz Payment Plugin
  * Go to Hikashop Option >> System >> Payment Method >> New >> Billplz
  * Set the particular details (API Secret Key, Collection ID & etc)
  * Save & Close
  
# Specific Configuration

  * **API Secret Key** : Get the API Key at Billplz Setting Page
  * **Collection ID** : Get the Collection ID at Billplz Billing Page
  * Enable Email & SMS Notification : Yes to send notification to customer on Bills Creation. (**Default=No**)
  * Verification Type : If you having problem with Payment Status not updated after payment, choose **Return**. (**Default=Callback**)
  * **Allow payment notifications from billplz** : **Yes (Mandatory)**
  * Mode : Only change to staging if you are register Billplz account at billplz-staging.herokuapp.com. Otherwise, leave it as Production
  * Debug : No
  * Invalid status : Cancelled
  * Verified status : Confirmed
  
# Custom Image

  * Upload **logo-billplz.png** file to **/media/com_hikashop/images/payment/**
  * Set it at Generic Configuration
  
# Donation

  * Support this project by giving donation to me:
  * www.wanzul.net/donate
  
