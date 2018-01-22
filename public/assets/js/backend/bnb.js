define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        store:function(){
           var indexUrl = 'bnb/store';
           Controller.index(indexUrl);
        },
        sale:function(){
            var indexUrl = 'bnb/sale';
            Controller.index(indexUrl);
        },
        index: function (indexUrl) {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    xxx_url: indexUrl,
                    index_url: indexUrl,
                    add_url: 'bnb/add',
                    edit_url: 'bnb/edit',
                    more_url: 'bnb/more',
                    del_url: 'bnb/del',
                    multi_url: 'bnb/multi',
                    bnbInfo_url:'bnb/btnInfo',
                    table: 'bnb',
                },
            });
            var table = $("#table");
            var state = $('#state').val();
            var cityData = Controller.api.citylist();
            var provinceData = Controller.api.provincelist();

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url+'?state='+state,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'name', title: __('Name'),operate:'LIKE'},
                        {field: 'status', title: __('Status'), formatter: Controller.api.formatter.status,searchList:{0:'下架',1:'正常',2:'推荐'}},
                        {field: 'weigh', title: __('Weigh'),operate:false},
                    //    {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime},
                    //    {field: 'updatetime', title: __('Updatetime'), formatter: Table.api.formatter.datetime},

                        {field: 'bnb_adwords', title: __('Bnb_adwords'),operate:false},
                        {field: 'area_province_code', title: __('Area_province_number'),formatter: Controller.api.formatter.province,searchList:provinceData},
                        {field: 'area_city_code', title: __('Area_city_number'),formatter: Controller.api.formatter.city,searchList:cityData},
                        {field: 'features_ids', title: __('Features_ids'),operate:false},
                        {field: 'room_people', title: __('Room_people')},
                        {field: 'room_space', title: '面积'},
                        {field: 'room_bedroom', title: __('Room_bedroom'),operate:false},
                        {field: 'room_livingroom', title: __('Room_livingroom'),operate:false},
                //        {field: 'room_bed', title: __('Room_bed')},
                //        {field: 'room_toilet', title: __('Room_toilet')},
                        {field: 'bnb_image', title: __('Bnb_image'), formatter: Controller.api.formatter.image,operate:false},
                        {field: 'landlord_name', title: __('Landlord_user'),operate:false},
                        {field: 'manage_name', title: __('Manager_user'),operate:false},
                        {field: 'in_hour', title: __('In_hour'),operate:false},
                        {field: 'out_hour', title: __('Out_hour'),operate:false},
                        {field: 'fee_clean', title: __('Fee_clean'),operate:false},
                        {field: 'fee_deposit', title: __('Fee_deposit'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: Controller.api.formatter.operatex}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Controller.api.changeCity();
        },
        add: function () {
            Controller.api.getMaps();
            Controller.api.getCitys();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.commonEdit();
            Controller.api.bindevent();
        },
        more:function(){
            var id = $('#ids').val();
            Fast.api.refresh();
            Controller.api.more.info(id);//修改信息
            Controller.api.more.weekprice(id);
            Controller.api.more.specialprice(id);
            Controller.api.more.viewImage(id);
            Controller.api.more.viewCleanPhoto(id);
            Controller.api.bindevent();
        },
        info:function(){
            Controller.api.bindevent();
            Controller.api.commonEdit();
        },
        bindbnb:function(){
            Fast.api.getCitys('province','city');
            Fast.api.refresh();
            Controller.api.bindbnb.subform();
            Controller.api.bindbnb.view();
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
                },
                image: function (value, row, index) {
                    value=value?value:'/assets/img/blank.gif';
                    var classname = typeof this.classname !== 'undefined' ? this.classname : 'img-sm img-center';
                    return '<img class="' + classname + '" src="/' +row.bnb_image+ '" />';
                },
                city:function(value,row,index){
                    var html = '<span class="text">'+row.city_name+'</span>';
                    return html;
                },
                province:function(value,row,index){
                    var html = '<span class="text">'+row.province_name+'</span>';
                    return html;
                },
                operatex: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                  /*  buttons.push({name: 'xxx', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-xxxone'});*/
                   /* buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-addtabs btn-editone'});*/
                    buttons.push({name: 'more', text:'详情', classname: 'btn btn-xs btn-success btn-addtabs btn-moreone'});
                    if((row.status == '1') || (row.status == '2')){
                        var mark = '推荐';
                        if(row.status == '2'){
                            mark = '不推荐';
                        }
                        buttons.push({name: 'line', text:'下架', classname: 'btn btn-xs btn-primary btn-lineone'});
                        buttons.push({name: 'mark', text:mark,classname: 'btn btn-xs btn-danger'+' btn-markone'});
                    }else if(row.status == '0'){
                        buttons.push({name: 'line', text: '上架', classname: 'btn btn-xs btn-primary btn-lineone'});
                        buttons.push({name: 'del', text: '删除', classname: 'btn btn-xs btn-danger btn-delone'});
                    }
                    var html = [];
                    var url, classname, icon, text, title, extend;
                    $.each(buttons, function (i, j) {
                        if (j.name === 'dragsort' && typeof row[Table.config.dragsortfield] === 'undefined') {
                            return true;
                        }
                        if (['xxx', 'add', 'edit','more', 'del', 'multi', 'dragsort'].indexOf(j.name) > -1 && !options.extend[j.name + "_url"]) {
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
                },
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
                        var url = options.extend.edit_url + (options.extend.edit_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-moreone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.more_url + (options.extend.more_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-markone': function (e, value, row, index) {
                        e.stopPropagation();
                        var jsonArr = {
                            url:'bnb/mark',
                            data:{id:row.id,act:'mark'}
                        };
                        Fast.api.ajax(jsonArr);
                    },
                    'click .btn-lineone': function (e, value, row, index) {
                        e.stopPropagation();
                        var jsonArr = {
                            url:'bnb/mark',
                            data:{id:row.id,act:'saleOrOff'}
                        };
                        Fast.api.ajax(jsonArr);

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
                info:function(id){
                    $('#bnb-info,#position,#bnb-type,#manageruser-change,#refundfee,#lorduser-change,#features-change,#offsale,#bnb-image,#settleprice').click(function(){
                        var typeVal = $(this).attr('data-type');
                        var text = $(this).attr('data-text');
                        var url = 'bnb/info/ids/' +id+'/type/'+typeVal;
                        Fast.api.open(url, text);
                    });
                },
                weekprice:function(id){
                    $('#weekprice').click(function(){
                        var url = 'bnb_weekprice/add/bid/' +id;
                        Fast.api.open(url, '设定房间价格');
                    });
                },
                specialprice:function(id){
                    $('#specialprice').click(function(){
                        var url = 'bnb_specialprice/setprice/bid/' +id;
                        Fast.api.open(url, '设定活动价格');
                    });
                },
                viewImage:function(id){
                    $('#bnb-listimage').click(function(){
                       /* var url = 'images/add/bid/'+id;*/
                        var url = 'images/index/bid/'+id;
                        Fast.api.open(url, '房间图片');
                    });
                },
               viewCleanPhoto:function(id){
                    $('#bnb-clean-image').click(function(){
                        var url = 'bnb_clean_photo/index/bid/'+id;
                        Fast.api.open(url, '房间保洁图片');
                    });
                },

            },
            getLatLng:function (city,directionName,latDomId,lngDomId,lat,lng){
                AMap.service('AMap.Geocoder',function(){
                    //实例化Geocoder
                    geocoder = new AMap.Geocoder({
                        city: city//城市，默认：“全国”
                    });
                    //TODO: 使用geocoder 对象完成相关功能
                    geocoder.getLocation(directionName, function(status, result) {
                        if (status === 'complete' && result.info === 'OK') {
                            //TODO:获得了有效经纬度，可以做一些展示工作
                            if(!lat && !lng){
                                lat = result.geocodes[0].location.lat;
                                lng = result.geocodes[0].location.lng;
                            }
                            $('#'+latDomId).val(lat);
                            $('#'+lngDomId).val(lng);
                            var map =  Controller.api.showMaps({lat:lat,lng:lng});
                            Controller.api.setPosition({lat:lat,lng:lng},map,latDomId,lngDomId);
                        }else{
                            //获取经纬度失败
                        }
                    });
                });
            },
            showMaps:function(direction){
                var map = new AMap.Map('maps', {
                    resizeEnable: true,
                    zoom:17,
                    center: [direction.lng,direction.lat]
                });
                return map;
            },
            setPosition:function (direction,map,latDomId,lngDomId){
                var marker = new AMap.Marker({
                    position: [direction.lng,direction.lat],
                    draggable: true,
                    cursor: 'move',
                    raiseOnDrag: true
                });
                marker.setMap(map);
                marker.on('dragend',function(){
                    var res = marker.getPosition( );
                    lat = res.lat;
                    lng = res.lng;
                    $('#'+latDomId).val(lat);
                    $('#'+lngDomId).val(lng);
                })
            },
            getCitys:function(){
                $('#c-area_province_number').change(function(){
                    var province = $(this).val();
                    $.ajax({
                        type:'get',
                        url:"ajax/getCitys",
                        data:{province:province},
                        dataType:'json',
                        success:function(result){
                            var datas = result.data;
                            console.log(datas);
                            var citys = [];
                            var html  = '';
                            html += '<option value="0" selected="selected">请选择城市</option>';
                            $.each(datas,function(i,item){
                                html += '<option value="'+i+'" >'+item+'</option>';
                            });
                            $('#c-area_city_number').html(html);
                        }
                    })
                });
            },
            getMaps:function(){
                $('#getLatLng').click(function(){
                    var city_code = $('#c-area_city_number').val();
                    var address = $('#c-area_address_number').val();
                    if(!city_code){layer.msg('请选择城市');return false;}
                    if(!address){layer.msg('请填写地址');return false;}
                    $('#lng-lat-area').show();
                    Controller.api.getLatLng(city_code,address,'lat','lng');
                });
            },
            commonEdit:function(){
                var city_code = $('#c-area_city_number').val();
                var area_address = $('#c-area_address_number').val();
                var lat = $('#lat').val();
                var lng = $('#lng').val();
                Controller.api.getMaps();
                Controller.api.getCitys();
                Controller.api.getLatLng(city_code,area_address,'lat','lng',lat,lng);
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
                $('select[name="area_province_code"]').change(function(){
                    var provinceCode = $('select[name="area_province_code"]').find("option:selected").attr('value');
                    var cityJson = Controller.api.citylist(provinceCode);
                    var html = '';
                    html += '<option value="">请选择</option>';
                    $.each(cityJson,function(i,item){
                        html += '<option value="'+i+'">'+item+'</option>';
                    });
                    $('select[name="area_city_code"]').html(html);
                });
            },
            bindbnb:{
                view:function(){
                    $('.btn-more').click(function(e){
                        var ids = $(this).attr('data-id');
                        e.stopPropagation();
                        var url = 'bnb/more'+ "?ids="+ids;
                        Backend.api.addtabs(Backend.api.replaceids(this,url), '详情');
                    });
                },
                subform:function(){
                    $('.btn-submit').click(function(){
                        var uid = $('#uid').val();
                        var bnbName = $('#name').val();
                        var city = $('#city').val();
                        var province = $('#province').val();
                        var html = '';
                        $.ajax({
                            type:'get',
                            url:'bnb/bindBnb',
                            data:{ids:uid,name:bnbName,province:province,city:city},
                            dataType:'json',
                            success:function(result){
                                //console.log(result);return false;
                                var data = result.rows.data;
                                if(data.length > 0) {
                                    $.each(data, function (i, item) {
                                        html += '<tr><td>';
                                        html += '<input class="check-btn" id="check-' + item.id + '" type="checkbox" name="ids[]"  value="' + item.id + '"></td>';
                                        html += '<td><img src="' + item.bnb_image + '" width="50" height="50"></td>';
                                        html += '<td>' + item.name + '</td>';
                                        html += '<td>' + item.status + '</td>';
                                        html += '<td>' + item.province_name + item.city_name + '</td>';
                                        html += '<td>' + item.room_people + '</td><td>';
                                        html += '<a href="javascript:void(0);" class="btn btn-danger btn-checkone" data-id="' + item.id + '">删除绑定</a>';
                                        html += '</td></tr>';
                                    });
                                }else{
                                    html = "<tr><td colspan='6'>暂无相关记录</td></tr>"
                                }
                                if (result.rows.name) {
                                    $('#name').val(result.rows.name);
                                }
                                if (result.rows.province) {
                                    var province = result.rows.province;
                                    $('option[value="' + province + '"]').attr('selected', 'selected');
                                }
                                if (result.rows.city) {
                                    var city = result.rows.city;
                                    $('option[value="' + city + '"]').attr('selected', 'selected');
                                }
                                $('#page').html(result.page);
                                $('tbody').html(html);

                            }
                        })
                    });
                }
            }
        }
    };
    return Controller;
});