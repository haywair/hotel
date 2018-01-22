define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order_bnb/index',
                    add_url: '',
                    edit_url: 'order_bnb/edit',
                    del_url: 'order_bnb/del',
                    more_url:'order_bnb/more',
                    multi_url: 'order_bnb/multi',
                    table: 'order_bnb',
                }
            });

            var table = $("#table");
            var landlord = $('#landlord').val();
            var state = {0:'已取消',10:'订单生成',20:'审核完成',30:'支付成功',40:'密码已发送',50:'已完成'};
            var sourceName = JSON.parse($('#sourceData').val());
            sourceName[0] = '平台';
            var cityData = Controller.api.citylist();
            var provinceData = Controller.api.provincelist();
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url+'?landlord='+landlord,
                pk: 'id',
                sortName: 'a.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'status', title: __('Status'),formatter:Controller.api.formatter.status,searchList:state},
                        {field: 'order_sn', title: __('订单编号'),operate: 'LIKE'},
                        {field: 'name', title: __('民宿'),operate: 'LIKE'},
                        {field: 'user_nickname', title: __('用户'),operate: 'LIKE'},
                        {field: 'province_code', title: __('省(直辖市)'),formatter: Controller.api.formatter.province,searchList:provinceData},
                        {field: 'city_code', title: __('市(区县)'),formatter: Controller.api.formatter.city,searchList:cityData},
                        {field: 'in_date', title: __('入住日期')},
                        {field: 'out_date', title: __('离开日期')},
                        {field: 'night', title: __('入住天数')},
                        {field: 'contact_name', title: __('联系人')},
                        {field: 'contact_mobile', title: __('联系电话'),operate: 'LIKE'},
                        {field: 'order_time', title: __('下单时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'verify_time', title: __('审核时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'pay_time', title: __('支付时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'finish_time', title: __('完成时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'pay_total', title: __('支付金额'),operate:false},
                        {field: 'order_original_total', title: __('订单原价'),operate:false},
                        {field: 'order_total', title: __('订单金额')},
                        {field: 'order_actually_total', title: __('实际价格(含赔偿金)'),operate:false},
                        {field: 'source_name', title: __('订单来源'),formatter: Controller.api.formatter.source_name,searchList:sourceName},
                        {field: 'replaced_order_sn', title: __('平台单号'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Controller.api.changeCity();
        },
        more:function(){
            Fast.api.refresh();
            Controller.api.more.addonClean();
            Controller.api.more.orderClean();
            Controller.api.more.savePay();
            Controller.api.bindevent();
        },
        add:function(){
            Fast.api.refresh();
            Fast.api.getCitys('province','city');
            Controller.api.getBnb();
            Controller.api.bindevent();
        },
        edit:function(){
            Controller.api.bindevent();
        },
        savepay:function(){
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
                    var colorArr = {"10": 'success', "0": 'grey', "20": 'danger', "30": 'danger', "40": 'danger', "50": 'danger'};
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    switch(value){
                        case '10':
                            value = '订单生成';
                            break;
                        case '0':
                            value = '已取消';
                            break;
                        case '20':
                            value = '审核完成';
                            break;
                        case '30':
                            value = '支付成功';
                            break;
                        case '40':
                            value = '密码已发送';
                            break;
                        case '45':
                            value = '部分入住完成';
                            break;
                        case '50':
                            value = '订单完成';
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },

                datetime:function(value,row,index){
                    return new Date(parseInt(value) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
                },
                city:function(value,row,index){
                    var html = '<span class="text">'+row.city_name+'</span>';
                    return html;
                },
                source_name:function(value,row,index){
                    var html;
                    if(row.source_name) {
                        html = '<span class="text">' + row.source_name + '</span>';
                    }else{
                        html = '<span class="text">平台</span>';
                    }
                    return html;
                },
                province:function(value,row,index){
                    var html = '<span class="text">'+row.province_name+'</span>';
                    return html;
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                  /*  buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});*/
                    if(row.status == 10 || row.status == 20){
                        buttons.push({name: 'edit', text:'修改价格', classname: 'btn btn-xs btn-success btn-editprice'});
                    }
                    if((parseInt(row.replaced_source_id) > 0) && (row.status == 30)){
                        buttons.push({name: 'cancel', text:'取消订单', classname: 'btn btn-xs btn-danger btn-cancelone'});
                    }
                    buttons.push({name: 'more', text:'详情', classname: 'btn btn-xs btn-primary btn-moreone btn-addtabs'});
                   /* buttons.push({name: 'del', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-delone'});*/
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
                    'click .btn-cancelone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.ajax({url:'order_bnb/cancelorder',data:{order_sn:row.order_sn}});
                    },
                    'click .btn-editprice':function(e,value,row,index){
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        Layer.prompt({
                            offset:[top,left],
                            formType: 2,
                            value: '',
                            title: '修改订单总价'
                        }, function(value, index, elem){
                            ruleVal = '([1-9]\d*(\.\d*[1-9])?)|(0\.\d*[1-9])';
                            var re = new RegExp(ruleVal);
                            if(!re.test(value)){
                                Toastr.error('请输入大于0的数字');
                            }else{
                                Fast.api.ajax({url:'order_bnb/editprice',data:{order_sn:row.order_sn,order_total:value}});
                                layer.close(index);
                            }

                        });
                    }

                }
            },
            more:{
                orderClean:function(){
                    $('.orderClean').click(function(){
                        var order_sn = $(this).attr('data-order-sn');
                        var url = 'order_bnb/orderClean?order_sn='+order_sn;
                        Fast.api.open(url,'用户预购保洁信息',{area:['1200px','500px']});
                    });
                },
                addonClean:function(){
                    $('.addonClean').click(function(){
                        var order_sn = $(this).attr('data-order-sn');
                        var url = 'order_bnb/addonClean?order_sn='+order_sn;
                        Fast.api.open(url,'订单附加保洁订单信息',{area:['1200px','500px']});
                    });
                },
                savePay:function(){
                    $('.savePay').click(function(){
                        var order_sn = $(this).attr('data-order-sn');
                        var url = 'order_bnb/savePay?order_sn='+order_sn;
                        Fast.api.open(url,'增加收款信息',{area:['1200px','500px']});
                    });
                }

            },
            provincelist:function(){
                var jsonData = {};
                $.ajax({
                    type:'get',
                    url:"ajax/getProvinces",
                    data:{},
                    async: false,
                    dataType:'json',
                    success:function(result){
                        if(result.code == 1){
                            jsonData = result.data;
                        }
                    }
                });
                return jsonData;
            },
            citylist:function(provinceCode){
                var jsonData = {};
                provinceCode = provinceCode?provinceCode:110000;
                $.ajax({
                    type: 'get',
                    url: "ajax/getCitys",
                    data: {province: provinceCode},
                    async: false,
                    dataType: 'json',
                    success: function (result) {
                        if (result.code == 1) {
                            jsonData = result.data;
                        }
                    }
                });
                return jsonData;

            },
            changeCity:function(){
                $('select[name="province_code"]').change(function(){
                    var provinceCode = $('select[name="province_code"]').find("option:selected").attr('value');
                    var cityJson = Controller.api.citylist(provinceCode);
                    var html = '';
                    html += '<option value="">请选择</option>';
                    $.each(cityJson,function(i,item){
                        html += '<option value="'+i+'">'+item+'</option>';
                    });
                    $('select[name="city_code"]').html(html);
                });
            },
            getBnb:function(){
                $('#city').change(function(){
                    var cityCode = $(this).val();
                    $.ajax({
                        type: 'get',
                        url: "ajax/getBnb",
                        data: {city:cityCode},
                        async: false,
                        dataType: 'json',
                        success: function (result) {
                            if (result.code == 1) {
                                var html = '<option value="">请选择民宿</option>';
                                $.each(result.data,function(i,item){
                                    html+= '<option value="'+i+'">'+item+'</option>';
                                })
                                $('#bnb_id').html(html);
                                $('.bnb-area').show();
                            }else{
                                Toastr.error(result.msg);
                            }
                        }
                    });
                });
            }

        }

    };
    return Controller;
});