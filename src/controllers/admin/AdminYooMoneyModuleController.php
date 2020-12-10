<?php

/**
 * @author YooMoney <cms@yoomoney.ru>
 * @copyright © 2020 "YooMoney", NBСO LLC
 * @license  https://yoomoney.ru/doc.xml?id=527052
 */

use YooKassa\Model\PaymentStatus;
use YooKassa\Request\Payments\Payment\CreateCaptureRequest;

/**
 * Class AdminYooMoneyModuleController
 *
 * @property yoomoneymodule $module
 */
class AdminYooMoneyModuleController extends ModuleAdminController
{
    public function postProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit();
        }
        if (!$this->module->active) {
            return;
        }

        $action = Tools::getValue('action');

        switch ($action) {
            case 'capturePayment':
                $this->capturePayment();
                break;
            case 'cancelPayment':
                $this->cancelPayment();
                break;
            case 'voteNps':
                $this->voteNps();
                break;
        }
    }

    private function capturePayment()
    {
        $orderId = (int)Tools::getValue('order_id');
        $this->module->log('debug', 'Capture payment for order #'.$orderId);

        $order = new Order($orderId);
        $kassa = $this->module->getKassaModel();
        if ((int)$order->getCurrentState() !== $kassa->getOnHoldStatusId()) {
            $this->module->log('error', 'Capture payment error: wrong order status');
            return;
        }

        $payment = $kassa->findOrderPayment($orderId);
        if (!$payment || $payment->getStatus() !== PaymentStatus::WAITING_FOR_CAPTURE) {
            $this->module->log('error', 'Capture payment error: wrong payment status: '
                .$payment->getStatus());
            return;
        }

        $response = null;
        try {
            $builder = CreateCaptureRequest::builder();
            $cart = new Cart($order->id_cart);

            $totalAmount   = $cart->getOrderTotal(true);
            $rubCurrencyId = Currency::getIdByIsoCode('RUB');
            if ($cart->id_currency != $rubCurrencyId) {
                $from = new Currency($cart->id_currency);
                $to   = new Currency($rubCurrencyId);
                $this->module->log('debug', 'Convert amount from "'.$from->name.'" to "'.$to->name.'"');
                $totalAmount = Tools::convertPriceFull($totalAmount, $from, $to);
            }
            $builder
                ->setAmount($totalAmount)
                ->setCurrency('RUB');

            $customer = new Customer((int)$order->id_customer);

            if ($kassa->getSendReceipt()) {
                $kassa->addReceiptItems($customer, $cart, $builder);
            }
            $request = $builder->build();
            if ($kassa->getSendReceipt()) {
                $request->getReceipt()->normalize($request->getAmount());
            }

            $response = $kassa->getApiClient()->capturePayment($request, $payment->getId());
        } catch (\Exception $e) {
            $this->module->log('error', 'Capture error: '.$e->getMessage());
            $response = $payment;
        }
        if (!$response || $response->getStatus() !== PaymentStatus::SUCCEEDED) {
            $this->module->log('error', 'Capture payment error: capture failed');
            return;
        }

        $history           = new OrderHistory();
        $history->id_order = $orderId;
        $history->changeIdOrderState($kassa->getSuccessStatusId(), $orderId);
        $kassa->updateOrderPaymentId($orderId, $payment);

        header('Content-Type: application/json');
        echo json_encode(array('result' => 'success'));
    }

    public function cancelPayment()
    {
        $orderId = (int)Tools::getValue('order_id');
        $this->module->log('debug', 'Cancel payment for order #'.$orderId);

        $order = new Order($orderId);
        $kassa = $this->module->getKassaModel();
        if ((int)$order->getCurrentState() !== $kassa->getOnHoldStatusId()) {
            $this->module->log('error', 'Cancel payment error: wrong order status');
            return;
        }

        $payment = $kassa->findOrderPayment($orderId);
        if (!$payment || $payment->getStatus() !== PaymentStatus::WAITING_FOR_CAPTURE) {
            $this->module->log('error', 'Cancel payment error: wrong payment status: '
                .$payment->getStatus());
            return;
        }

        $response = $kassa->cancelPayment($payment);
        if (!$response || $response->getStatus() !== PaymentStatus::CANCELED) {
            $this->module->log('error', 'Cancel payment error: cancel failed');
            return;
        }

        $history           = new OrderHistory();
        $history->id_order = $orderId;
        $history->changeIdOrderState($kassa->getCancelStatusId(), $orderId);
        $kassa->updateOrderPaymentId($orderId, $payment);

        header('Content-Type: application/json');
        echo json_encode(array('result' => 'success'));
    }

    public function voteNps()
    {
        Configuration::UpdateValue('YOOMONEY_NPS_VOTE_TIME', time());

        header('Content-Type: application/json');
        echo json_encode(array('result' => 'success'));
    }

}
