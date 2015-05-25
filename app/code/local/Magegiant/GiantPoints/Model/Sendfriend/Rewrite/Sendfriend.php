<?php

/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
* https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement.html
 */
class Magegiant_GiantPoints_Model_Sendfriend_Rewrite_Sendfriend extends Mage_Sendfriend_Model_Sendfriend
{
    public function send()
    {
        if ($this->isExceedLimit()) {
            Mage::throwException(Mage::helper('sendfriend')->__('You have exceeded limit of %d sends in an hour', $this->getMaxSendsToFriend()));
        }

        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate = Mage::getModel('core/email_template');

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender  = array(
            'name'  => $this->_getHelper()->htmlEscape($this->getSender()->getName()),
            'email' => $this->_getHelper()->htmlEscape($this->getSender()->getEmail())
        );

        $mailTemplate->setDesignConfig(array(
            'area'  => 'frontend',
            'store' => Mage::app()->getStore()->getId()
        ));

        foreach ($this->getRecipients()->getEmails() as $k => $email) {
            $name = $this->getRecipients()->getNames($k);
            $mailTemplate->sendTransactional(
                $this->getTemplate(),
                $sender,
                $email,
                $name,
                array(
                    'name'          => $name,
                    'email'         => $email,
                    'product_name'  => $this->getProduct()->getName(),
                    'product_url'   => $this->getProduct()->getUrlInStore(),
                    'message'       => $message,
                    'sender_name'   => $sender['name'],
                    'sender_email'  => $sender['email'],
                    'product_image' => Mage::helper('catalog/image')->init($this->getProduct(),
                            'small_image')->resize(75),
                )
            );
            if ($mailTemplate->getSentSuccess()) {
                $this->_earnPointsForSentFriends($email, $this->getProduct()->getId(), $this->getProduct()->getName());
            }
        }
        $translate->setTranslateInline(true);
        $this->_incrementSentCount();

        return $this;
    }

    protected function _earnPointsForSentFriends($email, $productId, $productName)
    {
        $customer = Mage::helper('giantpoints/customer')->getCustomer();
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $rewardAccount = Mage::helper('giantpoints/customer')->createRewardCustomer($customer);
        }
        $notice      = array(
            'email'      => $email,
            'product_id' => $productId
        );
        $alreadySent = Mage::getResourceModel('giantpoints/transaction_collection')
            ->hasAlreadySent($rewardAccount->getId(), 'customer_send_friend', $notice);
        if (count($alreadySent)) {
            return $this;
        }
        $rule = Mage::getModel('giantpoints/rule')
            ->getRuleByCondition($customer, Magegiant_GiantPoints_Model_Rule_Action::ACTION_SEND_FRIEND);
        if (!$rule || !$rule->getId()) {
            return $this;
        }
        $pointsForSent = $rule->getPointAmount();
        if (!$pointsForSent) {
            return $this;
        }
        $obj            = new Varien_Object(array(
            'point_amount'    => $pointsForSent,
            'product_name'    => $productName,
            'onhold_duration' => $rule->getOnholdDuration()

        ));
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $obj,
            'notice'        => $notice
        );
        Mage::helper('giantpoints/action')->createTransaction('customer_send_friend', $additionalData);
        $message = Mage::helper('giantpoints')->__('You earned %s for sent this product to your friends', Mage::helper('giantpoints')->addLabelForPoint($pointsForSent));
        Mage::getSingleton('core/session')->addSuccess($message);

        return $this;
    }
}