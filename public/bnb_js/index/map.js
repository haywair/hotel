/**
 * Created by Administrator on 2017/11/13 0013.
 */

function getLatLng(city,directionName,mapDom,latDomId,lngDomId,lat,lng){
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
                var map =  Fast.api.showMaps({lat:lat,lng:lng},mapDom);
                Fast.api.setPosition({lat:lat,lng:lng},map,latDomId,lngDomId);
            }else{
                //获取经纬度失败
            }
        });
    });
}
function showMaps(direction,mapDom){
    var map = new AMap.Map(mapDom, {
        resizeEnable: true,
        zoom:17,
        center: [direction.lng,direction.lat]
    });
    return map;
}
function setPosition(direction,map,latDomId,lngDomId){
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
}

