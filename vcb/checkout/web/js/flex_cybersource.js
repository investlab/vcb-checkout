var flex_key = $('#flex_key').html();

var jwk = flex_key;
jwk = JSON.parse(jwk);

var form = document.querySelector('#form-checkout');
var payButton = document.querySelector('#pay-button');
var flexResponse = document.querySelector('#flex-response');
var expMonth = document.querySelector('#expMonth');
var expYear = document.querySelector('#expYear');

// SETUP MICROFORM
FLEX.microform(
    {
        keyId: jwk.kid,
        keystore: jwk,
        container: '#cardNumber-container',
        label: '#cardNumber-label',
        placeholder: '',
        styles: {
            'input': {
                'font-family': '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"',
                'font-size': '1rem',
                'line-height': '1.5',
                'color': '#495057',
            },
            ':focus': {'color': 'blue'},
            ':disabled': {'cursor': 'not-allowed'},
            'valid': {'color': '#3c763d'},
            'invalid': {'color': '#a94442'},
        },
        encryptionType: 'rsaoaep256'
    },
    function (setupError, microformInstance) {
        if (setupError) {
            // handle error
            return;
        }

        // intercept the form submission and make a tokenize request instead
        payButton.addEventListener('click', function () {
            // Send in optional parameters from other parts of your payment form
            var options = {
                cardExpirationMonth: expMonth.value,
                cardExpirationYear: expYear.value
                // cardType: /* ... */
            };
            payButton.disabled = true;
            payButton.innerHTML = 'Loading...';
            microformInstance.createToken(options, function (err, response) {
                if (err) {
                    alert('Thông tin thẻ không chính xác');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Tiếp tục';
                    return false;
                } else {
                    $('#create-token-res').val(response);
                    // At this point the token may be added to the form
                    // as hidden fields and the submission continued
                    flexResponse.value = JSON.stringify(response);
                    form.submit();
                }
            });
        });

    }
);

// $('#paymentmethodcreditcardcybersourceform-card_number').hide();
// $('#cardNumber-container input[name=credit-card-number]').on('change', function () {
//     $('#paymentmethodcreditcardcybersourceform-card_number').val($(this.val()));
// });