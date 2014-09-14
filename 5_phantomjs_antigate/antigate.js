function solveCaptcha(captchaData, options) {
    if(typeof(options.numeric)==='undefined') options.numeric = 0;
    if(typeof(options.calc)==='undefined') options.calc = 0;
    
    var dfd = new $.Deferred();
    
    var fs = require('fs');
    var page = require('webpage').create();
    var antigateKey = fs.read('_antigate_key');
    
    var captchaBase64 = null;

    
    if(fs.isFile(captchaData)) {
        captchaBase64 = btoa(fs.open(captchaData, 'rb').read());
    } else {
        captchaBase64 = captchaData;
    }


    page.open('http://antigate.com/in.php', 'POST', 'method=base64&key=' + antigateKey + '&body=' + encodeURIComponent(captchaBase64) + '&numeric=' + options.numeric + '&calc=' + options.calc, function(status) {

        if (status !== 'success') {
            dfd.reject();
        } else {
            var captchaId = page.plainText.split("|")[1];

            if (captchaId !== undefined) {
                var solved = false;
                var captchaText = null;

                // how many times to check before we go further
                var checkNumber = 5;
                var checkInterval = setInterval(function() {
                    page.open('http://antigate.com/res.php?key=' + antigateKey + '&action=get&ids=' + captchaId, function(status) {

                        checkNumber--;

                        if (page.plainText.indexOf("CAPCHA_NOT_READY") > -1) {
                            solved = false;
                        } else {
                            solved = true;
                            captchaText = page.plainText;
                        }

                        if (checkNumber == 0 && solved == false) {
                            clearInterval(checkInterval);
                            dfd.reject();
                        } else if(solved == true) {
                            clearInterval(checkInterval);
                            dfd.resolve(captchaText);
                        }
                    });
                }, 15000);
            } else {
                dfd.reject();
            }
        }
    });

    return dfd.promise();
}