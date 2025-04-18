<?php

/*-----------------------------------CYBERSOURCE-STB-TEST------------------------------------------*/
//define ('CBS_FLEX_HMAC_SHA256', 'HmacSHA256');
//define ('CBS_FLEX_SHARED_SECRET_KEY', 'yPnuFuVzOwh58FYzxF3BErRHLLMFVuVGDo52WoqnD+A=');
//define ('CBS_FLEX_KEY_ID','cf25e2f1-f8b4-4e85-8d52-b7da994ce27f');
//define ('CBS_FLEX_MERCHANT_ID','nganluong');
//
//define('CBS_FLEX_TARGET_ORIGIN', SERVER_NAME);
////define('CBS_FLEX_TARGET_ORIGIN',"https://sandbox.nganluong.vn:8088");
//define('CBS_FLEX_SECURE_HTTPS','https://');
//define('CBS_FLEX_HOST','apitest.cybersource.com');
//define('CBS_FLEX_DESTINATION_RESOURCE','flex/v1/keys');
//
//define('CBS_FLEX_SHA256','sha256');
//define('CBS_FLEX_ENCRYPTION_TYPE','RsaOaep256');
////
//define('CBS_SOAP_MERCHANT_ID','nganluong');
//define('CBS_SOAP_TRANSACTION_KEY','ZwdtMPU2HcIq/wXydgGZAywwnP2umabeLbk4X+XhdNjK30I2NUqRUcPONrlNPwsAXC8D9v8S3LQqINjnHwAXc8W6nMvNZ2t6aNdFgXwoHtovDmGS/wHQ1FVQ12h9bD/bvdfl6FYFWlmR+qwAGgstinpo8Zy0CLkiWsNxZLlLPVxtU67E3dykMCNydmrT1dFlD0VDQKsTUrlF+vMojOphFxJgtO6WZSTw+/Ousd1j+063wSyXmItTwFmWhpFj0W/3vdsT4S1i7uMMfKAf/sFN7q1ygW5pazX/f75P4dKiHVnNPrujCYnT9WVXv1LHda+tO9M1croWqpv8SP7291uoaA==');
//define('CBS_SOAP_WSDL','https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.50.wsdl');

/*-----------------------------------CYBERSOURCE-STB------------------------------------------*/
define('CBS_FLEX_HMAC_SHA256', 'HmacSHA256');
define('CBS_FLEX_SHARED_SECRET_KEY', 'aYN+ru/e2JvuLWP86LSmKNAF2Ch2IixuxVCZOMAgiho=');
define('CBS_FLEX_KEY_ID', 'ba772a7d-cb45-4d31-a1ac-d81fe9efa7bb');
define('CBS_FLEX_MERCHANT_ID', 'nganluong');
define('CBS_FLEX_TARGET_ORIGIN', 'https://vietcombank.nganluong.vn');
define('CBS_FLEX_SECURE_HTTPS', 'https://');
define('CBS_FLEX_HOST', 'api.cybersource.com');
define('CBS_FLEX_DESTINATION_RESOURCE', 'flex/v1/keys');
define('CBS_FLEX_SHA256', 'sha256');
define('CBS_FLEX_ENCRYPTION_TYPE', 'RsaOaep256');
define('CBS_SOAP_MERCHANT_ID', 'nganluong');
define('CBS_SOAP_TRANSACTION_KEY', 'dZta3imOc4zH3BZMEchgkhyyPur0x2ICbCX+PfW7wR90LiHq440+JgteyXPQs2+5QNo1bcCfFxMi/ZrKRCYPCYFqohoLSwn9827zRB2KwJNfX/qZP7Ee5pb3f1FlRwWH1pbUReAb0SJfW0zLBrCMENiXemXuvVZk7W2ccREZxAPdqfQJttcm1PRX/oQveFjjwfOepoBTUhdwB2RM8IVuXGPPbTxi1NwOHtcKbpAlK5sEMCpv3HSjLegGBm+sZ/85iOGAtoPXIPCyMwrY23zozt6W95dPeyyFbWla4ceSD6hlZFxwL64rv4+9KM7RBvrpU6PCNOuvbdqa99a6kxPYTw==');
define('CBS_SOAP_WSDL', 'https://ics2ws.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.50.wsdl');
if (APP_ENV == 'prod') {
    define('CBS_SOAP_WSDL_3DS2', 'https://ics2ws.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.205.wsdl');
    define('GATEWAY_VA_ENDPOINT', 'https://gateway05.nganluong.vn/gateway/restful/api/request');
    define('GATEWAY_VA_ENDPOINT_UAT', 'https://uat-gateway05.nganluong.vn/gateway/restful/api/request');

    define('VCCB_VA_PARTNER_ID', 20);

    // VCB VA
    define('GET_BILL_VCB_VA_URL', 'https://vietcombank.nganluong.vn/api/web/partner/vcb-va-get-bill');
    define('PAY_BILL_VCB_VA_URL', 'https://vietcombank.nganluong.vn/api/web/partner/vcb-va-pay-bill');
    define('FLAG_EXPLICIT_ERROR', false);
    define('VCB_VA_PARTNER_ID', 32);



} else {
    define('CBS_SOAP_WSDL_3DS2', 'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.205.wsdl');
    define('GATEWAY_VA_ENDPOINT', 'https://gateway02-sandbox.nganluong.vn/naba/restful/api/request');
    define('GATEWAY_VA_ENDPOINT_UAT', 'https://gateway02-sandbox.nganluong.vn/naba/restful/api/request');

    // VCB VA
    define('GET_BILL_VCB_VA_URL', 'https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/api/web/partner/vcb-va-get-bill');
    define('PAY_BILL_VCB_VA_URL', 'https://sandbox2.nganluong.vn/vietcombank-checkout/vcb/api/web/partner/vcb-va-pay-bill');
    define('FLAG_EXPLICIT_ERROR', false);
    define('VCB_VA_PARTNER_ID', 25);


    define('VCCB_VA_PARTNER_ID', 28);
}

