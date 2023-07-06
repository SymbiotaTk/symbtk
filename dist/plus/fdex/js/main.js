App.Fdex = function () {
    const __NAMESPACE__ = 'App.Fdex';

    let namespace = function () {
        let info = {
            parent: this,
            name: arguments.callee.name
        };
        return __NAMESPACE__+'.';
    };

    let collections_id = function () {
        return 'collections';
    };

    let content_id = function () {
        return 'post-content';
    };

    let default_func = function () {
        return 'select_collection';
    };

    let fetch_timeout = async function () {
        return 1000;
    };

    let form_id = function () {
        return 'fdex-form';
    };

    let input_id = function () {
        return 'fdex-id';
    };

    let resource_class_id = function () {
        return 'resource';
    };

    let result_id = function () {
        return 'fdex-result';
    };

    let select_id = function () {
        return 'fdex-select';
    };

    let super_content_id = function () {
        return 'content';
    };

    let instructions = function (e) {
        document.getElementById(super_content_id())
            .appendChild(document.getElementById(content_id()));
        _Call(e, Callback);
    };

    let update_user_info = function (d) {
        let con = document.getElementsByClassName(resource_class_id());

        let uinfo = (d.Content && d.Content.user)
            ? d.Content.user
            : false;

        let uid = (uinfo && uinfo.uid)
            ? uinfo.uid
            : false;

        let greeting = (uid)
            ? String.format('Hello {0} (logged in at: {1})', uinfo.firstname, uinfo.lastlogindate)
            : false
        ;

        let login = (greeting)
            ? App.Html.m('div', {'class':'login'}, greeting)
            : App.Html.m('div', { 'class': 'error login' }, 'Not logged in')
        ;

        con[0].childNodes[0].appendChild(login);
        return false;
    };

    let Callback = function (d) {
        let func = (d.Content.function)
            ? namespace()+d.Content.function
            : namespace()+default_func();

        update_user_info(d);
        App.Html.node_appendChildById(content_id(), App.Util.call_function_by_name(func, d));
        return true;
    };

    let get = function (key) {
        let ns = __NAMESPACE__;
        window.__store__ = window.__store__ || {};
        window.__store__[ns] = window.__store__[ns] || {};
        return window.__store__[ns][key];
    };

    let getById = function (id) {
        let col = get(collections_id());
        return col[id] || {};
    };

    let set = function (key, value) {
        let ns = __NAMESPACE__;
        window.__store__ = window.__store__ || {};
        window.__store__[ns] = window.__store__[ns] || {};
        window.__store__[ns][key] = value;
        let store = window.__store__;

        return false;
    };

    let request = function (e, endpoint, callback) {
        let info = App.Http.Info();
        let tmp = App.Http.request_params();
        tmp.endpoint = endpoint;
        tmp.callback = callback;
        tmp.format = function (e, r, d) { return JSON.parse(d); };
        tmp.data = JSON.stringify(info.params);

        App.Http.Request(e, tmp);
    };

    let index_collections_by_collid = function (collections) {
        let idx = {};
        collections.forEach(function (el) {
            idx[el.collid] = {
                collectionName: el.collectionName,
                institutionCode: el.institutionCode
            };
        });
        return idx;
    };

    let sort_obj_by_attribute = function (obj, attr) {
        obj.sort((a, b) => {
            let ca = a[attr].toLowerCase(),
                cb = b[attr].toLowerCase();

            if (ca < cb) {
                return -1;
            }
            if (ca > cb) {
                return 1;
            }
            return 0;
        });

        return obj;
    };

    let select_collection = function(d) {
        let collections = d.Content.data.collections;
        set(collections_id(), index_collections_by_collid(collections));
        collections = sort_obj_by_attribute(collections, 'collectionName');

        let div = m('div', { class: 'fdex' });
        // let result = m('div', { class: 'fdex_result', id: result_id() });

        div.appendChild(embed_form(collections));
        // div.appendChild(result);

        return div;
    };

    let select_collection_embed = function (d) {
        let collections = (d.Content.data && d.Content.data.collections) ? d.Content.data.collections : false;

        if (! collections) { return false; }
        set(collections_id(), index_collections_by_collid(collections));
        collections = sort_obj_by_attribute(collections, 'institutionCode');
        let div = m('div', { class: 'fdex' });
        let button = m('button', { 'data-modal': embed_modal_id()}, 'Fdex meta-data');
        button.addEventListener('click', embed_modal_open);
        div.appendChild(button);
        div.appendChild(embed_modal(embed_form(collections)));

        return div;
    };

    let embed_form = function (collections) {
        let div = m('div', {});
        let form = m('form', { id: form_id() });

        let h5 = m('h5', {}, 'Select a collection: ');
        form.appendChild(h5);
        form.appendChild(embed_form_select(collections));

        let bdiv = m('div', {});
        let span = m('span', {});
        let button = m('button', {}, 'Find');
        button.addEventListener('click', action_find_record);
        let reset = m('button', { type: 'button'}, 'Clear');
        reset.addEventListener('click', action_clear_all);

        span.appendChild(button);
        span.appendChild(reset);
        bdiv.appendChild(span);

        form.appendChild(bdiv);

        let result = m('div', { class: 'fdex_result', id: result_id() });
        div.appendChild(form);
        div.appendChild(result);
        return div;
    };

    let embed_form_select = function (collections) {
        let select = m('select', { id: select_id() });
        let op = m('option', { value: -1 });
        select.appendChild(op);
        collections.forEach(function (el) {
            let name = el.collectionName;
            let inst = el.institutionCode;
            op = m('option', { value: el.collid }, name+" ["+inst+"]");
            select.appendChild(op);
        });
        select.addEventListener('change', action_selected_collection);
        return select;
    };

    let filter_select_options_reset = function () {
        let select = document.getElementById(select_id());
		var options = select.options;
		for (var i = 0; i < options.length; i++) {
			options[i].disabled = false;
        }
        return false;
    };

    let filter_select_options = function (e) {
        let select = document.getElementById(select_id());
		var text = e.target.value;
		var options = select.options;

		for (var i = 0; i < options.length; i++) {
			var option = options[i];
			var optionText = option.text;
			// lowercase comparison for case-insensitivity
			var lowerOptionText = optionText.toLowerCase();
			var lowerText = text.toLowerCase();
			var regex = new RegExp("^" + text, "i");
			var match = optionText.match(regex);
			var contains = lowerOptionText.indexOf(lowerText) != -1;

			// enabled / disbaled option matched to text
			option.disabled = match || contains ? false: true ;
        }
        return false;
    };

    let embed_form_dropdown = function (collections) {
        let div = m('div', { class: 'dropdown' });
        let button = m('button', { class: 'dropbtn' }, 'Select a collection');
        button.addEventListener('click', embed_form_dropdown_list);
        let divc = m('div', { id: dropdown_id(), class: 'dropdown-content' });

        let divi = m('div', { class: 'input_with_icon' });
        let input = m('input', { type: 'text', placeholder: 'Type to filter list...', id: dropdown_input_id() });
        let i = m('i', { class: 'fa fa-search', 'aria-hidden': true });
        input.addEventListener('keyup', embed_form_dropdown_filter);
        divi.appendChild(input);
        divi.appendChild(i);
        divc.appendChild(divi);

        let a;
        collections.forEach(function (el) {
            let name = el.collectionName;
            let inst = el.institutionCode;
            a = m('a', { tabindex: -1, 'data-id': el.collid }, name+" ["+inst+"]");
            a.addEventListener('click', embed_form_dropdown_selected);
            divc.appendChild(a);
        });

        div.appendChild(button);
        div.appendChild(divc);
        return div;
    };

    let embed_form_dropdown_list = function (e) {
        e.preventDefault();
        document.getElementById(dropdown_content_id()).classList.toggle("show");
        return false;
    };

    let embed_form_dropdown_filter = function (d) {
        var input, filter, ul, li, a, i;
        input = document.getElementById(dropdown_input_id());
        filter = input.value.toUpperCase();
        div = document.getElementById(dropdown_id());
        a = div.getElementsByTagName('a');
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
        return false;
    };

    let embed_form_dropdown_selected = function (e) {
        document.getElementById(dropdown_id()).classList.toggle("hide");
        return false;
    };

    let embed_modal = function (content) {
        let div = m('div', { id: embed_modal_id(), class: 'modalbox-modal' });
        let divc = m('div', { class: 'modalbox-modal-content' });
        let span = m('span', { id: 'modalbox-close', class: '-close' }, '✖');
        span.addEventListener('click', embed_modal_close);
        divc.appendChild(span);
        divc.appendChild(content);
        div.appendChild(divc);
        return div;
    };

    let embed_modal_close = function (e) {
        var modal = document.getElementById(embed_modal_id());
        modal.style.display = 'none';
        return false;
    };
    let embed_modal_open = function (e) {
        let attr = 'data-modal';
        let label = (e.target.attributes[attr])
            ? e.target.attributes[attr].value
            : false
        ;

        return (label) ? openmodal(label) : false;
    };

    let action_clear_all = function (e) {
        e.preventDefault();

        action_clear_result();

        filter_select_options_reset();

        let form = document.getElementById(form_id());
        form.reset();

    };

    let action_clear_result = function (e) {
        let res = document.getElementById(result_id());
        res.innerHTML = '';
        return false;
    };

    let spinningpixels = function() {
        return `
    <div class="cell">
<div class="card">
  <span class="spinning-pixels-loader">Fetching data&#8230;</span>
  <h3 style="padding-top: 80px;">Fetching data ... <span class='seconds-counter'></span></h3>
</div>
    </div>
        `;
    }

    let action_hourglass = async function (e) {
        action_clear_result();
        let res = document.getElementById(result_id());
        let hourglass = action_hourglass_container(spinningpixels());
        res.appendChild(hourglass);

        let tout = await fetch_timeout();
        await new Promise(r => setTimeout(r, tout));
        return false;
    };

    let action_hourglass_container = function(content) {
        let div = document.createElement('div');
        div.className = 'spinner';
        div.appendChild(action_hourglass_grid(content));
        return div;
    }

    let action_hourglass_grid = function(content) {
        let div = document.createElement('div');
        div.className = 'grid';
        div.insertAdjacentHTML('afterbegin', content);
        return div;
    }

    let action_find_record = async function (e) {
        e.preventDefault();
        let info = App.Http.Info();
        let url = info.resource_url;

        let res = document.getElementById(result_id());
        let collid = document.getElementById(select_id()).value;

        if (! collid) { return false; }
        await action_hourglass();

        if (collid && collid != -1) {
            let m = getById(collid);
            url = url + '/' + collid;
        }

        url = url + ':json';
        request(e, url, format_result);
        return false;
    };

    let action_selected_collection = function(e) {
        action_find_record(e);
        return false;
    };

    let format_error = function (d) {
        let display = document.getElementById(result_id());
        let msg = (d.Content.result && d.Content.result.message) ? d.Content.result.message : 'Please submit a valid occid or catalogNumber.';
        display.innerHTML = msg;

        return false;
    };

    let format_filter = function (i) {
        let def = {
            'CollectionName': true,
            'InstitutionCode': true,
            'CollectionCode': true,
            'CollType': true,
            'ManagementType': true,
            'dwcaUrl': true,
            'initialTimestamp': true,
            'dataLastModified': true,
            'totalRecords': 'Total Records',
            'totalRecordsTaxonIdentified' : 'Records with Scientific Names Linked to Thesaurus',
            'totalUniqueTaxonIdentified' : 'Unique Scientific Names Linked to Thesaurus',
            'totalRecordsTaxonNotIdentified' : 'Records with Scientific Names Not Linked to Thesaurus',
            'totalRecordsTaxonNULL' : 'Records with No Scientific Names',
            'totalUniqueTaxonNotIdentifiedUnrecognizedNotNULL' : 'Unique Problematic Scientific Names within Records',
            'totalUniqueTaxonNotIdentifiedExistsInThesaurus' : 'Records with Scientific Names That Can Be Linked to Thesaurus',
            'totalUniqueTaxonNotIdentifiedExistsInFdex' : 'Unique Scientific Names That Can Be Linked to Thesaurus, <br />after Fdex import',
            'NewTaxaFromFdex' : true
        };

        return (def.hasOwnProperty(i))
            ? ((typeof def[i] === 'string')
                ? def[i]
                : i)
            : false;
    };

    let format_result = function (d) {
        let meta = d.Content.data;
        let res = d.Content.data.result;

        if (! res || res.message) { return format_error(d); }

        let id = res.CollID;

        let display = document.getElementById(result_id());
        display.innerHTML = '';

        let span = m('span', {});
        let p = m('p', {});
        let link = m('a', { href: portal_instance_url(id), target: '_blank' }, 'Taxonomic name cleaner');
        p.appendChild(link);
        span.appendChild(p);

        let tab = m('table', {});
        let thead = m('thead', {});
        let tbody = m('thead', {});
        let thk = m('th', {}, 'key');
        let thv = m('th', {}, 'value');

        let tr, tdk, tdv;

        Object.keys(res).forEach(function (i) {
            let f = format_filter(i);
            if (f) {
                tr = m('tr', {});
                tdk = m('td', {}, f);
                tdv = m('td', {}, res[i]);
                tr.appendChild(tdk);
                tr.appendChild(tdv);
                tbody.appendChild(tr);
            }
        });

        thead.appendChild(thk);
        thead.appendChild(thv);

        tab.appendChild(thead);
        tab.appendChild(tbody);

        display.appendChild(span);
        display.appendChild(tab);

        let dn = m('i', { class: 'fa fa-download', 'aria-hidden': 'true', 'data-value': result_id(), 'data-filename': downloadFileTsv(id), 'data-type': 'text/tsv' }, ' Download as tab delimited file. [.tsv]');
        dn.addEventListener('click', action_download_file);
        display.appendChild(dn);

        return false;
    };

    let portal_instance_url = function (id) {
        let info = App.Http.Info();
        return '/portal/collections/cleaning/taxonomycleaner.php?collid='+id;
    };

    let action_download_file = function (e) {
        let id = e.target.attributes['data-value'].value;
        let fn = e.target.attributes['data-filename'].value;
        let type = e.target.attributes['data-type'].value;
        let res = document.getElementById(id);

        let rows = res.getElementsByTagName('tr');
        let tsv = tableToTsv(rows);

        downloadFile(fn, tsv, type);

        return false;
    };

    let tableToTsv = function (rows) {
        let tsv_head = [];
        let tsv_data = [];
	    for (var i = 0; i < rows.length; i++) {
		    let cols = rows[i].querySelectorAll('td');

            tsv_head.push(cols[0].innerHTML);
            tsv_data.push(cols[1].innerHTML);
        }

        let tsv_head_row = tsv_head.join('\t');
        let tsv_data_row = tsv_data.join('\t');

        return [ tsv_head_row, tsv_data_row ].join('\n');
    };

    let tableToCSV = function () {

	    // Variable to store the final csv data
	    var csv_data = [];

	    // Get each row data
	    var rows = document.getElementsByTagName('tr');
	    for (var i = 0; i < rows.length; i++) {

		    // Get each column data
		    var cols = rows[i].querySelectorAll('td,th');

		    // Stores each csv row data
		    var csvrow = [];
		    for (var j = 0; j < cols.length; j++) {

			    // Get the text data of each cell of
			    // a row and push it to csvrow
			    csvrow.push(cols[j].innerHTML);
		    }

		    // Combine each column value with comma
		    csv_data.push(csvrow.join(","));
	    }
	    // combine each row data with new line character
	    csv_data = csv_data.join('\n');

	    /* We will use this function later to download
	        the data in a csv file downloadCSVFile(csv_data);
	    */
    };

    let downloadFileTsv = function (id) {
        return 'myco-fdex-'+id+'.tsv';
    };

    let downloadFile = function(fn, data, type) {

	    File = new Blob([data], { type: type });

	    var temp_link = document.createElement('a');

	    temp_link.download = fn;
	    var url = window.URL.createObjectURL(File);
	    temp_link.href = url;

	    temp_link.style.display = "none";
	    document.body.appendChild(temp_link);

	    // Automatically click the link to trigger download
	    temp_link.click();
	    document.body.removeChild(temp_link);
    };

    return {
        instructions: instructions,
        select_collection: select_collection,
    };
}();

App.Fdex.instructions(event);
