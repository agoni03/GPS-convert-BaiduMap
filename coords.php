<?php require_once('test.php');?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <script src = "jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.5&ak=kktO0i6HtNvG4xC09cyGgmlTZ3dAeKDU"></script>
    <title></title>
  </head>
  <body>
    <?php
      $conn = new conn();
      $total=$conn->getOne('select count(*) as total from coords');
      $total=$total['total']; //goods表数据总数据条数
      $num  = 100;
      $totalpage=ceil($total/$num);  //计算页数
      if(isset($_GET['page']) && $_GET['page']<=$totalpage){//这里做了一个判断，若get到数据并且该数据小于总页数情况下才付给当前页参数，否则跳转到第一页
        $thispage=$_GET['page'];
      }else{
        $thispage=1;
      }
      //注意下面sql语句中红色部分，通过计算来确定从第几条数据开始取出，当前页数减去1后再乘以每页显示数据条数
      $sql='select coords.id,coords.x,coords.y from coords limit '.($thispage-1)*$num.','.$num.'';
      $data=$conn->getAll($sql);
      $coords = json_encode($data);
      echo "<table border = '1'><tr><th>序号</th><th>旧经度</th><th>旧纬度</th></tr>";
      foreach($data as $k=>$v){
        echo '<tr><td>'.$v['id'].'</td><td>'.$v['x'].'</td><td>'.$v['y'].'</td></tr>';
      }
      echo "</table>";

      //显示分页数字列表
      for($i=1;$i<=$totalpage;$i++){
        echo '<a href="?page='.$i.'">'.$i.'</a> ';
      }
    ?>
    <form action = 'post.php' method="post">
      <input style="display:none" id = "coords" name = 'coords'>
      <input style="display:none" name = "data" value = <?php echo $coords; ?>>
      <button type="submit">转换</button>
    </form>
  </body>
</html>
<script type="text/javascript">

function myTransMore(points,type,callbackName){
    var xyUrl = "http://api.map.baidu.com/geoconv/v1/?coords=";
    var coordsStr = "";
    var maxCnt = 100 ;
    var send = function(){
        var positionUrl = xyUrl + coordsStr + "&from=1&to=5&ak=kktO0i6HtNvG4xC09cyGgmlTZ3dAeKDU" + "&callback="+callbackName;
        var script = document.createElement('script');
        script.src = positionUrl;
        document.getElementsByTagName("head")[0].appendChild(script);
        coordsStr = "";
    }
    for( var index in points){
        if (index % maxCnt == 0 && index != 0) {
            send();
        }
        coordsStr = coordsStr + points[index].lng + ',' +points[index].lat;
        if (index < points.length - 1) {
            coordsStr = coordsStr + ';';
        }
        if (index == points.length - 1) {
            send();
        }
    }

}
(function(){
function load_script(xyUrl, callback){
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = xyUrl;
    //借鉴了jQuery的script跨域方法
    script.onload = script.onreadystatechange = function(){
        if((!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){
            callback && callback();
            // Handle memory leak in IE
            script.onload = script.onreadystatechange = null;
            if ( head && script.parentNode ) {
                head.removeChild( script );
            }
        }
    };
    // Use insertBefore instead of appendChild  to circumvent an IE6 bug.
    head.insertBefore( script, head.firstChild );
}
//
window.BMap = window.BMap || {};
BMap.Convertor = {};
BMap.Convertor.transMore = myTransMore;
})();

var testJsonStr = <?php echo json_encode($data); ?>;
var mapCenterPt = new BMap.Point(116.41413701159672,39.90795884517671);
var posIndex = 0;
var pointsArray = new Array();
var maxCnt = 100 ;
TransGPS();
 function TransGPS(){
    gpsPoints = getPoints(testJsonStr);
    pointsArray = wareGpsPointsBeforeSend(gpsPoints);
    myTransMore(pointsArray[posIndex],0,"callbackName");
 }


 function callbackName(data){
    if (data.status!=0) {
        alert("地图坐标转换出错");
        return;
    }
    var points = data.result;
    var TransResult = null;
    for(var index in points){
        TransResult = points[index];
        // var points = new BMap.Point(TransResult.x, TransResult.y);
        index1 = eval(Math.floor(posIndex * maxCnt)+Math.floor(index));
        if (testJsonStr[index1]["x"]!= null &&
            testJsonStr[index1]["x"]!=0 &&
            testJsonStr[index1]["y"]!=null &&
            testJsonStr[index1]["y"]!=0

            ) {
            testJsonStr[index1]["x"] = points.lng;
            testJsonStr[index1]["y"] = points.lat;
        }
    }
    posIndex++;
    if (posIndex<pointsArray.length) {
        myTransMore(pointsArray[posIndex],0,"callbackName");
    }
    var baidu = data.result;
    var coords = new Array();
    for (var i = 0; i < baidu.length; i++) {
      coords[i] = new Array(baidu[i]['x'],baidu[i]['y']);
    }
    console.log(coords);
    var obj = JSON.stringify(coords);
    $('#coords').attr('value',obj);
 }

function getPoints(testJsonStr){
    var points = [];
    for( var i = 0 ; i< testJsonStr.length;i++){
        if(testJsonStr[i]["x"]!= 0 && testJsonStr[i]["y"]!=0
            && testJsonStr[i]["x"]!= null && testJsonStr[i]["y"]!=null){
            var pt = new BMap.Point(testJsonStr[i]["x"],testJsonStr[i]["y"]);
        points.push(pt);
        }else{
            points.push(mapCenterPt);
        }
    }
    return points;
}

function wareGpsPointsBeforeSend(gpsPoints){
    var pointsArray = new Array();
    var times = Math.floor(gpsPoints.length/maxCnt);
    var k = 0 ;
    for(var i= 0; i<times;i++){
        pointsArray[i] = new Array();
        for(var j= 0;j<maxCnt;j++,k++){
            pointsArray[i][j] = gpsPoints[k];
        }
    }
    if (k<gpsPoints.length) {
        var j = 0 ;var i = times;
        pointsArray[i] = new Array();
        while(k<gpsPoints.length){
            pointsArray[i][j] = gpsPoints[k];
            k++;
            j++;
        }
    }
    return pointsArray;
}
</script>
