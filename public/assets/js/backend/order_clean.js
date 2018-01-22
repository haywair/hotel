define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index:function(){
            var indexUrl = 'order_clean/index';
            Controller.displayindex(indexUrl);
        },
        progress:function(){
            var indexUrl = 'order_clean/progress';
            Controller.displayindex(indexUrl);
        },
        cleanerorder:function(){
            var indexUrl = 'order_clean/cleanerOrder';
            Controller.displayindex(indexUrl);
        },
        displayindex: function (indexUrl) {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: indexUrl,
                    add_url: '',
                    edit_url: 'order_clean/edit',
                    del_url: 'order_clean/del',
                    more_url:'order_clean/more',
                    multi_url: 'order_clean/multi',
                    table: 'order_clean',
                }
            });
            var table = $("#table");
            var cleaner = $('#cleaner').val();
            var type = $('#type').val();
            var state = {0:'已取消',10:'订单生成',20:'审核完成',30:'支付成功',40:'密码已发送',50:'已完成'};
            var cityData = Controller.api.citylist();
            var provinceData = Controller.api.provincelist();
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url+'?cleaner='+cleaner+'&type='+type,
                pk: 'id',
                sortName: 'a.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'status', title: __('Status'),formatter:Controller.api.formatter.status,searchList:state},
                        {field: 'order_sn', title: __('订单编号'),operate: 'LIKE'},
                        {field: 'name', title: __('民宿'),operate: 'LIKE'},
                        {field: 'province_code', title: __('省(直辖市)'),formatter: Controller.api.formatter.province,searchList:provinceData},
                        {field: 'city_code', title: __('市(区县)'),formatter: Controller.api.formatter.city,searchList:cityData},
                        {field: 'contact_name', title: __('联系人'),operate:false},
                        {field: 'contact_mobile', title: __('联系电话'),operate: 'LIKE'},
                        {field: 'room_space', title: __('房间面积'),operate: 'LIKE'},
                        {field: 'fee_clean', title: __('价格'),operate: 'LIKE'},
                        {field: 'order_time', title: __('下单时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'verify_time', title: __('审核时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Controller.api.changeCity();
        },
        more:function(){
            /*Controller.api.more.finishClean();*/
            Fast.api.refresh();
            Controller.api.more.toCleaner();//分配保洁员
            Controller.api.more.verifyCleanPhoto();
            Controller.api.bindevent();
        },
        addcomparephoto:function(){
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
                        case '-1':
                            value = '已删除';
                            break;
                        case '0':
                            value = '隐藏';
                            break;
                        case '10':
                            value = '等待保洁';
                            break;
                        case '15':
                            value = '分配保洁人员';
                            break;
                        case '20':
                            value = '保洁人员接单';
                            break;
                        case '30':
                            value = '保洁完成';
                            break;
                        case '40':
                            value = '完成审核,费用发放';
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
                    var html="";
                    if(row.city_name) {
                        var html = '<span class="text">' + row.city_name + '</span>';
                    }else{
                        html = '未填写';
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
                  /*  buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});
                    buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-editone'});*/
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
                    }
                }
            },
            more:{
                verifyCleanPhoto:function(){
                    $('.btn-verify').click(function(){
                        var photoId = $(this).attr('data-id');
                        var state = $(this).attr('data-state');
                        Fast.api.ajax({url:'order_clean/verifyCleanPhoto',data:{id:photoId,status:state}});
                    });
                },
               /* finishClean:function(){
                    //完成保洁
                    $('.finishClean').click(function(){
                        var order_sn = $(this).attr('data-order-sn');
                        var url = 'orderclean/finishClean';
                        Fast.api.ajax({url:url,data:{order_sn:order_sn}});
                    });
                },*/
                toCleaner:function(){
                    //分配保洁员
                    $('.toCleaner').click(function(){
                        var order_sn = $(this).attr('data-order-sn');
                        var url = 'order_clean/allotClean';
                        Fast.api.ajax({url:url,data:{order_sn:order_sn}});
                    });
                }
            }
            ,
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
            }
        }

    };
    return Controller;
});