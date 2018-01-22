define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'area/index',
                    add_url: 'area/add',
                    edit_url: 'area/edit',
                    del_url: 'area/del',
                    multi_url: 'area/multi',
                    table: 'area',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'status', title: __('Status'), formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常"}},
                        {field: 'weigh', title: __('Weigh'),visible: false, operate:false},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,visible: false, operate:false},
                        {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime,visible: false, operate:false},
                        {field: 'province_name', title: __('Province_name'),operate:'LIKE'},
                        {field: 'province_code', title: __('Province_code'), operate:false},
                        {field: 'city_name', title: __('City_name'),operate:'LIKE'},
                        {field: 'city_code', title: __('City_code'), operate:false},
                        {field: 'county_name', title: __('County_name'),visible: false, operate:false},
                        {field: 'county_code', title: __('County_code'),visible: false, operate:false},
                        {field: 'map_lng', title: __('Map_lng'), operate:false},
                        {field: 'map_lat', title: __('Map_lat'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter : {
                status: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    var colorArr = {"1": 'success', "0": 'grey', "-1": 'danger', "2": 'info'};

                    value = value.toString();
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    value = value.charAt(0).toUpperCase() + value.slice(1);
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> ' + __('Status '+value) + '</span>';
                    return html;
                }
            }
        }
    };
    return Controller;
});