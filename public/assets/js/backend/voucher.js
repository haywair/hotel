define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    xxx_url: 'voucher/index',
                    index_url: 'voucher/index',
                    add_url: 'voucher/add',
                    edit_url: 'voucher/edit',
                    del_url: 'voucher/del',
                    more_url:'voucher/more',
                    table: 'voucher',
                }
            });

            var table = $("#table");
            var type = $('#type').val();
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url+'?type='+type,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'weigh', title: __('Weigh'),operate:false},
                        {field: 'type', title: '申请类型',formatter: Controller.api.formatter.type,searchList:{1:"民宿",2:'保洁'}},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常",2:'已处理'}},
                        {field: 'name', title: __('名称')},
                        {field: 'price_over', title: __('优惠条件')},
                        {field: 'price_discount', title: __('优惠金额')},
                        {field: 'max_counter', title: __('发行数量'),},
                        {field: 'user_get_counter', title: __('已发放数量')},
                        {field: 'user_use_counter', title: __('已使用数量')},
                        {field: 'start_time', title: __('开始时间'), formatter: Controller.api.formatter.datetime,operate:false},
                        {field: 'end_time', title: __('结束时间'), formatter: Controller.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
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
        more:function(){
            Fast.api.refresh();
            Controller.api.bindevent();
        },
        config:function(){
            Controller.api.config.setconfig();
            Controller.api.config.postconfig();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter : {
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
                        case '2':
                            value = "已处理";
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },
                type:function(value,row,index){
                    value = (value == 1?'保洁':'房东');
                    var html = '<span class="text">'+value+'</span>';
                    return html;
                },

                datetime:function(value,row,index){
                    return value ? Moment(parseInt(value) * 1000).format("YYYY-MM-DD") : __('None');
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    /*buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});*/
                    buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-editone'});
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
                    'click .btn-xxxone': function (e, value, row, index) {
                        e.stopPropagation();
                        alert('xxx');
                    },
                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.open(options.extend.edit_url + (options.extend.edit_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk], __('Edit'), $(this).data() || {});

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
                getMaps:function(){
                    var lng = $('#lng').val();
                    var lat = $('#lat').val();
                    if((parseFloat(lng) >0) && (parseFloat(lat) >0) ){
                        district = {lat:lat,lng:lng};
                        var map = Fast.api.showMaps(district,'maps');
                        Fast.api.setPosition(district,map,'lat','lng');
                    }
                },
                landlordPreview:function(){
                    var id = $('#ids').val();
                    $('#landlord-preview').click(function(){
                        var url = 'landlordinfo/preview/uid/' +id;
                        Fast.api.open(url, '查看房东信息',{area:['1000px','600px']});
                    });
                },
                bindbnb:function(){
                    var id = $('#ids').val();
                    $('#bnb-bind').click(function(){
                        var url = 'landlordinfo/bindbnb/uid/' +id;
                        Fast.api.open(url, '绑定民宿',{area:['1000px','600px']});
                    });
                },
                cleanerPreview:function(){
                    var id = $('#ids').val();
                    $('#cleaner-preview').click(function(){
                        var url = 'cleaninfo/preview/uid/' +id;
                        Fast.api.open(url, '查看保洁员信息',{area:['1000px','600px']});
                    });
                }

            },
            config:{
                setconfig:function(){
                    $('.nav > li > a').click(function(){
                        var id = $(this).attr('href').substring('1');
                        var selectVal = $('#'+id+'_val').val();
                        $('.nav li').removeClass('active');
                        $('.nav li').addClass('bg-color');
                        $('.voucher-con').hide();
                        $('#'+id).show();
                        $(this).parent().addClass('active');

                    })
                },
                postconfig:function(){
                    $('.btn').click(function(){
                        var data_id = $(this).attr('data-id');
                        var config_val = $('#'+data_id+'-input').val();
                        if(!config_val){
                            layer.msg('请选择优惠券');return false;
                        }
                        Fast.api.ajax({url:'voucher/config',data:{key:data_id,val:config_val}});
                    });
                }
            }

        }

    };
    return Controller;
});