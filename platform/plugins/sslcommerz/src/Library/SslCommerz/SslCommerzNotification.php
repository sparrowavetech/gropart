<?php

namespace Botble\SslCommerz\Library\SslCommerz;

use Illuminate\Support\Arr;
use stdClass;

class SslCommerzNotification extends AbstractSslCommerz
{
    protected array $data = [];

    protected array $config = [];

    protected string $successUrl;

    protected string $cancelUrl;

    protected string $failedUrl;

    protected string $error;

    protected stdClass $sslc_data;

    public function __construct()
    {
        $this->config = config('plugins.sslcommerz.sslcommerz');

        $this->setStoreId($this->config['apiCredentials']['store_id']);
        $this->setStorePassword($this->config['apiCredentials']['store_password']);
    }

    public function orderValidate($postData, $transactionId = '', $amount = 0, $currency = 'BDT')
    {
        if ($postData == '' && $transactionId == '' && ! is_array($postData)) {
            $this->error = 'Please provide valid transaction ID and post request data';

            return $this->error;
        }

        return $this->validate($transactionId, $amount, $currency, $postData);
    }

    /**
     * VALIDATE SSLCOMMERZ TRANSACTION
     *
     * @param $merchant_trans_id
     * @param $merchant_trans_amount
     * @param $merchant_trans_currency
     * @param $postData
     * @return bool
     */
    protected function validate($merchant_trans_id, $merchant_trans_amount, $merchant_trans_currency, $postData): bool
    {
        // MERCHANT SYSTEM INFO
        if ($merchant_trans_id != '' && $merchant_trans_amount != 0) {
            // CALL THE FUNCTION TO CHECK THE RESULT
            $postData['store_id'] = $this->getStoreId();
            $postData['store_pass'] = $this->getStorePassword();

            if ($this->verifyHash($postData, $this->getStorePassword())) {
                $val_id = urlencode($postData['val_id']);
                $store_id = urlencode($this->getStoreId());
                $storePassword = urlencode($this->getStorePassword());
                $requested_url = ($this->config['apiDomain'] . $this->config['apiUrl']['order_validate'] . '?val_id=' . $val_id . '&store_id=' . $store_id . '&store_passwd=' . $storePassword . '&v=1&format=json');

                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $requested_url);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

                if ($this->config['connect_from_localhost']) {
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
                } else {
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 2);
                }

                $result = curl_exec($handle);

                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                if ($code == 200 && ! (curl_errno($handle))) {
                    // TO CONVERT AS ARRAY
                    // $result = json_decode($result, true);
                    // $status = $result['status'];

                    // TO CONVERT AS OBJECT
                    $result = json_decode($result);
                    $this->sslc_data = $result;

                    // TRANSACTION INFO
                    $status = $result->status;
                    $tran_id = $result->tran_id;
                    $amount = $result->amount;
                    $currency_type = $result->currency_type;
                    $currency_amount = $result->currency_amount;

                    // GIVE SERVICE
                    if ($status == 'VALID' || $status == 'VALIDATED') {
                        if ($merchant_trans_currency == 'BDT') {
                            if (trim($merchant_trans_id) == trim($tran_id) && (abs($merchant_trans_amount - $amount) < 1) && trim($merchant_trans_currency) == trim('BDT')) {
                                return true;
                            } else {
                                // DATA TEMPERED
                                $this->error = 'Data has been tempered';

                                return false;
                            }
                        } else {
                            //echo "trim($merchant_trans_id) == trim($tran_id) && ( abs($merchant_trans_amount-$currency_amount) < 1 ) && trim($merchant_trans_currency)==trim($currency_type)";
                            if (trim($merchant_trans_id) == trim($tran_id) && (abs($merchant_trans_amount - $currency_amount) < 1) && trim($merchant_trans_currency) == trim($currency_type)) {
                                return true;
                            } else {
                                // DATA TEMPERED
                                $this->error = 'Data has been tempered';

                                return false;
                            }
                        }
                    } else {
                        // FAILED TRANSACTION
                        $this->error = 'Failed Transaction';

                        return false;
                    }
                } else {
                    // Failed to connect with SSLCOMMERZ
                    $this->error = 'Failed to connect with SSLCOMMERZ';

                    return false;
                }
            } else {
                // Hash validation failed
                $this->error = 'Hash validation failed';

                return false;
            }
        } else {
            // INVALID DATA
            $this->error = 'Invalid data';

            return false;
        }
    }

    /**
     * FUNCTION TO CHECK HASH VALUE
     *
     * @param $postData
     * @param string|null $storePassword
     * @return bool
     */
    protected function verifyHash($postData, ?string $storePassword = ''): bool
    {
        if (isset($postData) && isset($postData['verify_sign']) && isset($postData['verify_key'])) {
            // NEW ARRAY DECLARED TO TAKE VALUE OF ALL POST
            $pre_define_key = explode(',', $postData['verify_key']);

            $new_data = [];
            foreach ($pre_define_key as $value) {
                $new_data[$value] = ($postData[$value]);
            }
            // ADD MD5 OF STORE PASSWORD
            $new_data['store_passwd'] = md5($storePassword);

            // SORT THE KEY AS BEFORE
            ksort($new_data);

            $hash_string = '';
            foreach ($new_data as $key => $value) {
                $hash_string .= $key . '=' . ($value) . '&';
            }
            $hash_string = rtrim($hash_string, '&');

            if (md5($hash_string) == $postData['verify_sign']) {
                return true;
            } else {
                $this->error = 'Verification signature not matched';

                return false;
            }
        } else {
            $this->error = 'Required data mission. ex: verify_key, verify_sign';

            return false;
        }
    }

    /**
     * @param array $requestData
     * @param string $type
     * @param string $pattern
     * @return array
     */
    public function makePayment(array $requestData, $type = 'checkout', $pattern = 'json')
    {
        if (empty($requestData)) {
            return [
                'error' => true,
                'message' => 'Please provide a valid information list about transaction with transaction id, amount, success url, fail url, cancel url, store id and pass at least',
            ];
        }

        $header = [];

        $this->setApiUrl($this->config['apiDomain'] . $this->config['apiUrl']['make_payment']);

        // Set the required/additional params
        $this->setParams($requestData);

        // Set the authentication information
        $this->setAuthenticationInfo();

        // Now, call the Gateway API
        $response = $this->callToApi($this->data, $header, $this->config['connect_from_localhost']);

        // Here we will define the response pattern
        $formattedResponse = $this->formatResponse($response, $type, $pattern);

        $data = [
            'error' => false,
            'message' => null,
        ];

        if ($type == 'hosted') {
            if (isset($formattedResponse['GatewayPageURL']) && $formattedResponse['GatewayPageURL'] != '') {
                $this->redirect($formattedResponse['GatewayPageURL']);
            } else {
                $data['error'] = true;
                $data['message'] = 'No redirect URL found!';

                if (Arr::get($formattedResponse, 'status') == 'FAILED' && Arr::get($formattedResponse, 'failedreason')) {
                    $data['message'] = Arr::get($formattedResponse, 'failedreason');
                }

                return $data;
            }
        }

        return $data;
    }

    /**
     * @param array $requestData
     */
    public function setParams($requestData)
    {
        //  Integration Required Parameters
        $this->setRequiredInfo($requestData);

        //  Customer Information
        $this->setCustomerInfo($requestData);

        //  Shipment Information
        $this->setShipmentInfo($requestData);

        //  Product Information
        $this->setProductInfo($requestData);

        //  Customized or Additional Parameters
        $this->setAdditionalInfo($requestData);
    }

    /**
     * @param array $info
     * @return array
     */
    public function setRequiredInfo(array $info)
    {
        // decimal (10,2) Mandatory - The amount which will process by SslCommerz. It shall be decimal value (10,2). Example : 55.40. The transaction amount must be from 10.00 BDT to 500000.00 BDT
        $this->data['total_amount'] = $info['total_amount'];
        // string (3) Mandatory - The currency type must be mentioned. It shall be three characters. Example : BDT, USD, EUR, SGD, INR, MYR, etc. If the transaction currency is not BDT, then it will be converted to BDT based on the current convert rate. Example : 1 USD = 82.22 BDT.
        $this->data['currency'] = $info['currency'];
        // string (30) Mandatory - Unique transaction ID to identify your order in both your end and SslCommerz
        $this->data['tran_id'] = $info['tran_id'];
        // string (50) Mandatory - Mention the product category. It is a open field. Example - clothing,shoes,watches,gift,healthcare, jewellery,top up,toys,baby care,pants,laptop,donation,etc
        $this->data['product_category'] = $info['product_category'];

        // Set the SUCCESS, FAIL, CANCEL Redirect URL before setting the other parameters
        $this->setSuccessUrl();
        $this->setFailedUrl();
        $this->setCancelUrl();

        // string (255) Mandatory - It is the callback URL of your website where user will redirect after successful payment (Length: 255)
        $this->data['success_url'] = $this->getSuccessUrl();
        // string (255) Mandatory - It is the callback URL of your website where user will redirect after any failure occure during payment (Length: 255)
        $this->data['fail_url'] = $this->getFailedUrl();
        // string (255) Mandatory - It is the callback URL of your website where user will redirect if user canceled the transaction (Length: 255)
        $this->data['cancel_url'] = $this->getCancelUrl();

        /*
         * IPN is very important feature to integrate with your site(s).
         * Some transaction could be pending or customer lost his/her session, in such cases back-end IPN plays a very important role to update your backend office.
         *
         * Type: string (255)
         * Important! Not mandatory, however better to use to avoid missing any payment notification - It is the Instant Payment Notification (IPN) URL of your website where SSLCOMMERZ will send the transaction's status (Length: 255).
         * The data will be communicated as SSLCOMMERZ Server to your Server. So, customer session will not work.
         * */
        $this->data['ipn_url'] = (isset($info['ipn_url'])) ? $info['ipn_url'] : null;

        /*
         * Type: string (30)
         * Do not Use! If you do not customize the gateway list - You can control to display the gateway list at SslCommerz gateway selection page by providing this parameters.
         * Multi Card:
            brac_visa = BRAC VISA
            dbbl_visa = Dutch Bangla VISA
            city_visa = City Bank Visa
            ebl_visa = EBL Visa
            sbl_visa = Southeast Bank Visa
            brac_master = BRAC MASTER
            dbbl_master = MASTER Dutch-Bangla
            city_master = City Master Card
            ebl_master = EBL Master Card
            sbl_master = Southeast Bank Master Card
            city_amex = City Bank AMEX
            qcash = QCash
            dbbl_nexus = DBBL Nexus
            bankasia = Bank Asia IB
            abbank = AB Bank IB
            ibbl = IBBL IB and Mobile Banking
            mtbl = Mutual Trust Bank IB
            bkash = Bkash Mobile Banking
            dbblmobilebanking = DBBL Mobile Banking
            city = City Touch IB
            upay = Upay
            tapnpay = Tap N Pay Gateway
         * GROUP GATEWAY
            internetbank = For all internet banking
            mobilebank = For all mobile banking
            othercard = For all cards except visa,master and amex
            visacard = For all visa
            mastercard = For All Master card
            amexcard = For Amex Card
         * */
        $this->data['multi_card_name'] = (isset($info['multi_card_name'])) ? $info['multi_card_name'] : null;

        /*
         * Type: string (255)
         * Do not Use! If you do not control on transaction - You can provide the BIN of card to allow the transaction must be completed by this BIN. You can declare by coma ',' separate of these BIN.
         * Example: 371598,371599,376947,376948,376949
         * */
        $this->data['allowed_bin'] = (isset($info['allowed_bin'])) ? $info['allowed_bin'] : null;

        // Parameters to Handle EMI Transaction
        // integer (1) Mandatory - This is mandatory if transaction is EMI enabled and Value must be 1/0. Here, 1 means customer will get EMI facility for this transaction
        $this->data['emi_option'] = (isset($info['emi_option'])) ? $info['emi_option'] : null;
        // integer (2) Max instalment Option, Here customer will get 3,6, 9 instalment at gateway page
        $this->data['emi_max_inst_option'] = (isset($info['emi_max_inst_option'])) ? $info['emi_max_inst_option'] : null;
        // integer (2) Customer has selected from your Site, So no instalment option will be displayed at gateway page
        $this->data['emi_selected_inst'] = (isset($info['emi_selected_inst'])) ? $info['emi_selected_inst'] : null;
        $this->data['emi_allow_only'] = (isset($info['emi_allow_only'])) ? $info['emi_allow_only'] : 0;

        return $this->data;
    }

    /**
     * @return string
     */
    protected function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    protected function setSuccessUrl()
    {
        $this->successUrl = route('public.index') . '/' . ltrim($this->config['success_url'], '/');
    }

    /**
     * @return string
     */
    protected function getFailedUrl(): string
    {
        return $this->failedUrl;
    }

    protected function setFailedUrl()
    {
        $this->failedUrl = route('public.index') . '/' . ltrim($this->config['failed_url'], '/');
    }

    /**
     * @return string
     */
    protected function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    protected function setCancelUrl()
    {
        $this->cancelUrl = route('public.index') . '/' . ltrim($this->config['cancel_url'], '/');
    }

    /**
     * @param array $info
     * @return array
     */
    public function setCustomerInfo(array $info): array
    {
        // string (50) Mandatory - Your customer name to address the customer in payment receipt email
        $this->data['cus_name'] = $info['cus_name'];
        // string (50) Mandatory - Valid email address of your customer to send payment receipt from SslCommerz end
        $this->data['cus_email'] = $info['cus_email'];
        // string (50) Mandatory - Address of your customer. Not mandatory but useful if provided
        $this->data['cus_add1'] = $info['cus_add1'];
        // string (50) Address line 2 of your customer. Not mandatory but useful if provided
        $this->data['cus_add2'] = $info['cus_add2'];
        // string (50) Mandatory - City of your customer. Not mandatory but useful if provided
        $this->data['cus_city'] = $info['cus_city'];
        // string (50) State of your customer. Not mandatory but useful if provided
        $this->data['cus_state'] = (isset($info['cus_state'])) ? $info['cus_state'] : null;
        // string (30) Mandatory - Postcode of your customer. Not mandatory but useful if provided
        $this->data['cus_postcode'] = $info['cus_postcode'];
        // string (50) Mandatory - Country of your customer. Not mandatory but useful if provided
        $this->data['cus_country'] = $info['cus_country'];
        // string (20) Mandatory - The phone/mobile number of your customer to contact if any issue arises
        $this->data['cus_phone'] = $info['cus_phone'];
        // string (20) Fax number of your customer. Not mandatory but useful if provided
        $this->data['cus_fax'] = (isset($info['cus_fax'])) ? $info['cus_fax'] : null;

        return $this->data;
    }

    /**
     * @param array $info
     * @return array
     */
    public function setShipmentInfo(array $info): array
    {
        // string (50) Mandatory - Shipping method of the order. Example: YES or NO or Courier
        $this->data['shipping_method'] = $info['shipping_method'];
        // integer (1) Mandatory - No of product will be shipped. Example: 1 or 2 or etc
        $this->data['num_of_item'] = isset($info['num_of_item']) ? $info['num_of_item'] : 1;
        // string (50) Mandatory, if shipping_method is YES - Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_name'] = $info['ship_name'];
        // string (50) Mandatory, if shipping_method is YES - Additional Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_add1'] = $info['ship_add1'];
        // string (50) Additional Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_add2'] = (isset($info['ship_add2'])) ? $info['ship_add2'] : null;
        // string (50) Mandatory, if shipping_method is YES - Shipping city of your order. Not mandatory but useful if provided
        $this->data['ship_city'] = $info['ship_city'];
        // string (50) Shipping state of your order. Not mandatory but useful if provided
        $this->data['ship_state'] = (isset($info['ship_state'])) ? $info['ship_state'] : null;
        // string (50) Mandatory, if shipping_method is YES - Shipping postcode of your order. Not mandatory but useful if provided
        $this->data['ship_postcode'] = (isset($info['ship_postcode'])) ? $info['ship_postcode'] : null;
        // string (50) Mandatory, if shipping_method is YES - Shipping country of your order. Not mandatory but useful if provided
        $this->data['ship_country'] = (isset($info['ship_country'])) ? $info['ship_country'] : null;

        return $this->data;
    }

    /**
     * @param array $info
     * @return array
     */
    public function setProductInfo(array $info): array
    {
        // String (256) Mandatory - Mention the product name briefly. Mention the product name by coma separate. Example: Computer,Speaker
        $this->data['product_name'] = (isset($info['product_name'])) ? $info['product_name'] : '';
        // String (100) Mandatory - Mention the product category. Example: Electronic or topup or bus ticket or air ticket
        $this->data['product_category'] = (isset($info['product_category'])) ? $info['product_category'] : '';

        /*
         * String (100)
         * Mandatory - Mention goods vertical. It is very much necessary for online transactions to avoid chargeback.
         * Please use the below keys :
            1) general
            2) physical-goods
            3) non-physical-goods
            4) airline-tickets
            5) travel-vertical
            6) telecom-vertical
        */
        $this->data['product_profile'] = (isset($info['product_profile'])) ? $info['product_profile'] : '';

        // string (30) Mandatory, if product_profile is airline-tickets - Provide the remaining time of departure of flight till at the time of purchasing the ticket. Example: 12 hrs or 36 hrs
        $this->data['hours_till_departure'] = (isset($info['hours_till_departure'])) ? $info['hours_till_departure'] : null;
        // string (30) Mandatory, if product_profile is airline-tickets - Provide the flight type. Example: Oneway or Return or Multistop
        $this->data['flight_type'] = (isset($info['flight_type'])) ? $info['flight_type'] : null;
        // string (50) Mandatory, if product_profile is airline-tickets - Provide the PNR.
        $this->data['pnr'] = (isset($info['pnr'])) ? $info['pnr'] : null;
        // string (256) - Mandatory, if product_profile is airline-tickets - Provide the journey route. Example: DAC-CGP or DAC-CGP CGP-DAC
        $this->data['journey_from_to'] = (isset($info['journey_from_to'])) ? $info['journey_from_to'] : null;
        // string (20) Mandatory, if product_profile is airline-tickets - No/Yes. Whether the ticket has been taken from third party booking system.
        $this->data['third_party_booking'] = (isset($info['third_party_booking'])) ? $info['third_party_booking'] : null;
        // string (256) Mandatory, if product_profile is travel-vertical - Please provide the hotel name. Example: Sheraton
        $this->data['hotel_name'] = (isset($info['hotel_name'])) ? $info['hotel_name'] : null;
        // string (30) Mandatory, if product_profile is travel-vertical - How long stay in hotel. Example: 2 days
        $this->data['length_of_stay'] = (isset($info['length_of_stay'])) ? $info['length_of_stay'] : null;
        // string (30) Mandatory, if product_profile is travel-vertical - Checking hours for the hotel room. Example: 24 hrs
        $this->data['check_in_time'] = (isset($info['check_in_time'])) ? $info['check_in_time'] : null;
        // string (50) Mandatory, if product_profile is travel-vertical - Location of the hotel. Example: Dhaka
        $this->data['hotel_city'] = (isset($info['hotel_city'])) ? $info['hotel_city'] : null;
        // string (30) Mandatory, if product_profile is telecom-vertical - For mobile or any recharge, this information is necessary. Example: Prepaid or Postpaid
        $this->data['product_type'] = (isset($info['product_type'])) ? $info['product_type'] : null;
        // string (150) Mandatory, if product_profile is telecom-vertical - Provide the mobile number which will be recharged. Example: 8801700000000 or 8801700000000,8801900000000
        $this->data['topup_number'] = (isset($info['topup_number'])) ? $info['topup_number'] : null;
        // string (30) Mandatory, if product_profile is telecom-vertical - Provide the country name in where the service is given. Example: Bangladesh
        $this->data['country_topup'] = (isset($info['country_topup'])) ? $info['country_topup'] : null;

        /*
         * Type: JSON
         * JSON data with two elements. product : Max 255 characters, quantity : Quantity in numeric value and amount : Decimal (12,2)
         * Example:
           [{"product":"DHK TO BRS AC A1","quantity":"1","amount":"200.00"},{"product":"DHK TO BRS AC A2","quantity":"1","amount":"200.00"},{"product":"DHK TO BRS AC A3","quantity":"1","amount":"200.00"},{"product":"DHK TO BRS AC A4","quantity":"2","amount":"200.00"}]
         * */
        $this->data['cart'] = (isset($info['cart'])) ? $info['cart'] : null;
        // decimal (10,2) Product price which will be displayed in your merchant panel and will help you to reconcile the transaction. It shall be decimal value (10,2). Example : 50.40
        $this->data['product_amount'] = (isset($info['product_amount'])) ? $info['product_amount'] : null;
        // decimal (10,2) The VAT included on the product price which will be displayed in your merchant panel and will help you to reconcile the transaction. It shall be decimal value (10,2). Example : 4.00
        $this->data['vat'] = (isset($info['vat'])) ? $info['vat'] : null;
        // decimal (10,2) Discount given on the invoice which will be displayed in your merchant panel and will help you to reconcile the transaction. It shall be decimal value (10,2). Example : 2.00
        $this->data['discount_amount'] = (isset($info['discount_amount'])) ? $info['discount_amount'] : null;
        // decimal (10,2) Any convenience fee imposed on the invoice which will be displayed in your merchant panel and will help you to reconcile the transaction. It shall be decimal value (10,2). Example : 3.00
        $this->data['convenience_fee'] = (isset($info['convenience_fee'])) ? $info['convenience_fee'] : null;

        return $this->data;
    }

    /**
     * @param array $info
     * @return array
     */
    public function setAdditionalInfo(array $info): array
    {
        // value_a [ string (255) - Extra parameter to pass your metadata if it is needed. Not mandatory]
        $this->data['value_a'] = (isset($info['value_a'])) ? $info['value_a'] : null;
        // value_b [ string (255) - Extra parameter to pass your metadata if it is needed. Not mandatory]
        $this->data['value_b'] = (isset($info['value_b'])) ? $info['value_b'] : null;
        // value_c [ string (255) - Extra parameter to pass your metadata if it is needed. Not mandatory]
        $this->data['value_c'] = (isset($info['value_c'])) ? $info['value_c'] : null;
        // value_d [ string (255) - Extra parameter to pass your metadata if it is needed. Not mandatory]
        $this->data['value_d'] = (isset($info['value_d'])) ? $info['value_d'] : null;

        return $this->data;
    }

    /**
     * @return array
     */
    public function setAuthenticationInfo(): array
    {
        $this->data['store_id'] = $this->getStoreId();
        $this->data['store_passwd'] = $this->getStorePassword();

        return $this->data;
    }
}
