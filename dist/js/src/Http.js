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
