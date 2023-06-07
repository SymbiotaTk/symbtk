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
