<?php
ini_set("display_errors",0);
session_start();

if (!isset($_SESSION[login])) {
     header("Location: login.php");
     exit;
}
?>

<html>
<head>
<meta charset="utf-8">
<title>Temperature &amp; Humidity Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">


<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

<!-- Firebase -->
<script src="https://www.gstatic.com/firebasejs/3.3.2/firebase.js"></script>

<!-- canvasjs -->
<script src="js/jquery.canvasjs.min.js"></script>

<!-- Material Design fonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

<!-- Bootstrap -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-material-design.min.css">
<link rel="stylesheet" href="css/ripples.min.css">
<script src="js/bootstrap.min.js"></script>
<script src="js/material.min.js"></script>
<script src="js/ripples.min.js"></script>

<!-- Animate.css -->
<link rel="stylesheet" href="css/animate.min.css">



<script src="https://cdn.netpie.io/microgear.js"></script>
<script>

  const APPID = "SmartGardenProject";
  const KEY = "0PU47Vd8dzaRIuT";
  const SECRET = "A3QLd61QNZJTXOwWsHOuXtpls";

  const ALIAS = "Web_sensor";
  const thing1 = "Web";

  function OnClick(logic){
    if(logic == 1){
	alert("Data has been sent to raspberry pie")
	var temp = document.getElementById("Temperature").value
	var humid = document.getElementById("Humidity").value 
        microgear.chat("pi",temp + "," + humid);
	document.getElementById("now").innerHTML = "Temperature is  now set to " + temp + "C and Humidity is at " + humid +"%"
        }
    else if(logic == 0){
        var temp = 28
	var humid = 80 
        microgear.chat("pi",temp + "," + humid);
	document.getElementById("Temperature").value = '28'
	document.getElementById("Humidity").value = '80'
	document.getElementById("now").innerHTML = "Temperature is  now set to " + temp + "C and Humidity is at " + humid +"%"
        }
  }

  var microgear = Microgear.create({
    key: KEY,
    secret: SECRET,
    alias : ALIAS
  });


  microgear.on('message', function(topic,data) {
    if(data == "ON"){
      document.getElementById("Status").innerHTML =  "ON";
    }else if(data == "OFF"){
      document.getElementById("Status").innerHTML =  "OFF";
    }
  });

  microgear.on('connected', function() {
    microgear.setAlias(ALIAS);
    document.getElementById("connected_NETPIE").innerHTML = "     Status: Successfully connected to NetPie"
  });

  microgear.on('present', function(event) {
    console.log(event);
  });

  microgear.on('absent', function(event) {
    console.log(event);
  });

  microgear.resettoken(function(err) {
    microgear.connect(APPID);
  });

  
</script>

<script>
	
var chart, toOffline;
var newItems = false;

  // Your web app's Firebase configuration
  var config = {
    apiKey: "AIzaSyBKoHODu0sCdbpEe6d1XmnUd_2_T0Ss5Mk",
    authDomain: "seniorproject-muic.firebaseapp.com",
    databaseURL: "https://seniorproject-muic.firebaseio.com",
    projectId: "seniorproject-muic",
    storageBucket: "seniorproject-muic.appspot.com",
    messagingSenderId: "253133476903",
    appId: "1:253133476903:web:96407886ab1cab0b"
  };
  // Initialize Firebase
  //firebase.initializeApp(firebaseConfig);


$(document).ready(function(e) {
	$.material.init()
	
	chart = new CanvasJS.Chart("chartContainer", {
		title: {
			text: "Temperature and Humidity History Graph"
		},
		axisX:{  
			valueFormatString: "MM/DD HH:mm"
		},
		axisY: {
			valueFormatString: "0.0#"
		},
		toolTip: {
			shared: true,
			contentFormatter: function (e) {
				var content = CanvasJS.formatDate(e.entries[0].dataPoint.x, " ") + "<br>";
				content += "<strong>Time</strong>: " + e.entries[1].dataPoint.x + "<br>";
				content += "<strong>Temperature</strong>: " + e.entries[0].dataPoint.y + " &deg;C<br>";
				content += "<strong>Humidity</strong>: " + e.entries[1].dataPoint.y + " %";
				return content;
			}
		},
		animationEnabled: true,
		zoomEnabled: true, 
		interactivityEnabled: true,
	
		data: [
			{
				type: "spline", //change it to line, area, column, pie, etc
				dataPoints: []
			},
			{
				type: "spline", //change it to line, area, column, pie, etc
				dataPoints: []
			}
		]
	});
	chart.render();	
	
	firebase.initializeApp(config);
		
	var logDHT = firebase.database().ref('sensor/am2320(new)').child("average");
	var count =0;
	logDHT.on("child_added", function(sanp) {
		if (!newItems) return;
		var row = sanp.val();
		row.Time = new Date(row.Time);
		content += "<strong>Time</strong>: " + e.entries[1].dataPoint.x + "<br>";
		chart.options.data[0].dataPoints.push({x: row.Time, y: row.Temperature});
		chart.options.data[1].dataPoints.push({x: row.Time, y: row.Humidity});
		chart.render();
		
		$("#Temperature > .content").html(row.Temperature + " &deg;C");
		$("#Humidity > .content").text(row.Humidity + " %");
		
		$("#status").removeClass("danger").addClass("success");
		$("#status > .content").text("ONLINE");
		
		setTimeoffline();
	});
	//count = 0;
	var now = new Date();
	logDHT.orderByChild("Time").startAt(now.getFullYear() + "-" + (now.getMonth() + 1) + "-" + now.getDate()).once("value", function(sanp) {
		//console.log(sanp);
		newItems = true;
		var dataRows = sanp.val();
		//console.log(dataRows);
		var lastRows = 0;
		$.each(dataRows, function(index, row) {
			row.Time = new Date(row.Time);
			//console.log(row.Temperature);
			chart.options.data[0].dataPoints.push({x: row.Time, y: row.Temperature});
			chart.options.data[1].dataPoints.push({x: row.Time, y: row.Humidity});
			 lastRows = row;
			 //console.log(row.Temperature);
			 
		});
		chart.render();
		
		var Arow = lastRows;
		$("#Temperature > .content").html(Arow.Temperature + " &deg;C");
		$("#Humidity > .content").text(Arow.Humidity + " %");
		
		var now = new Date();
		Arow.Time = new Date(Arow.Time);
		// if (Math.round(now) -  Math.round(Arow.Time) < (2 * 60 * 1000)) {
			$("#status").removeClass("danger").addClass("success");
			$("#status > .content").text("ONLINE");
		// } else {
		// 	$("#status").removeClass("success").addClass("danger");
		// 	$("#status > .content").text("OFFLINE");
		// }
		
		setTimeoffline();
	});
});

var setTimeoffline = function() {
	if (typeof toOffline === "number") clearTimeout(toOffline);
	
	toOffline = setTimeout(function() {
		$("#status").removeClass("success").addClass("danger");
		$("#status > .content").text("OFFLINE");
	}, 2 * 60 * 1000);
}
</script>

<style>
.dialog {
	width: 100%;
	border-radius: 4px;
	margin-bottom: 20px;
	box-shadow: 0 1px 6px 0 rgba(0, 0, 0, 0.12), 0 1px 6px 0 rgba(0, 0, 0, 0.12);
}
.dialog > .content {
	padding: 30px 0;
	border-radius: 6px 6px 0 0;
	font-size: 64px;
	color: rgba(255,255,255, 0.84);
	text-align: center;
}
.dialog.primary > .content{
	background: #00aa9a;
}
.dialog.success > .content {
	background: #59b75c;
}
.dialog.info > .content {
	background: #03a9f4;
}
.dialog.warning > .content {
	background: #ff5722;
}

.dialog.danger > .content {
	background: #f44336;
}
.dialog > .title {
	background: #FFF;
	border-radius: 0 0 6px 6px;
	font-size: 22px;
	color: rgba(0,0,0, 0.87);
	text-align: center;
	padding: 10px 0;
	/* box-shadow: 0px 10px 8px -10px rgba(0, 0, 0, 0.4) inset; */
	font-weight: bold;
}
.nav-tabs {
	margin-bottom: 20px;
}
/* Add a black background color to the top navigation */
.topnav {
  background-color: #333;
  overflow: hidden;
}

/* Style the links inside the navigation bar */
.topnav a {
  float: left;
  color: #f2f2f2;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 17px;
}

/* Change the color of links on hover */
.topnav a:hover {
  background-color: #ddd;
  color: black;
}

/* Add a color to the active/current link */
.topnav a.active {
  background-color: #4CAF50;
  color: white;
}
</style>


</head>

<body style="background-color: a7e9af;" onload = 'test();'>

<div class="topnav">
  <a class="active" href="/index.html">Home</a>
  <a href="./everything.php">Overview</a>
  <a href="./graph.php">Daily Chart</a>
  <a href="./everything.php"target="_blank">Open in full screen</a>
  <a href="./logout.php">Logout</a>
</div>


<div id="place"></div>
  
  <h1><center><p><b>Overview</b></p></center></h1>
  <h2 id="connected_NETPIE"></h2>
	Temperature: <input type="number" id="Temperature" placeholder="default is 28" value="28" min="1"> &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <br>
	Humidity: <input type="number" id="Humidity" placeholder="default is 80" value="80" min="1"> <br>
	<button type="button" onclick="OnClick(1)">Submit</button>
  <button type="button" onclick="OnClick(0)">Default</button>
  <a href="everything.php">
   <input type="button" value="Logout" /></a>
	<p id="now">Temperature and Humidity are currently at the default value</p>
    <p><b>Note:</b> Number must be greater than 0.</p>
    

    <!-- starting the indicators -->

    <!-- <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active"><a href="#overview" aria-controls="home" role="tab" data-toggle="tab">Overview</a></li>
      <li role="presentation"><a href="#history" aria-controls="profile" role="tab" data-toggle="tab">History</a></li>
	</ul>  -->
	
    <!-- Tab panes -->
	<div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="overview">
        <div class="row">
           <div class="col-sm-4">
             <div class="dialog primary fadeIn animated" id="Temperature">
               <div class="content">00.0 &deg;C</div>
               <div class="title">Temperature</div>
             </div>
           </div>
           <div class="col-sm-4">
             <div class="dialog info fadeIn animated" id="Humidity">
               <div class="content">00.0 %</div>
               <div class="title">Humidity</div>
             </div>
           </div>
           <div class="col-sm-4">
             <div class="dialog success fadeIn animated" id="status">
               <div class="content">???</div>
               <div class="title">Firebase connection</div>
             </div>
           </div>
        </div>
	  </div>
	  </div>
     	<div role="tabpanel" class="tab-pane" id="history">
		<div id="chartContainer" style="height: 300px; width: 100%;"></div>
		</div>
		
    

</body>
</html>