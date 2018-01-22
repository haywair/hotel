define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cleaninfo/index',
                    add_url: '',
                    edit_url: 'cleaninfo/edit',
                    del_url: 'cleaninfo/del',
                    more_url:'cleaninfo/more',
                    setUserclass_url:'users/setUserclass',
                    multi_url: 'cleaninfo/multi',
                    table: 'users',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常"}},
                        {field: 'user_avatar', title: __('头像'),formatter: Controller.api.formatter.image,operate:false},
                        {field: 'user_nickname', title: __('Username')},
                        {field: 'user_sex', title: __('性别'),formatter: Controller.api.formatter.sex,operate:false},
                        {field: 'user_class', title: __('是否认证'),formatter: Controller.api.formatter.class,searchList:{1:"普通用户",2:"认证用户"}},
                        {field: 'user_truename', title: __('Truename')},
                        {field: 'user_mobile', title: __('Mobile')},
                        {field: 'user_idcard_number', title: __('证件号码')},
                        {field: 'is_cleaner', title: __('是否保洁员'),formatter: Controller.api.formatter.landlordOrCleaner,searchList:{1:"是"}},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        preview: function () {
            Controller.api.preview.add();
            Controller.api.preview.edit();
            Controller.api.preview.getMaps();
        },
        billcleanpreview:function(){
            Controller.api.billCleanPreview.subform();
            Controller.api.bindevent();
        },
        add:function(){
            Controller.api.getMaps();
            Controller.api.getCitys();
            Controller.api.bindevent();
        },
        edit: function () {
            var city_code = $('#city').val();
            var address = $('#address').val();
            var lat = $('#lat').val();
            var lng = $('#lng').val();
            Controller.api.getCitys();
            Controller.api.getMaps();
            Fast.api.getLatLng(city_code,address,'maps','lat','lng',lat,lng);
            Controller.api.bindevent();
        },
        more:function(){
            Fast.api.refresh();
            Controller.api.more.getMaps();//获取地图
            Controller.api.more.cleanerPreview();//查看保洁信息
            Controller.api.more.orderCleanPreview();//查看保洁工作订单
            Controller.api.more.billCleanPreview();//查看保洁工作订单
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
                sex:function(value,row,index){
                    value = (value == 1?'男':'女');
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
                    if(row.bid > 0) {
                        buttons.push({name: 'edit',icon: 'fa fa-pencil',classname: 'btn btn-xs btn-success btn-editone'});
                    }
                    buttons.push({name: 'more', text:'详情', classname: 'btn btn-xs btn-primary btn-moreone btn-addtabs'});
                    if(row.user_class == 1) {
                        buttons.push({ name: 'userclass',text: '认证',classname: 'btn btn-xs btn-success btn-userclass'});
                    }
                    if(row.is_order == 1) {
                        buttons.push({ name: 'orderno',text: '不接单',classname: 'btn btn-xs btn-danger btn-order-no'});
                    }else{
                        buttons.push({ name: 'orderyes',text: '接单',classname: 'btn btn-xs btn-success btn-order-yes'});
                    }
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
                        Fast.api.open(options.extend.edit_url + (options.extend.edit_url.match(/(\?|&)+/) ? "&uid=" : "/uid/") + row[options.pk], __('Edit'), $(this).data() || {});
                    },
                    'click .btn-moreone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.more_url + (options.extend.more_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-userclass': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        Fast.api.open(options.extend.setUserclass_url + (options.extend.setUserclass_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk], __('认证'), $(this).data() || {});
                    },
                    'click .btn-order-yes': function (e, value, row, index) {
                        e.stopPropagation();
                        var id = row.id;
                        var state = 'success';
                        Fast.api.ajax({url:'cleaninfo/setOrder',data:{id:id,state:state}});
                    },
                    'click .btn-order-no': function (e, value, row, index) {
                        e.stopPropagation();
                        var id = row.id;
                        var state = 'fail';
                        Fast.api.ajax({url:'cleaninfo/setOrder',data:{id:id,state:state}});
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
                orderCleanPreview:function(){
                    //查看该保洁所属工作订单
                    var userId = $('#ids').val();
                    $('#clean-order-preview').click(function(){
                        var url = 'order_clean/cleanerOrder?cleaner='+userId;
                        Fast.api.open(url,'查看保洁订单',{area:['1000px','500px']});
                    });
                },
                cleanerPreview:function(){
                    var id = $('#ids').val();
                    $('#cleaner-preview').click(function(){
                        var url = 'cleaninfo/preview/uid/' +id;
                        Fast.api.open(url, '查看保洁员信息');
                    });
                },
                billCleanPreview:function(){
                    //查看该保洁所属结算信息
                    var userId = $('#ids').val();
                    $('#bill-clean-preview').click(function(){
                        var url = 'cleaninfo/billCleanPreview?cleaner_id='+userId;
                        Fast.api.open(url,'查看房保洁结算信息',{area:['1000px','500px']});
                    });
                }
            },
            billCleanPreview:{
                subform:function(){
                    $('.btn-submit').click(function(){
                        var uid = $('#uid').val();
                        var bnbName = $('#name').val();
                        var bill_date = $('#bill_date').val();
                        var order_sn = $('#order_sn').val();
                        var html = '';
                        $.ajax({
                            type:'get',
                            url:'cleaninfo/billCleanPreview',
                            data:{cleaner_id:uid,name:bnbName,bill_date:bill_date,order_sn:order_sn},
                            dataType:'json',
                            success:function(result){
                                var  data = result.rows.data;
                                $.each(data,function(i,item){
                                    html += '<tr><td><input class="check-btn" id="check-'+item.id+'}" type="checkbox" name="ids[]"  value="';
                                    html += item.id+'"></td><td>';
                                    html += item.name+'</td><td>';
                                    html += item.order_sn+'</td><td>';
                                    html += item.clean_money+'</td><td>';
                                    html += item.bill_date+'</td></tr>';
                                });
                                if(result.rows.name){
                                    $('#name').val(result.rows.name);
                                }
                                if(result.bill_date){
                                    $('#bill_date').val(result.rows.bill_date);
                                }
                                if(result.rows.order_sn){
                                    $('#order_sn').val(result.rows.order_sn);
                                }
                                $('#page').html(result.page);
                                $('tbody').html(html);
                            }
                        })
                    });
                }
            },
            preview: {
                add: function () {
                    var id = $('#users_id').val();
                    $('#cleaner-add').click(function () {
                        var url = 'cleaninfo/add/uid/' + id;
                        Fast.api.open(url, '添加保洁员信息',{area:['800px','500px']});
                    });
                },
                edit:function(){
                    var id = $('#users_id').val();
                    $('#cleaner-edit').click(function () {
                        var url = 'cleaninfo/edit/uid/' + id;
                        Fast.api.open(url, '修改保洁员信息',{area:['800px','500px']});
                    });
                },
                getMaps:function(){
                    var lng = $('#lng').val();
                    var lat = $('#lat').val();
                    var pos_lng = $('#position_lng').val();
                    var pos_lat = $('#position_lat').val();
                    if((parseFloat(lng) >0) && (parseFloat(lat) >0) ){
                        var district = {lat:lat,lng:lng};
                        var map = Fast.api.showMaps(district,'maps');
                        Fast.api.setPosition(district,map,'lat','lng');
                    }
                    if((parseFloat(pos_lng) >0) && (parseFloat(pos_lat) >0) ){
                        var pos_district = {lat:pos_lat,lng:pos_lng};
                        var position_map = Fast.api.showMaps(pos_district,'position_maps');
                        Fast.api.setPosition(pos_district,position_map,'position_lat','position_lng');
                    }
                }
            },
            getMaps:function(){
                $('#getLatLng').click(function(){
                    var city_code = $('#city').val();
                    var address = $('#address').val();
                    if(!city_code){layer.msg('请选择城市');return false;}
                    if(!address){layer.msg('请填写地址');return false;}
                    $('#lng-lat-area').show();
                    Fast.api.getLatLng(city_code,address,'maps','lat','lng');
                });
            },
            getCitys:function(){
                $('#province').change(function(){
                    var province = $(this).val();
                    $.ajax({
                        type:'get',
                        url:"ajax/getCitys",
                        data:{province:province},
                        dataType:'json',
                        success:function(result){
                            var datas = result.data;
                            var citys = [];
                            var html  = '';
                            html += '<option value="0" selected="selected">请选择城市</option>';
                            $.each(datas,function(i,item){
                                html += '<option value="'+i+'" >'+item+'</option>';
                            });
                            $('#city').html(html);
                        }
                    })
                });
            }
        }
    };
    return Controller;
});
