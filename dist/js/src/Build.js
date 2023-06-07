// [App.js][Build.js]
//
var App = App || {};

App.Build = function() {
    let refreshOff = function () {
        return true;
    }

    let refreshOn = function (mseconds) {
        // 30000, 20000, 16000, 10000, 8000
        let delay = (mseconds != undefined) ? mseconds : 16000;

        setTimeout(function() {
          location.reload();
        }, delay);
    }

    return {
        refreshOff: refreshOff,
        refreshOn: refreshOn
    };
}();
