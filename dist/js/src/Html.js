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
