<?php
  require_once('test.php');
  $str = $_POST['coords'];
  $old = $_POST['data'];
  $old = json_decode($old,true);
  $coords = substr($str,0,strlen($str)-1);
  $coords = substr($coords,1);
  $coords = str_replace('],','];',$coords);
  $coords = explode(';',$coords);
  for($i = 0;$i<count($coords);$i++){
    $a = explode(',',$coords[$i]);
    $b = substr($a[0],1,strlen($a[0]));
    $c = substr($a[1],0,strlen($a[1]-1));
    $corrds[$i]=array('x'=>$b,'y'=>$c);
  }
  echo "<table border = '1'><tr><th>序号</th><th>旧经度</th><th>旧纬度</th><th>新经度</th><th>新纬度</th></tr>";
  for($i = 0;$i<count($corrds);$i++){
    echo '<tr><td>'.$old[$i]['id'].'</td><td>'.$old[$i]['x'].'</td><td>'.$old[$i]['y'].'</td><td>'.$corrds[$i]['x'].'</td><td>'.$corrds[$i]['y'].'</td></tr>';
  }
  echo "</table>";
  echo '<a href="#" onClick="javascript :history.back(-1);">返回</a>';
?>
