let o = {
    "version": "ae1g20e"
};

// [App.js]
// [Html.js]
// [Http.js]
// [Util.js]
// [Model.js]
// [Resource.js]
// [Form.js]
// [Build.js]

/** Main application file. */

let div = document.getElementById('post-content');

let m = App.Html.Markup;

/**
 * Callback_Data_fdex
 * @constructor
 * @param Object data
 */
let Callback_Data = function (d) {
    if ((d.data !== undefined) && (d.mode === 'plugin')) {
        div.innerHTML = '';
        let ul = m('ul');
        d.data.forEach(function (el) {
            let li = m('li');
            li.innerHTML = el;
            if (el === 'fdex') { li.addEventListener('click', Call_Fdex); }
            ul.appendChild(li);
        });
        div.appendChild(ul);
    }
    if ((d.data !== undefined) && (d.mode === 'select')) {
        div.innerHTML = '';
        let select = m('select');
        let option_default = m('option');
        option_default.value = '';
        option_default.innerHTML = '';
        select.appendChild(option_default);

        Object.keys(d.data).forEach(function (k) {
            let option = m('option');
            let v = d.data[k];
            option.value = k;
            option.innerHTML = v + '('+k+')';
            select.appendChild(option);
        });
        if (1 === 1) { select.addEventListener('change', Call_Fdex_Select); }
        div.appendChild(select);
    }
};

let Load_Data = function (e) {
    _Call(e, Callback_Data);
};

let Callback_Fdex = function (d) {
    console.log(d);
};

let Call_Fdex_Select = function (e) {
    console.log(e);
    let info = App.Http.Info();
    info.endpoint = App.Http.unset_param(info.endpoint, 'collid');
    let selected = e.target.value;
    if (selected !== '') {
        window.location.assign(info.endpoint+'&collid='+selected);
    }
};

let Call_Fdex = function (e) {
    let info = App.Http.Info();
    info.endpoint = App.Http.unset_param(info.endpoint, 'plugin');
    window.location.assign(info.endpoint+'?plugin=fdex');
    _Call(e, Callback_Fdex);
};

let _Call = function (e, callback) {
    let info = App.Http.Info();
    let tmp = App.Http.request_params();
    tmp.endpoint = info.endpoint;
    tmp.callback = callback;
    tmp.format = function (e, r, d) {
        // console.log(e, r, d);
        return JSON.parse(d);
    };
    tmp.data = JSON.stringify(info.params);

    App.Http.Request(e, tmp);
};
