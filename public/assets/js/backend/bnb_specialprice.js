define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        setprice: function () {
            Controller.api.removePrice();//移除活动价格
            Controller.api.open();//打开编辑或增加页
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.priceTypeChange();//切换价格显示
            Controller.api.bindevent();
        },
        edit:function(){
            Controller.api.priceTypeChange();//切换价格显示
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
            },
            open:function(){
                //打开编辑或增加页面
                $('.add-span,.edit-span').click(function(){
                    var dataAct = $(this).attr('data-act');
                    var bid = $('#bid').val();
                    var url = '';
                    var text = '';
                    if(dataAct == 'edit') {
                        var dataId = $(this).attr('data-id');
                        url = 'bnb_specialprice/edit/ids/' + dataId;
                        text = '编辑活动价格';
                    }else{
                        url =  'bnb_specialprice/add/bid/'+bid;
                        text = '添加活动价格';
                    }
                    Fast.api.open(url, text);
                });
            },
            removePrice:function(){
                //移除活动价格
                $(document).on("click",".rm-span",function(){
                    var dataAct = $(this).attr('data-act');
                    if(dataAct == 'edit'){
                        var dataId = $(this).attr('data-id');
                        $.ajax({
                            type:'get',
                            url:'bnb_specialprice/delete',
                            data:{id:dataId},
                            dataType:'json',
                            success:function(result){
                                if(result.code == 1){
                                    Toastr.success(result.msg);
                                    var t=setTimeout("window.location.reload()",2000);
                                }else{
                                    Toastr.error(result.msg);
                                }
                            }
                        });
                    }else{
                        $(this).parent().parent().remove();
                    }

                });
            },
            priceTypeChange:function(){
                //切换价格显示
                $('input[name="price_type"]').change(function(){
                    var price_type = $(this).val();
                    if(price_type == 2){
                        $('#end-time').hide();
                        $('#begin-time label').html('活动日期');
                    }else{
                        $('#end-time').show();
                        $('#begin-time label').html('开始日期');
                    }
                });
            }
        }
    };
    return Controller;
});
