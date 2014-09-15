// example of the input '{"name":"Тимошенко Юлія Володимирівна"}'

var startTime = (new Date).getTime();
var page = require('webpage').create();
var fs = require('fs');
var system = require('system');
phantom.page.injectJs('./antigate.js');
phantom.page.injectJs('./jquery-1.11.1.min.js');

fs.isFile('captcha.png') ? fs.remove('captcha.png'):null;
fs.isFile('result.png') ? fs.remove('result.png'):null;

var args = system.args;
var input = null;
// parsing the input arguments, input should be the JSON string
if (args.length === 1) {
    console.log(JSON.stringify({'status': 'error', 'message': 'The input should be passed to the script'}));
    phantom.exit();
} else {
    try {
        input = JSON.parse(args[1]);
    } catch (e) {
        console.log(JSON.stringify({'status': 'error', 'message': 'Input argument should be in JSON format'}));
        phantom.exit();
    }
}

page.open('http://search.irc.gov.ua/edr.html', function() {
    page.includeJs("http://code.jquery.com/jquery-1.11.0.min.js", function() {

        // making the form visible and filling it
        page.evaluate(function(input) {
            $("#agreeCheckSearch").trigger('click');
            $("#searchform").children("table").css("visibility", "visible");
            $('#agreeCheckSearch').attr("checked", true);
            $('input[name=query]').val(input.name);
            $('#searchform').submit();
        }, input);

        // workaround, for the known bug https://github.com/ariya/phantomjs/issues/10832
        setTimeout(function() {

            (function() {
                var dfd = new $.Deferred();
                // how many times to check before we go further
                var checkNumber = 3;
                var checkInterval = setInterval(function() {
                    checkNumber--;
                    var resultSuccess = page.evaluate(function() {
                        return $('img.captcha').is('*');
                    });
                    if (resultSuccess) {
                        clearInterval(checkInterval);
                        dfd.resolve();
                    } else if (checkNumber == 0) {
                        clearInterval(checkInterval);
                        dfd.reject();
                    }
                }, 3000);
                return dfd.promise();
            })().then(/*success*/ function() {
                var captchaObj = page.evaluate(function() {
                    function getImgDimensions($i) {
                        if (typeof ($i) == undefined) {
                            return false;
                        }

                        return {
                            top: $i.offset().top,
                            left: $i.offset().left,
                            width: $i.width(),
                            height: $i.height()
                        }
                    }
                    // get captcha	
                    return getImgDimensions($("img.captcha"));
                });

                if (captchaObj == false) {
                    console.log(JSON.stringify({'status': 'error', 'message': 'Unable find captcha'}));
                    phantom.exit();
                }

                page.clipRect = captchaObj;
                var captchaData = page.renderBase64('PNG');
                // page.render('captcha.png');

                page.clipRect = {left: 0, top: 0, width: 0, height: 0};
                solveCaptcha(captchaData, {'numeric': 0, 'calc': 0}).then(function(captchaText) {

                    page.evaluate(function(captchaText) {
                        $('input[name=captcha]').val(captchaText);
                        $('#searchform').submit();
                    }, captchaText);

                    (function() {
                        var dfd = new $.Deferred();

                        var checkNumber = 3;
                        var checkInterval = setInterval(function() {
                            checkNumber--;

                            var resultSuccess = page.evaluate(function() {
                                return $('#restable').is('*');
                            });
                            var resultEmpty = page.evaluate(function() {
                                return $('body:contains(На жаль)').is('*');
                            });
                            if (resultSuccess || resultEmpty) {
                                clearInterval(checkInterval);
                                dfd.resolve();
                            } else if (checkNumber == 0) {
                                clearInterval(checkInterval);
                                dfd.reject();
                            }
                        }, 5000);

                        return dfd.promise();
                    })().then(/* success */ function() {
                        var result = page.evaluate(function() {
                            var result = [];
                            $('#restable tr:gt(0)').each(function(key, value) {
                                var object = {};
                                object.name = $(value).find('td:eq(0)').text();
                                object.registration = $(value).find('td:eq(1)').text();
                                object.status = $(value).find('td:eq(2)').text();

                                result.push(object);

                            });

                            return result;
                        });

                        page.render('result.png');

                        var endTime = (new Date).getTime();
                        console.log(JSON.stringify({'status': 'success',
                                                    'result': result,
                                                    'executionTime': ((endTime - startTime) / 1000)}));

                        phantom.exit();
                    },
                            /* failure */
                                    function() {
                                        console.log(JSON.stringify({'status': 'error', 'message': 'Unable to submit the search'}));
                                        phantom.exit();
                                    });


                        }, function() {
                    console.log(JSON.stringify({'status': 'error', 'message': 'Unable to solve captcha'}));
                    phantom.exit();
                });
            }, /*failure*/ function() {
                console.log(JSON.stringify({'status': 'error', 'message': 'Unable to submit the search'}));
                phantom.exit();
            });
        }, 1);
    });
});