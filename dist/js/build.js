// [App.js][Html.js]

var App = App || {};

App.Html = function () {
    let Markup = function (name, attributes, innerValue) {
        let node = document.createElement(name);
        if (attributes != undefined) {
            Object.keys(attributes).map(function(key, index) {
                let value = attributes[key];
                node.setAttribute(key, value);
            });
        }

        node = node.cloneNode(false);

        if (innerValue != undefined) {
            node.innerHTML = innerValue;
        }
        return node;
    }

    let m = function (name, attributes, innerValue) {
        return Markup(name, attributes, innerValue);
    }

    let set_title = function (label) {
        let title = document.getElementsByTagName('title')[0];
        title.innerHTML = label;
    }

    let set_button_listener = function (name, el, data) {
        let listener = false;
        let action = false;
        let func = false;
        let inner = (el.value !== undefined) ? el.value : '';
        delete el.value;
        if (el.listener !== undefined) {
            listener = true;
            action = el.listener[0];
            func = el.listener[1];
            delete el.listener;
        };
        let btn = tag_button(name, el);
        btn.innerHTML = str_replace_map(data, inner);

        if (listener) {
            btn.addEventListener(action, func);
        };
        return btn;
    }

    let Clear = function (node) {
        if (node != undefined) {
            node.innerHTML = '';
        }
    }

    let Delete = function (node) {
        if (node != undefined) {
            node.outerHTML = '';
        }
    }

    let Render = function (params) {
        let Config = params.config;
        let Data = params.data;
        let Layout = params.layout;
        let Node = params.parent;

        Layout.forEach(function(r) {
            let div = Row();
            Object.keys(r).forEach(function(el) {
                let obj = {
                    name: el,
                    index: r[el],
                    element: Config.elements[r[el]],
                    data: Data
                };
                div = element_row(div, obj, params);
            });
            Node.appendChild(div);
        });
    }

    let element_row = function (parent, obj, params) {
        let Config = params.config;
        let Data = params.data;
        let el = obj.element;
        let name = obj.name;

        let div = Column(3);
        if (el.tag !== undefined) {
            switch (el.tag) {
                case "input":
                    div = tag_input(div, key, value);
                    break;
                case "textarea":
                    div = tag_input(div, key, value);
                    break;
                case "select":
                    div = tag_select(div, key, value);
                    break;
                case "button":
                    let btn = set_button_listener(name, el, Data);
                    parent.appendChild(btn);
                    break;
                default:
                    div = tag_by_name(div, name, el, Data);
            }
            parent.appendChild(div);
        }
        return parent;
    }

    let tag_input = function (parent, name, attributes) {
        let tag = (attributes.tag != undefined) ? attributes.tag : 'input';
        let tagType = (attributes.type != undefined) ? attributes.type : 'text';
        delete attributes.tag;
        let id = (attributes.id != undefined) ? attributes.id : name;
        if (attributes.id == undefined) { attributes.id = name; }
        let inner = name;
        if (attributes.label != undefined) {
            inner = attributes.label;
            delete attributes.label;
        }
        let label = m('label', { for: id }, inner);

        let inner2 = "";
        if ((tag === 'textarea') && (attributes.value)) {
            inner2 = attributes.value;
            delete attributes.value;
        }
        let input = m(tag, attributes, inner2);
        if ((tagType === 'checkbox') && (attributes.value)) {
            if (attributes.value === "1") {
                // console.log(typeof(input));
                input.checked = true;
            }
        }

        parent.appendChild(label);
        parent.appendChild(input);
        return parent;
    }

    let tag_checkbox = function (parent, name, attributes) {
    }

    let tag_select = function (parent, name, attributes) {
        let tag = (attributes.tag != undefined) ? attributes.tag : 'select';
        delete attributes.tag;
        let id = (attributes.id != undefined) ? attributes.id : name;
        if (attributes.id == undefined) { attributes.id = name; }
        let inner = name;
        if (attributes.label != undefined) {
            inner = attributes.label;
            delete attributes.label;
        }
        let label = m('label', { for: id }, inner);
        let select = m('select', { id: id, class: '_width100' });
        if (attributes.options != undefined) {
            Object.keys(attributes.options).map(function(key, index) {
                let option = m('option', { value: key }, attributes.options[key]);
                select.appendChild(option);
            });
        }
        if (attributes.value) {
            // select.selectedIndex = attributes.value;
            select.value = attributes.value;
        }
        label.appendChild(select);
        parent.appendChild(label);
        return parent;
    }

    let tag_button = function (name, attributes) {
        let inner = name;
        delete attributes.tag;
        return m('button', attributes, inner);
    }

    let tag_clean_attributes = function (attributes) {
        Object.keys(attributes).map(function(a) {
            if (attributes[a] === false) {
                delete attributes[a];
            };
        });
        return attributes;
    }

    let tag_by_name = function (parent, name, attributes, data) {
        let inner = (attributes.value !== undefined) ? str_replace_map(data, attributes.value) : '';
        delete attributes.value;
        let type = attributes.tag;
        delete attributes.tag;

        parent.appendChild(m(type, tag_clean_attributes(attributes), inner));
        return parent;
    }

    let Column = function (colsize=6) {
        return m('div', { class: "col m" + colsize });
    }

    let Row = function () {
        return m('div', { class: "row" });
    }

    let node_appendChildById = function (id, child) {
        let doc = document.getElementById(id);
        return ((doc) && (child)) ? doc.appendChild(child) : false;
    }

    let node_replace_class = function (node, find, replace) {
        if (typeof node.classList != 'undefined') {
            node.classList.remove(find);
            node.classList.add(replace);
            return node;
        }
        if ( node.className.match(/(?:^|\s)find(?!\S)/) ) {
            node.className = node.className.replace( /(?:^|\s)find(?!\S)/g, replace );
        }
        return node;
    }

    let node_insert_after = function (node, existing_node) {
        existing_node.parentNode.insertBefore(node, existing_node.nextSibling);
    }

    return {
        Clear: Clear,
        Delete: Delete,
        Markup: Markup,
        node_appendChildById: node_appendChildById,
        node_replace_class: node_replace_class,
        node_insert_after: node_insert_after,
        m: m,
        Render: Render,
        set_title: set_title
    };
}();
// [App.js][Http.js]
var App = App || {};
App.Http = function () {

    let get_href = function () {
        if (typeof window !== 'undefined') {
            return window.location.href;
        }
        return false;
    }

    let get_href_parts = function () {
        let params = {};
        let obj = get_href();

        var parts = obj.replace(window.location.hash, "").replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            params[key] = value;
        });

        let urlObject = new URL(document.location.href);
        let attr = urlObject.searchParams;
        // params.__ = attr;

        return params;
    }

    let get_base = function () {
        let domain = location.toString().replace(location.search, "").replace(location.hash, "");
        return domain;
    }

    let parentPath = function (url) {
        let sl = '/';
        let ts = url.replace(/\/$/, "");
        let arr = ts.split(sl);
        arr.pop();
        let purl = (arr.join(sl) == "")
            ? sl
            : arr.join(sl);
        return (url.length == ts.length)
            ? purl
            : (purl == sl) ? purl : purl + sl
    }

    let page_info = function () {
        let info = {
            url: get_href(),
            base: get_base(),
            protocol: window.location.protocol,
            domain: window.location.hostname,
            port: (window.location.port) ? window.location.port : '',
            params: get_href_parts(),
            hash: (window.location.hash) ? window.location.hash : false,
            query: (window.location.search) ? window.location.search : false
        };
        let wodom = info.url.replace(info.base, "");
        let plugin = wodom.replace("?", "").split('&')[0];
        let url_no_hash = info.url.replace(info.hash, "");
        info.offset = info.base.replace(info.protocol+'//'+info.domain, '');
        info.offset = (info.port !== '') ? info.offset.replace(':'+info.port, '') : info.offset;
        let endpoint = info.offset + url_no_hash.replace(info.base, "");
        info.query_delim = '?/';
        info.resource = plugin.split('/')[1];
        info.resource_url = info.offset + info.query_delim + info.resource;
	info.offset_parent = parentPath(info.offset);
        info.append = function(str) { return endpoint + str; };
        info.append_subdir = function(str) {
            let e = (endpoint.substr(-1) === '/')
                ? endpoint.substr(0, endpoint.length - 1) : endpoint;
            let p = (str.substr(0) === '/')
                ? str.substr(1, str.length) : str;
            return [e,p].join('/');
        };

        info.plugin = plugin;
        info.url_no_hash = url_no_hash;
        info.endpoint = endpoint;

        return info;
    }

    let unset_param = function (uri, name) {
        let info = page_info();
        if (info.params[name] != undefined) {
            let key = '&'+name+'='+info.params[name];
            let edit = uri.replace(key, '');
            key = '?'+name+'='+info.params[name];
            edit = uri.replace(key, '');
            return edit;
        }
        return uri;
    }

    let get_param = function (name) {
        let info = page_info();
        return (has_param(name)) ? info.params[name] : false;
    }

    let has_param = function (name) {
        let info = page_info();
        if (info.params[name] != undefined) {
            return true;
        }

        return false;
    }

    let is_plugin = function () {
        let info = page_info();
        return (info.plugin !== "") ? true : false;
    }

    let has_param_value = function (name, value) {
        let info = page_info();
        if (info.params[name] != undefined) {
            if (info.params[name] === value) {
                return true;
            }
        }

        return false;
    }

    let encode_query = function (data) {
       const ret = [];
       for (let d in data)
         ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
       return ret.join('&');
    }

    let request_params = function (Request) {
        let req = (Request) ? Request : {};
        let res = {};
        res.endpoint = (req.endpoint != undefined) ? req.endpoint : false;
        res.data = (req.data != undefined) ? req.data : false;
        res.format = (req.format != undefined) ? req.format : false;
        res.callback = (req.callback != undefined) ? req.callback : false;

        return res;
    }

    let request_promise = function (event, Request, method='POST') {
        let attr = request_params(Request);
        return new Promise((resolve, reject) => {
            let xhttp = new XMLHttpRequest();

            /*
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    return attr.callback(attr.format(event, Request, this.responseText))
                }
            };
            */
            xhttp.open(method, attr.endpoint, true);
            xhttp.setRequestHeader('Content-Type', 'application/json');
            xhttp.onerror = () => reject(xhttp.statusText);
            xhttp.onload = () => {
                if (xhttp.status >= 200 && xhttp.status < 300) {
                    resolve(xhttp.response);
                } else {
                    reject(xhttp.statusText);
                }
            };
            xhttp.send(attr.data);
        });
    }

    let Request = function (event, Request, method='POST') {

        // let XMLHttpRequest = (typeof XMLHttpRequest === 'Object') ? XMLHttpRequest : new AppSimXMLHttpRequest;
        //
        let attr = request_params(Request);
        let xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                return attr.callback(attr.format(event, Request, this.responseText))
            }
        };
        xhttp.open(method, attr.endpoint, true);
        xhttp.setRequestHeader('Content-Type', 'application/json');
        xhttp.send(attr.data);
    }

    return {
        Info: page_info,
        Href: get_href,
        Base: get_base,
        Params: get_href_parts,
        unset_param: unset_param,
        get_param: get_param,
        has_param: has_param,
        has_param_value: has_param_value,
        is_plugin: is_plugin,
        encode_query: encode_query,
        request_params: request_params,
        request_promise: request_promise,
        Request: Request
    };
}();
// [App.js][Util.js]
var App = App || {};

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

// String.format
// String.format('I am {0}, you are {1}, but not {0} {2}', 'here', 'there');
if (!String.format) {
  String.format = function(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    return format.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

App.Util = function () {

    isFunction = function (fname) {
        return (typeof window[fname] === 'function');
    }

    isObject = (obj) => {
        return Object.prototype.toString.call(obj) === '[object Object]';
    };

    str_replace_map = (map, str) => {
        if (typeof(str) !== 'string') { return str; };
        Object.keys(map).map(k => str=str_replace(str,k,map[k]));
        return str;
    };

    str_replace = (s, k, v) => {
        if (typeof(s) !== 'string') { return s; };
        k='%{'+k+'}';
        let re = new RegExp(k, 'g');
        return s.replace(re,v);
    };

    merge = (...args) => {
        // create a new object
        let target = {};

        // deep merge the object into the target object
        const merger = (obj) => {
            for (let prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    if (Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                        // if the property is a nested object
                        target[prop] = merge(target[prop], obj[prop]);
                    } else {
                        // for regular property
                        target[prop] = obj[prop];
                    }
                }
            }
        };

        // iterate through all objects and
        // deep merge them with target
        for (let i = 0; i < args.length; i++) {
            merger(args[i]);
        }

        return target;
    };

    call_function_by_name = function (name, args) {
        return eval(name)(args);
    };

    copy = function (obj) {
        return merge(obj);
    };

    ucfirst = function (str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    };

    keyvalues = function (obj, format=null) {
        let arr = [];
        for (let key in obj) {
            if (obj[key].hasOwnProperty('value')) {
                arr[ucfirst(key)] = obj[key].value;
            }
        }
        return arr;
    };

    executeByName = function(name, context) {
        var args, func, i, j, k, len, len1, n, normalizedName, ns;
        if (context == null) {
            context = window;
        }
        args = Array.prototype.slice.call(arguments, 2);
        normalizedName = name.replace(/[\]'"]/g, '').replace(/\[/g, '.');
        ns = normalizedName.split(".");
        func = context;
        for (i = j = 0, len = ns.length; j < len; i = ++j) {
            n = ns[i];
            func = func[n];
        }
        ns.pop();
        for (i = k = 0, len1 = ns.length; k < len1; i = ++k) {
            n = ns[i];
            context = context[n];
        }
        if (typeof func !== 'function') {
            throw new TypeError('Cannot execute function ' + name);
        }
        return func.apply(context, args);
    }

    return {
        call_function_by_name: call_function_by_name,
        copy: copy,
        executeByName: executeByName,
        isFunction: isFunction,
        isObject: isObject,
        keyvalues: keyvalues,
        merge: merge,
        str_replace_map: str_replace_map,
        str_replace: str_replace,
        ucfirst: ucfirst
    };

}();
// [App.js][Model.js]
var App = App || {};

App.Model = function () {
    let hold = function () {
        return true;
    };

    return {
        hold: hold
    };

}();
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
// [App.js][Form.js]
var App = App || {};

App.Form = function () {
    let hold = function () {
        return true;
    }

    return {
        hold: hold
    };

}();
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
