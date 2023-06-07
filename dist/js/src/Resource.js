// [App.js][Resource.js]
var App = App || {};

App.Resource = function () {
    let hold = function () {
        return true;
    };

    return {
        hold: hold
    };

}();
