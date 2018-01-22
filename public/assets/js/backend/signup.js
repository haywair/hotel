define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        landlord:function(){
            var indexUrl = 'signup/landlord';
            Controller.index(indexUrl);
        },
        cleaner:function(){
            var indexUrl = 'signup/cleaner';
            Controller.index(indexUrl);
        },
        index: function (indexUrl) {
            Table.api.init({
                extend: {
                    index_url: indexUrl,
                    verify_url: 'signup/verify',
                    del_url: 'signup/del',
                    more_url:'signup/more',
                    table: 'signup',
                }
            });
            var table = $("#table");
            var type = $('#type').val();
            var url = $.fn.bootstrapTable.defaults.extend.index_url+'?type='+type;
            var cityData = Controller.api.citylist();
            var provinceData = Controller.api.provincelist();

            // 初始化表格
            table.bootstrapTable({
               /* url: $.fn.bootstrapTable.defaults.extend.index_url+'?type='+type,*/
                url:url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'weigh', title: __('Weigh'),operate:false},
                        {field: 'type', title: '申请类型',formatter: Controller.api.formatter.type,searchList:{1:"保洁",2:'房东'}},
                        {field: 'status', title: __('Status'),formatter: Controller.api.formatter.status,searchList:{0:"隐藏",1:"正常",2:'已处理'}},
                        {field: 'user_avatar', title: __('头像'),formatter: Controller.api.formatter.image,operate:false},
                        {field: 'user_nickname', title: __('Username')},
                        {field: 'truename', title: __('Truename')},
                        {field: 'sex', title: __('性别'),formatter: Controller.api.formatter.sex,operate:false},
                        {field: 'age', title: __('年龄'),operate:false},
                        {field: 'contact_mobile', title: __('Mobile')},
                        {field: 'province_code', title: __('省'),formatter: Controller.api.formatter.province,searchList:provinceData},
                        {field: 'city_code', title: __('市'),formatter: Controller.api.formatter.city,searchList:cityData},
                        {field: 'idcard_number', title: __('身份证号')},
                        {field: 'username', title: __('管理员'),operate:false},
                        {field: 'finish_time', title:'处理完成时间', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'createtime', title: __('申请时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'updatetime', title: __('更新时间'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Controller.api.changeCity();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        more:function(){
            Fast.api.refresh();
            Controller.api.more.getMaps();
            Controller.api.more.landlordPreview();
            Controller.api.more.bindbnb();
            Controller.api.more.cleanerPreview();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter : {
                image:function(value,row,index){
                    html = "<img src='"+value+"' alt='未设置'>";
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
                city:function(value,row,index){
                    var html = '<span class="text">'+row.city_name+'</span>';
                    return html;
                },
                province:function(value,row,index){
                    var html = '<span class="text">'+row.province_name+'</span>';
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
                    /*buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});*/
                    /* buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-editone'});*/
                    var verifyState = 2;//已审核状态值
                    if (row.status != verifyState) {
                        buttons.push({name: 'verify', text: '审核', classname: 'btn btn-xs btn-danger btn-verifyone'});
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
                    'click .btn-xxxone': function (e, value, row, index) {
                        e.stopPropagation();
                        alert('xxx');
                    },
                    'click .btn-moreone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.more_url + (options.extend.more_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-verifyone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var id = row[options.pk];
                        var type = row.type;
                        var user_id = row.users_id
                        Fast.api.ajax({url:'signup/verify',data:{id:id,type:type,user_id:user_id}});
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