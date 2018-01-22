define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        landlord:function(){
            var indexUrl = 'withdraw/landlord';
            Controller.index(indexUrl);
        },
        cleaner:function(){
            var indexUrl = 'withdraw/cleaner';
            Controller.index(indexUrl);
        },
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdraw/index',
                    add_url: '',
                    edit_url: 'withdraw/edit',
                    del_url: 'withdraw/del',
                    more_url:'withdraw/more',
                    setUserclass_url:'withdraw/set_userclass',
                    multi_url: 'withdraw/multi',
                    table: 'users',
                }
            });

            var table = $("#table");
            var type = $('#type').val();//用户类型
            var state = {'1':'用户申请','2':'审核通过','3':'审核不通过','4':'提现完成','5':'提现失败'};
            var url = $.fn.bootstrapTable.defaults.extend.index_url;
            if(type){
                url = $.fn.bootstrapTable.defaults.extend.index_url+'?type='+type;
            }
            // 初始化表格
            table.bootstrapTable({
                url: url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常"}},
                        {field: 'finishtime', title:'提现时间', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'user_nickname', title: __('Username')},
                    // {field: 'money_total', title:'可提现金额',operate:false},
                        {field: 'money', title:'提现金额'},
                        {field: 'withdraw_status', title:'提现状态',formatter: Controller.api.formatter.withdrawStatus,searchList:state},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        withdraw: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdraw/index',
                    add_url: '',
                    withdraw_url:'withdraw/withdraw',
                    edit_url: 'withdraw/edit',
                    del_url: 'withdraw/del',
                    more_url:'withdraw/more',
                    setUserclass_url:'withdraw/set_userclass',
                    multi_url: 'withdraw/multi',
                    table: 'users',
                }
            });

            var table = $("#table");
            var type = $('#type').val();//用户类型
            var state = {'1':'用户申请','2':'审核通过','3':'审核不通过','4':'提现完成','5':'提现失败'};
            var url = $.fn.bootstrapTable.defaults.extend.withdraw_url;
            if(type){
                url = $.fn.bootstrapTable.defaults.extend.withdraw_url+'?type='+type;
            }
            // 初始化表格
            table.bootstrapTable({
                url: url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常"}},
                        {field: 'finishtime', title:'提现时间', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'user_nickname', title: __('Username')},
                        {field: 'type', title: __('提现类型'),formatter: Controller.api.formatter.type,searchList:{1:"保洁",2:"房东"}},
                //      {field: 'money_total', title:'可提现金额',operate:false},
                        {field: 'money', title:'提现金额'},
                        {field: 'withdraw_status', title:'提现状态',formatter: Controller.api.formatter.withdrawStatus,searchList:state},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operateT}
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
        setUserclass:function(){
            Controller.api.bindevent();
        },
        more:function(){
            Fast.api.refresh();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter : {
                image:function(value,row,index){
                    html = "<img src='"+value+"' alt='不存在'>";
                    return html;
                },
                status: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    var colorArr = {"1": 'success', "0": 'grey', "-1": 'danger'};
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    switch(value){
                        case '1':
                            value = '正常';
                            break;
                        case '0':
                            value = '隐藏';
                            break;
                        case '-1':
                            value = "已删除";
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },
                withdrawStatus: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    var colorArr = {"1": 'gray', "2": 'success', "3": 'danger','4':'success'};
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    switch(value){
                        case '1':
                            value = '申请';
                            break;
                        case '2':
                            value = '审核通过';
                            break;
                        case '3':
                            value = "审核未通过";
                            break;
                        case '4':
                            value = "提现完成";
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },
                sex:function(value,row,index){
                    value = (value == 1?'男':'女');
                    var html = '<span class="text">'+value+'</span>';
                    return html;
                },
                type:function(value,row,index){
                    value = (value == 1?'保洁':'房东');
                    var html = '<span class="text">'+value+'</span>';
                    return html;
                },
                class:function(value,row,index){
                    value = (value == 1?'普通用户':'已认证用户');
                    var html = '<span class="text">'+value+'</span>';
                    return html;

                },
                landlordOrCleaner:function(value,row,index){
                    value = (value == 1?'是':'否');
                    var html = '<span class="text">'+value+'</span>';
                    return html;
                },
                datetime:function(value,row,index){
                    return new Date(parseInt(value) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});
                    buttons.push({name: 'more', text:'详情', classname: 'btn btn-xs btn-primary btn-moreone btn-addtabs'});
                    if(row.withdraw_status == 1) {
                        buttons.push({ name: 'verify_success',text: '审核通过',classname: 'btn btn-xs btn-success btn-verify-success'});
                        buttons.push({ name: 'verify_fail',text: '审核不通过',classname: 'btn btn-xs btn-danger btn-verify-fail'});
                    }
                    buttons.push({name: 'del', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-delone'});
                    var html = Controller.api.formatter.buttons(buttons,row,options,table,value, index);
                    return html;
                },
                operateT:function(value,row,index){
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    buttons.push({name: 'withdraw', text:'转账', classname: 'btn btn-xs btn-primary btn-withdraw'});
                    var html = Controller.api.formatter.buttons(buttons,row,options,table,value, index);
                    return html;
                },
                buttons:function(buttons,row,options,table,value, index){
                    var html = [];
                    var url, classname, icon, text, title, extend;
                    $.each(buttons, function (i, j) {
                        if (j.name === 'dragsort' && typeof row[Table.config.dragsortfield] === 'undefined') {
                            return true;
                        }
                        if (['add', 'edit', 'del', 'multi', 'dragsort'].indexOf(j.name) > -1 && !options.extend[j.name + "_url"]) {
                            return true;
                        }
                        var attr = table.data("operate-" + j.name);
                        if (typeof attr === 'undefined' || attr) {
                            url = j.url ? j.url : '';
                            if (url.indexOf("{ids}") === -1) {
                                url = url ? url + (url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk] : '';
                            }
                            url = Table.api.replaceurl(url, value, row, table);
                            url = url ? Fast.api.fixurl(url) : 'javascript:;';
                            classname = j.classname ? j.classname : 'btn-primary btn-' + name + 'one';
                            icon = j.icon ? j.icon : '';
                            text = j.text ? j.text : '';
                            title = j.title ? j.title : text;
                            extend = j.extend ? j.extend : '';
                            html.push('<a href="' + url + '" class="' + classname + '" ' + extend + ' title="' + title + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>');
                        }
                    });
                    return html.join(' ');
                }

            },
            events: {
                operate: {
                    'click .btn-moreone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.more_url + (options.extend.more_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-verify-success': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var id = row.id;
                        var state = 'success';
                        Fast.api.ajax({url:'withdraw/verify',data:{id:id,state:state}});
                    },
                    'click .btn-verify-fail': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var id = row.id;
                        var state = 'fail';
                        Fast.api.ajax({url:'withdraw/verify',data:{id:id,state:state}});
                    },
                    'click .btn-delone': function (e, value, row, index) {
                        e.stopPropagation();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var index = Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function () {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    },
                    'click .btn-withdraw': function (e, value, row, index) {
                        e.stopPropagation();
                        var id = row.id;
                        var state = row.state
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.ajax({url:'withdraw/pay',data:{id:id,state:state}});

                    },

                }
            },
            more:{

            }
        }

    };
    return Controller;
});