{**
* Module is prohibited to sales! Violation of this condition leads to the deprivation of the license!
*
* @author    YooMoney <cms@yoomoney.ru>
* @copyright © 2020 "YooMoney", NBСO LLC
* @license   https://yoomoney.ru/doc.xml?id=527052
*
* @category  Front Office Features
* @package   YooMoney Payment Solution
*}
<script src="https://static.yoomoney.ru/checkout-credit-ui/v1/index.js"></script>
<style type="text/css">

    .yoomoney-pay-button {
        font-family: YandexSansTextApp-Regular, Arial, Helvetica, sans-serif;
        text-align: center;
        height: 60px;
        width: 155px;
        border-radius: 4px;
        transition: 0.1s ease-out 0s;
        color: #000;
        box-sizing: border-box;
        outline: 0;
        border: 0;
        background: #FFDB4D;
        cursor: pointer;
        font-size: 12px;
    }

    .yoomoney-pay-button:hover, .yoomoney-pay-button:active {
        background: #f2c200;
    }

    .yoomoney-pay-button span {
        display: block;
        font-size: 20px;
        line-height: 20px;
    }

    .yoomoney-pay-button_type_fly {
        box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.12), 0 5px 10px -3px rgba(0, 0, 0, 0.3);
    }
</style>

<div class="row">
    <div class="col-xs-6">
        <p class="payment_module">
            <a href="{$link->getModuleLink('yoomoneymodule', 'paymentkassa')|escape:'quotes':'UTF-8'}"
               title="{l s='Payment with YooKassa' mod='yoomoneymodule'}"
               class="yoomoney_yoo_money yoomoney_payment">
                {l s='Payment with YooKassa' mod='yoomoneymodule'}
            </a>
        </p>
    </div>
    <div class="col-xs-6">
        <form method="post" action="{$action|escape:'htmlall':'UTF-8'}" id="yoomoney-form">
            <input type="hidden" class="form-check-input" name="payment_method" id="yoomoney-form-payment-type"
                   value="installments"/>
            {if $model->getShowInstallmentsButton()}
                <div id="installment-wrapper" class="installment-wrapper" style="float: right"></div>
            {/if}
        </form>
    </div>
</div>
{if $model->getShowInstallmentsButton()}
    <script>
        const yoomoneyCheckoutCreditUI = YandexCheckoutCreditUI({
            shopId: '{$model->getShopId()}',
            sum: '{$amount}',
            language: 'ru'
        });
        const yoomoneyCheckoutCreditButton = yoomoneyCheckoutCreditUI({
            type: 'button',
            theme: 'default',
            domSelector: '.installment-wrapper'
        });
    </script>
{/if}