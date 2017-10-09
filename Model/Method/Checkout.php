<?php
/*
 * Copyright (C) 2017 primathon
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      primathon
 * @copyright   2017 primathon
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

namespace Primathonpay\MWarrior\Model\Method;
use mWarrior\Settings;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use \Magento\Framework\Message\ManagerInterface;

/**
 * Checkout Payment Method Model Class
 * Class Checkout
 * @package Primathonpay\MWarrior\Model\Method
 */
class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{
    use \Primathonpay\MWarrior\Model\Traits\OnlinePaymentMethod;


    const CODE = 'mwarrior_checkout';
    /**
     * Checkout Method Code
     */
    protected $_code = self::CODE;

    protected $_canOrder                    = true;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canCancelInvoice            = true;
    protected $_canVoid                     = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canAuthorize                = true;
    protected $_isInitializeNeeded          = false;
    protected $_canReviewPayment            = true;


    /**
     * Get Instance of the Magento Code Logger
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Checkout constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\App\Action\Context $actionContext
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Primathonpay\MWarrior\Helper\Data $moduleHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger  $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Primathonpay\MWarrior\Helper\Data $moduleHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_actionContext = $actionContext;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_moduleHelper = $moduleHelper;
        $this->_productFactory = $productFactory;
        $this->_configHelper =
            $this->getModuleHelper()->getMethodConfig(
                $this->getCode()
            );
    }

    /**
     * Get Default Payment Action On Payment Complete Action
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
    }

    /**
     * Get Available Checkout Transaction Types
     * @return array
     */
    public function getCheckoutTransactionTypes()
    {
        $selected_types = $this->getConfigHelper()->getTransactionTypes();

        return $selected_types;
    }

    public function canReviewPayment()
    {
        \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
        return $this->_canReviewPayment;

    }
    
    public function acceptPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        $isProcessManually = $this->getConfigData('process_manually');
        if($isProcessManually && (!($isProcessManually === 1))){
            $amount = 0.0;
            return $this->processTransaction($payment, $amount);
        }else{
            return true;
        }
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $info = $this->getInfoInstance();

        if ($this->getInfoInstanceHasCcDetails($info)) {
            return $this;
        }

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }
        

        /** @var DataObject $info */
        $info->addData(
            [
                'cc_type'           => $additionalData->getCcType(),
                'cc_owner'          => $additionalData->getCcOwner(),
                'cc_last_4'         => substr($additionalData->getCcNumber(), -4),
                'cc_number'         => $additionalData->getCcNumber(),
                'cc_number_enc'     => $additionalData->getCcNumberEnc(),
                'token'             => $additionalData->getToken(),
                'cc_exp_month'      => $additionalData->getCcExpMonth(),
                'cc_exp_year'       => substr($additionalData->getCcExpYear(), -2),
                'cc_ss_issue'       => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_trans_id'       => $additionalData->getCcTransId(),
                'last_trans_id'     => $additionalData->getLastTransId(),
                'cc_ss_start_year'  => $additionalData->getCcSsStartYear()
            ]
        );

        return $this;
    }

    protected function getInfoInstanceHasCcDetails(\Magento\Payment\Model\InfoInterface $info)
    {
        return
            !empty($info->getCcNumber()) &&
            !empty($info->getCcCid()) &&
            !empty($info->getCcExpMonth()) &&
            !empty($info->getCcExpYear());
    }

    public function checkout($data)
    {
        $transaction = new \primathonpay\AddCard();  // mwarrior/Addcard;

        $transaction->setMerchantUUID($data['merchantUUID']);
        $transaction->setApiKey($data['apiKey']);
        $transaction->setCcOwner($data['cardName']);
        $transaction->setCardNumber($data['cardNumber']);
        $transaction->setCardExpMonth($data['cardExpiryMonth']);
        $transaction->setCardExpYear($data['cardExpiryYear']);

        $payment_methods = $this->getConfigPaymentAction();
        $helper = $this->getModuleHelper();

        $transaction->submit();
        $response = $transaction->gatewayResponse();

        return $this->_parseResponse($response);

    }

    public function _parseResponse($result){

        //Check for any result at all
        if (!$result)
        {
            array('status' => false, 'error' => "Could not successfully communicate with Payment Processor.  Check the URL.", 'result' => $result);
        }

        // Check for CURL errors
        if (isset($result['err']) && strlen($result['err']))
        {
            return array('status' => false, 'error' => "Could not successfully communicate with Payment Processor ({$result['err']}).", 'result' => $result);
        }
        
        // Make sure the API returned something
        if (!isset($result['data']) || strlen($result['data']) < 1)
        {
            return array('status' => false, 'error' => "Payment Processor did not return a valid response.", 'result' => $result);
        }

        // Parse the XML
        $xml = simplexml_load_string($result['data']);
        // Convert the result from a SimpleXMLObject into an array
        $xml = (array) $xml;
        
        // Check for a valid response code
        if (!isset($xml['responseCode']) || strlen($xml['responseCode']) < 1)
        {
            return array('status' => false, 'error' => "Payment Processor did not return a valid response.", 'result' => $result, 'responseData' => $xml);
        }

        // Validate the response - the only successful code is 0
        $status = ((int) $xml['responseCode'] === 0) ? true : false;

        // Set an error message if the transaction failed
        if ($status === false)
        {
            return array('status' => false, 'error' => "Transaction Declined: {$xml['responseMessage']}.", 'result' => $result, 'responseData' => $xml);
        }
        
        // Make the response a little more useable - there are a few fields that may or may not be present
        // depending on the different transaction types, so this handles them all generically.
        $response = array (
            'status' => $status,
            'responseData' => $xml,
            'transactionID' => (isset($xml['transactionID']) ? $xml['transactionID'] : null)
        );

        return $response;
    }

    /**
     * Order Payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();

        $this->transactionID = '';

        $orderId = ltrim(
            $order->getIncrementId(),
            '0'
        );

        $cardHolderName = null;

        $billing = $order->getBillingAddress();
        if (empty($billing)) {
            throw new \Exception(__('Billing address is empty.'));
        }
        $ccExpMonth = $payment->getCcExpMonth();
        
        if(strlen((string)$ccExpMonth) === 1){
            $ccExpMonth = sprintf("%02d",$payment->getCcExpMonth());
        }
        else{
            $ccExpMonth = $payment->getCcExpMonth();
        }

        if (!empty($payment->getCcOwner())) {
          $cardHolderName = $payment->getCcOwner();
        } else {
          $cardHolderName = $billing->getFirstname();
        }

        $data = [
            'method' => $helper::ADDCARD,
            'merchantUUID' => $this->getConfigData('merchant_id'),
            'apiKey' => $this->getConfigData('api_key'),
            'cardName'=>  $cardHolderName,
            'cardNumber' => $payment->getCcNumber(),
            'cardExpiryMonth' => $ccExpMonth,
            'cardExpiryYear' => $payment->getCcExpYear()
        ];

        //        $this->getConfigHelper()->initGatewayClient();

        try {

            $responseObject = $this->checkout($data);

             $this->setMWarriorResponse(
                $responseObject
            );

            $mwarrior_response = $this->getModuleHelper()->getArrayFromGatewayResponse(
                $this->getMWarriorResponse()
            );
		
            $payment
            ->setCcNumberEnc(
                $responseObject['responseData']['cardNumber']
            )
            ->setToken(
                $responseObject['responseData']['cardID']
            )
            ->setIsTransactionClosed(
                false
            )
            ->setIsTransactionPending(
                true
            )
            ->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $mwarrior_response
            );

            return $this;
        } catch (\Exception $e) { $this->getLogger()->info("Add card method error" . $e);
            $this->getLogger()->error(
                $e->getMessage()
            );

            $this->getCheckoutSession()->setPrimathonpayLastCheckoutError(
                $e->getMessage()
            );

            $this->getModuleHelper()->maskException($e);
        }
    }


    /**
     * Higher level implementation of Merchant Warrior POST API request - payment 
     *
     * @return
     */
    public function processTransaction(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
        $order = $payment->getOrder();
        $helper = $this->getModuleHelper();
        
        $billing = $order->getBillingAddress();

        $cardHolderName = null;

        if (!empty($payment->getCcOwner())) {
          $cardHolderName = $payment->getCcOwner();
        } else {
          $cardHolderName = $billing->getFirstname();
        }

        $product = $this->_registry->registry('current_product');
        // $name = str_replace('&', '&amp;', trim($billing->getFirstname())) . str_replace('&', '&amp;', trim($billing->getLastname()));
        
        $postData = [
            'method' => 'processCard',
            'merchantUUID' => $this->getConfigData('merchant_id'),
            'apiKey' => $this->getConfigData('api_key'),
            'transactionAmount' => sprintf("%.2f",$order->getGrandTotal()),
            'transactionCurrency' => 'AUD',
            'transactionProduct' => '$FOH-00100503',
            'customerName' => $cardHolderName,
            'customerCountry' => $billing->getCountryId(),
            'customerState' => $billing->getRegion(),
            'customerCity' => $billing->getCity(),
            'customerAddress' => $billing->getStreet()[0],
            'customerPostCode' => $billing->getPostcode(),
            'customerPhone' => $billing->getTelephone(),
            'customerEmail' => $billing->getEmail(),
            'cardId' => $payment->getToken()
        ];
        array_push($postData,['hash' => $this->getHash($postData)]);

     //         $this->getConfigHelper()->initGatewayClient();
        
        try {

            $responseObject = $this->processCard($postData);

            $this->setMWarriorResponse(
                $responseObject
            );

            $mwarrior_response = $this->getModuleHelper()->getArrayFromGatewayResponse(
                $this->getMWarriorResponse()
            );
            
            if($mwarrior_response === true){
                $payment
                    ->setLastTransId(
                            $responseObject['transactionID']
                        )
                    ->setCcTransId(
                        $responseObject['transactionID']
                    )
                    ->setIsTransactionClosed(
                        false
                    )
                    ->setIsTransactionPending(
                        false
                    )
                    ->setPaymentTransactionAdditionalInfo(
                        $mwarrior_response
                    );
                return true;
            }else{
                $message = $responseObject['error'];
                $this->getLogger()->error($message);
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
                return false;
            }
        } catch (\Exception $e) {
            $e.$message = $responseObject['error'];
            $logInfo =
                'Transaction Direct Payment' .
                ' for order #' . $order->getIncrementId() .
                ' failed with message "' . $e->getMessage() . '"';

            $this->getLogger()->error($logInfo);

            $this->getModuleHelper()->maskException($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
            return false;
        }

    }


     public function processCard($data)
    {
        $transaction = new \primathonpay\Payment;

        $transaction->setMerchantUUID($data['merchantUUID']);
        $transaction->setApiKey($data['apiKey']);
        $transaction->setGrandTotal($data['transactionAmount']);
        $transaction->setBaseCurrency($data['transactionCurrency']);
        $transaction->setTransactionProduct($data['transactionProduct']);
        $transaction->setCustomerName($data['customerName']);
        $transaction->setCountryId($data['customerCountry']);
        $transaction->setRegion($data['customerState']);
        $transaction->setCity($data['customerCity']);
        $transaction->setStreet($data['customerAddress']);
        $transaction->setPostCode($data['customerPostCode']);
        $transaction->setTelephone($data['customerPhone']);
        $transaction->setEmail($data['customerEmail']);
        $transaction->setToken($data['cardId']);
        $transaction->setHash($data[0]['hash']);

        $payment_methods = $this->getConfigPaymentAction();
        $helper = $this->getModuleHelper();

        $transaction->submit();
        $response = $transaction->gatewayResponse();

        return $this->_parseResponse($response);

    }

    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $this->getLogger()->debug('Refund transaction for order #' . $order->getIncrementId());

        $captureTransaction = $this->getModuleHelper()->lookUpCaptureTransaction(
            $payment
        );

        if (!isset($captureTransaction)) {
            $errorMessage = __('Refund transaction for order # %1 cannot be finished (No Capture Transaction exists)',
                $order->getIncrementId()
            );

            $this->getLogger()->error(
                $errorMessage
            );

            $this->getMessageManager()->addError($errorMessage);

            $this->getModuleHelper()->throwWebApiException(
                $errorMessage
            );
        }

        try {
            $this->doRefund($payment, $amount, $captureTransaction);
        } catch (\Exception $e) {
            $this->getLogger()->error(
                $e->getMessage()
            );

            $this->getMessageManager()->addError(
                $e->getMessage()
            );

            $this->getModuleHelper()->maskException($e);
        }

        return $this;
    }

    /**
     * Payment Cancel
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Void Payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        /** @var \Magento\Sales\Model\Order $order */

        $order = $payment->getOrder();

        $this->getLogger()->debug('Void transaction for order #' . $order->getIncrementId());

        $referenceTransaction = $this->getModuleHelper()->lookUpVoidReferenceTransaction(
            $payment
        );

        if ($referenceTransaction->getTxnType() == \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH) {
            $authTransaction = $referenceTransaction;
        } else {
            $authTransaction = $this->getModuleHelper()->lookUpAuthorizationTransaction(
                $payment
            );
        }

        if (!isset($authTransaction) || !isset($referenceTransaction)) {
            $errorMessage = __('Void transaction for order # %1 cannot be finished (No Authorize / Capture Transaction exists)',
                            $order->getIncrementId()
            );

            $this->getLogger()->error($errorMessage);
            $this->getModuleHelper()->throwWebApiException($errorMessage);
        }

        try {
            $this->doVoid($payment, $authTransaction, $referenceTransaction);
        } catch (\Exception $e) {
            $this->getLogger()->error(
                $e->getMessage()
            );
            $this->getModuleHelper()->maskException($e);
        }

        return $this;
    }

    /**
     * Determines method's availability based on config data and quote amount
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) &&
            $this->getConfigHelper()->isMethodAvailable();
    }

    /**
     * Checks base currency against the allowed currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getModuleHelper()->isCurrencyAllowed(
            $this->getCode(),
            $currencyCode
        );
    }

    /**
     * Calculate verification hash for Merchant Warrior POST API
     *
	 * @param array $postData
     * @return string
     */
    public function getHash(array $postData = array()) 
	{
		// Check the amount param
        if (!isset($postData['transactionAmount']) || !strlen($postData['transactionAmount'])) {
             $this->getLogger()->error("Missing or blank amount field in post array.");
        }

        // Check the currency param
        if (!isset($postData['transactionCurrency']) || !strlen($postData['transactionCurrency'])) {
             $this->getLogger()->error("Missing or blank currency field in post array.");
        }
        $this->getLogger()->info("apipass" . $this->getConfigData('api_passphrase'));
        // Generate & return the hash
        $passphrase = $this->getConfigData('api_passphrase');
        $merchantUUID = $this->getConfigData('merchant_id');
        
        return md5(strtolower($passphrase . $merchantUUID . $postData['transactionAmount'] . $postData['transactionCurrency']));
    }
}
