define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'refund/index',
                    add_url: '',
                    edit_url: 'refund/edit',
                    del_url: 'refund/del',
                    desposit_url:'refund/desposit',
                    more_url:'refund/more',
                    setUserclass_url:'refund/set_userclass',
                    multi_url: 'refund/multi',
                    table: 'refund',
                }
            });

            var table = $("#table");
            var type = $('#type').val();//用户类型
            var url = $.fn.bootstrapTable.defaults.extend.index_url;
            var state = {'-1':'已删除','0':'隐藏','1':'等待退款','8':'退款失败','9':'退款成功'};
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
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:state},
                        {field: 'refund_sn', title: __('退款单号')},
                       /* {field: 'order_sn', title: __('订单号')},*/
                        {field: 'refund_type', title: __('退款类型'),formatter: Controller.api.formatter.type,searchList:{'A':"自动退款",'D':"退换保证金",'B':'民宿订单退款','C':'保洁订单退款'}},
                        {field: 'user_nickname', title: __('Username')},
                        {field: 'pay_sn', title: __('支付单号')},
                        {field: 'pay_amount', title: __('支付金额'),operate:false},
                        {field: 'pay_time', title: __('支付时间'),formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'refund_amount', title: __('退款金额'),operate:false},
                        {field: 'reason', title: __('退款原因'),operate:false},
                        {field: 'refund_id', title: __('交易号')},
                        {field: 'admin', title: __('管理员'),operate:false},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Controller.api.bindevent();
        },
        desposit: function () {
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
                status: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    switch(value){
                        case '1':
                            value = '等待退款';
                            break;
                        case '0':
                            value = '隐藏';
                            break;
                        case '-1':
                            value = "已删除";
                            break;
                        case '8':
                            value = '退款失败';
                            break;
                        case '9':
                            value = '退款成功';
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },
                type:function(value,row,index){
                    switch(value){
                        case 'A':
                            value = '自动退款';
                            break;
                        case 'B':
                            value = '民宿订单退款';
                            break;
                        case 'C':
                            value = "保洁订单退款";
                            break;
                        case 'D':
                            value = "退还保证金";
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text"><i class="fa fa-circle"></i> '+ value+ '</span>';
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
                    if((row.status == 1) && row.refund_type == 'B') {
                        buttons.push({name: 'edit',text: '退款',classname: 'btn btn-xs btn-success btn-editone'
                        });
                    }
                    if(row.refund_type == 'D' && row.status == 1) {
                        buttons.push({name: 'desposit',text: '保证金',classname: 'btn btn-xs btn-success btn-desposit'});
                    }
                    buttons.push({name: 'more', text:'详情', classname: 'btn btn-xs btn-primary btn-moreone btn-addtabs'});
                    buttons.push({name: 'del', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-delone'});
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

                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.open(options.extend.edit_url + '?refund_sn='+row.refund_sn, __('退款'), $(this).data() || {});
                    },
                    'click .btn-desposit': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.open(options.extend.desposit_url + '?refund_sn='+row.refund_sn, __('保证金退款'), $(this).data() || {});
                    },
                    'click .btn-moreone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.more_url + (options.extend.more_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
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
                    }

                }
            },
            more:{

            }
        }

    };
    return Controller;
});