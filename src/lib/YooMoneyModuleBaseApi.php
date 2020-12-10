<?php 
/**
* Module is prohibited to sales! Violation of this condition leads to the deprivation of the license!
*
* @category  Front Office Features
* @package   YooMoney Payment Solution
* @author    YooMoney <cms@yoomoney.ru>
* @copyright © 2020 "YooMoney", NBСO LLC
* @license   https://yoomoney.ru/doc.xml?id=527052
*/

class YooMoneyModuleBaseApi
{
    const MONEY_URL = "https://yoomoney.ru";
    const SP_MONEY_URL = "https://yoomoney.ru";
    
    public static function sendRequest($url, $options=array(), $access_token=null)
    {
        $full_url= self::MONEY_URL . $url;
        if ($access_token != null) {
            $headers = array(
                "Authorization" => sprintf("Bearer %s", $access_token),
            );
        } else {
            $headers = array();
        }
        $result = \YooMoneyModuleRequests::post($full_url, $headers, $options);
        return self::processResult($result);
    }
    
    public static function de($object, $kill = true)
    {
        echo '<xmp style="text-align: left;">';
        print_r($object);
        echo '</xmp><br />';

        if ($kill) {
            die('END');
        }

        return $object;
    }
    
    protected static function processResult($result)
    {
        switch ($result->status_code) {
            case 400:
                throw new PrestaShopException('Format error #400');
                break;
            case 401:
                throw new PrestaShopException('Token error #401');
                break;
            case 403:
                throw new PrestaShopException('Scope error #403');
                break;
        }
        return json_decode($result->body);
    }
}
