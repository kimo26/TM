<!DOCTYPE HTML>
<HTML>
    <HEAD>
        <link href="https://fonts.googleapis.com/css?family=Francois+One&display=swap" rel="stylesheet">
        <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="finance.api.js" type="text/javascript"></script>
        <link href="sara.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@1.0.0/dist/tf.min.js"></script>
        <script src="https://unpkg.com/lodash@4.16.6"></script>
        <script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-analytics.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-auth.js"></script>
        <script src="https://www.gstatic.com/firebasejs/7.6.1/firebase-database.js"></script>
        <script>
  // Your web app's Firebase configuration
  var firebaseConfig = {
    apiKey: "AIzaSyAoR1-y3P1C_V6BE-nRJHujuPGoU_2Be8M",
    authDomain: "financial-aid-8967d.firebaseapp.com",
    databaseURL: "https://financial-aid-8967d.firebaseio.com",
    projectId: "financial-aid-8967d",
    storageBucket: "financial-aid-8967d.appspot.com",
    messagingSenderId: "458633954869",
    appId: "1:458633954869:web:8c9a5e8d4782f746c2d37c",
    measurementId: "G-7YBVD348Q5"
  };
  var project = firebase.initializeApp(firebaseConfig);
  firebase.analytics();
  
</script>
    </HEAD>
    <BODY>
        
        <?php
            $ticker = $_GET['ticker'];
            $period = $_GET['period'];
            require_once 'simple_html_dom.php';
            
            $interval = 0;
            $range=0;
            if ($period == "short"){
                $range = "1mo";
                $interval = "1d";
            }else if ($period == "medium"){
                $range = "365d";
                $interval = "1d";
            }else{
                $interval = "1mo";
                $range="3650d";
            }
            
            $html = str_get_html(file_get_contents('https://query1.finance.yahoo.com/v8/finance/chart/'.$ticker.'?range='.$range.'&interval='.$interval));
            echo "<script>
            var raw = ".$html.";
            var specific = raw.chart.result[0].indicators.quote[0];
            var time = raw.chart.result[0].timestamp;
            var close = specific.close;
            var open = specific.open;
            var high = specific.high;
            var low = specific.low;
            var dates = [];
            function rounds(n){
                return Math.trunc(1000*n)/1000;
            }
            for (i=0;i<close.length;i++){
                close[i]=round(close[i])
            }
            var percentage_change = 100*(close[close.length-1]-close[0])/close[0];
            
            

            </script>"
        ?>
        <script>
var close = specific.close;
var volume = specific.volume;
function pct(future,current){
    var top = future-current
    return top/current;
}
var volumeOld = [];
for (i=0; i<volume.length;i++){
    volumeOld.push(volume[i])
}
for (i=0; i<3; i++){
    volume.shift();
}
var volume_percentage=[]
for (i=0;i<volumeOld.length;i++){
    volume_percentage.push(pct(volume[i],volumeOld[i]));
}
var closeOld=[];
for (i=0; i<close.length;i++){
    closeOld.push(close[i])
}
for (i=0; i<3; i++){
    close.shift();
}
var close_percentage=[]
for (i=0;i<closeOld.length;i++){
    close_percentage.push(pct(close[i],closeOld[i]));
}
var data=[]
for (i=0;close_percentage.length>i;i++){
    data.push([close_percentage[i],volume_percentage[i]]);
}
var sequential_data=[];
var seq=[];
for (i=0;i<data.length;i++){
    seq.push(data[i]);
    if (seq.length == 20){
        sequential_data.push(seq);
        seq = [];
    }
}
var size = sequential_data.length;
var denom = size*(size+1)/2;
var predictions=[];
var sum=0;
const model = tf.loadLayersModel('https://finance-api.000webhostapp.com/model/model.json').then(model=>{
        for (i=0;i<size;i++){
            var prediction = model.predict(tf.tensor([sequential_data[i]]));
            prediction = prediction.argMax().dataSync()[0]
            predictions.push(prediction*i);
            sum+=prediction
        }
    });
function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}
sum = round(sum/denom);
var guess = '';
if (sum <0.4){
    guess = 'sell';
}else if (sum>0.6){
    guess='buy';
}else{
    guess='keep';
}
var ticker = parseURL(window.location).searchObject.ticker;
var ref = firebase.database().ref("companies");
var snap = "hi";

window.onload = function what(){

                if ("buy"  == guess){
                    document.getElementsByClassName('prediction')[0].style.color = 'green';
                    document.getElementsByClassName('prediction')[0].innerHTML = "Our model recommends you to buy the stock";
                }else if ("sell" == guess){
                    document.getElementsByClassName('prediction')[0].style.color = 'red';
                    document.getElementsByClassName('prediction')[0].innerHTML = "Our model recommends you to sell the stock";
                }else{
                    document.getElementsByClassName('prediction')[0].style.color = 'gray';
                    document.getElementsByClassName('prediction')[0].innerHTML = "Our model recommends you to hold the stock";
                }
                document.getElementsByClassName('price')[0].innerHTML = '$'+close[close.length - 1];
                
                if (percentage_change > 0){
                    document.getElementsByClassName('percent')[0].innerHTML = '+'+round(percentage_change) + '%';
                    document.getElementsByClassName('percent')[0].style.color = 'green'
                }else{
                    document.getElementsByClassName('percent')[0].innerHTML = round(percentage_change) + '%';
                    document.getElementsByClassName('percent')[0].style.color = 'red'
                }
                            ref.orderByChild("ticker").equalTo(ticker).on("child_added", function(snapshot) {
               snap="load";
  document.getElementById('title').innerHTML = snapshot.node_.children_.root_.left.value.value_


});

            }
              console.log(snap)

            

</script>
<br>
<a href="index.php" style="text-decoration:none; font-size: 20px; font-family: 'Francois One', sans-serif; margin-left: 20px; margin-top: 20px; color: black;
">back</a>
<br>
        <h1 id="title"></h1><br>
        	<div id='myDiv' style="margin-left:60px"></div>
        	<br><br>
        <div id="informations">
            <h1 id="info" style="margin-right:30px; margin: auto"><?php echo $ticker ?></h1>
            <h1 id="info" class="price" style="margin-right:30px;margin:auto"></h1>
            <h1 id="info" class="percent" style="margin:auto"></h1>
        </div>
        <br><br><br>
        <div>
            <h1>Prediction based on our deep learning model:</h1>
            <h1 class="prediction" style="font-size: 60px"></h1>
        </div>
<?php

echo "<script>

for (i=0;i<time.length;i++){
    
    var unixtimestamp = time[i];
    var months_arr = ['01','02','03','04','05','06','07','08','09','10','11','12'];
     var date = new Date(unixtimestamp*1000);
     var year = date.getFullYear();
     var month = months_arr[date.getMonth()];
     var day = date.getDate();
     var convdataTime = year+'-'+month+'-'+day;
     
     dates.push(convdataTime)
}
var dataX = [];
var dataY=[];
for (i=(close.length-close.length);i<close.length;i++){
    dataX.push(close[i]);
    dataY.push(dates[i]);
}

var options = {
            chart: {
                height: window.screen.height - 300,
                type: 'area',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            series: [{
                name: '".$ticker."',
                data: dataX
            }],
            
            labels: dataY,
            
            xaxis: {
                type: 'datetime',
                labels: {
                    style: {
                        fontSize: '20px'
                    }
       }
            },
            yaxis: {
                opposite: true,
                labels: {
            style: {
                fontSize: '20px'
            }
       }
            },
            legend: {
                horizontalAlign: 'left'
            }
        }

        var chart = new ApexCharts(
            document.querySelector('#myDiv'),
            options
        );

        chart.render();
</script>";
?>

    </BODY>
</HTML>