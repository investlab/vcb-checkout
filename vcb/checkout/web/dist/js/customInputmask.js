var customInputmask = (function () {
    var config = {
        extendDefaults: {
            nullable: false,
            clearMaskOnLostFocus: false,
            clearIncomplete: true,
            /*  onUnMask: function (maskedValue, unmaskedValue, opts) {
                  if (unmaskedValue === "" && opts.nullable === true) {
                      return unmaskedValue;
                  }
                  var processValue = maskedValue.replace(opts.prefix, "");
                  processValue = processValue.replace(opts.suffix, "");
                  processValue = processValue.replace(new RegExp(Inputmask.escapeRegex(opts.groupSeparator), "g"), "");
                  if (opts.radixPoint !== "" && processValue.indexOf(opts.radixPoint) !== -1)
                      processValue = processValue.replace(Inputmask.escapeRegex.call(this, opts.radixPoint), ".");

                  return processValue;
              },
                                      <<?? function sử dụng cho việc serialize??>>
              */
            // onBeforeMask:    function (pastedValue, opts) {
            //     if (pastedValue == '' || pastedValue == '0' || pastedValue == 0) {
            //         this.clearMaskOnLostFocus = true;
            //         return null;
            //     } else {
            //         this.clearMaskOnLostFocus = false;
            //     }
            // }
        },
        extendDefinitions: {},
        extendAliases: {
            'currencyDecimal': {
                alias: 'decimal',
                groupSeparator: '.',
                radixPoint: ',',
                digits: 2,
                enforceDigitsOnBlur: true,
                autoUnmask: true,
                removeMaskOnSubmit: true,
                digitsOptional: false,
            },
            'currencyNumbered': {
                alias: 'integer',
                groupSeparator: '.',
                enforceDigitsOnBlur: true,
                radixPoint: ',',
                autoUnmask: true,
                removeMaskOnSubmit: true,
                digitsOptional: false,
            },
            'percentageDecimal': {
                alias: 'percentage',
                digits: 2,
                groupSeparator: '.',
                radixPoint: ',',
                enforceDigitsOnBlur: true,
                autoUnmask: true,
                removeMaskOnSubmit: true,
                digitsOptional: false,
            },
            'singleDate': {
                alias: 'datetime',
                inputFormat: 'dd/mm/yyyy',
                positionCaretOnClick: 'none',
                jitMasking: true,
                showMaskOnFocus: false,
                showMaskOnHover: false,
            },
            'doubleDate': {
                alias: 'datetime',
                inputFormat: 'dd/mm/yyyy-dd/mm/yyyy',
                positionCaretOnClick: 'none',
                jitMasking: true,
                showMaskOnFocus: false,
                showMaskOnHover: false,
            },
            'time': {
                alias: 'datetime',
                inputFormat: 'HH:MM',
                positionCaretOnClick: 'none',
                jitMasking: true,
                showMaskOnFocus: false,
                showMaskOnHover: false,
            },
            'numberInt':{
                alias: 'integer',
                min:0,
                groupSeparator: '.',
                autoUnmask: true,
                placeholder:'',
                removeMaskOnSubmit: true,
            },
            'bankAccount':{
                 alias: 'integer',
                  _mask: function _mask(opts) {
                    return "(" + opts.groupSeparator + "9999){+|1}";
                },
                max:9999999999999999,
                groupSeparator: ' ',
                rightAlign : false,
                showMaskOnFocus: false,
                showMaskOnHover: false,
                placeholder:'',
                removeMaskOnSubmit: true,
            },
            'singleDate1': {
                alias: 'datetime',
                inputFormat: 'mm/yy',
                positionCaretOnClick: 'none',
                jitMasking: true,
                showMaskOnFocus: false,
                showMaskOnHover: false,
            },
        },
    };
    var init = function () {
        Inputmask.extendDefaults(config.extendDefaults);
        Inputmask.extendDefinitions(config.extendDefinitions);
        Inputmask.extendAliases(config.extendAliases);
    };
    return {
        init: init
    };
}());
(function () {
    customInputmask.init();
}());
