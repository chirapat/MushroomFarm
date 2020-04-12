<?php
ini_set("display_errors",0);
session_start();

if (!isset($_SESSION[login])) {
     header("Location: login.php");
     exit;
}
?>

<!DOCTYPE HTML>
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

<script>

var chart, toOffline;
var newItems = false;
// Initialize Firebase
var config = {
	apiKey: "AIzaSyBKoHODu0sCdbpEe6d1XmnUd_2_T0Ss5Mk",
    authDomain: "seniorproject-muic.firebaseapp.com",
    databaseURL: "https://seniorproject-muic.firebaseio.com",
    projectId: "seniorproject-muic",
    storageBucket: "seniorproject-muic.appspot.com",
    messagingSenderId: "253133476903",
    appId: "1:253133476903:web:96407886ab1cab0b"
};

$(document).ready(function(e) {

	firebase.initializeApp(config);
	$("button").click(function(){

			var longday = document.getElementById("mydate").value; 
			var myyear = longday.substring(0,4)+"/";
			var mymonth = longday.substring(5,7)+"/";
			var mydayString = longday.substring(8,11);
			var mydayInt = parseInt(mydayString)-1;

			if(mydayInt<10){
				var myday = "0"+String(mydayInt)
				}
			else {
				var myday = String(mydayInt)}
			var comparefirebasetime = myyear.concat(mymonth,myday)
			console.log(comparefirebasetime)

			var longdayEnd = document.getElementById("mydateEnd").value; 
			var myyearEnd = longdayEnd.substring(0,4)+"/";
			var mymonthEnd = longdayEnd.substring(5,7)+"/";
			var mydayEnd = longdayEnd.substring(8,11);
		
			var comparefirebasetimeEnd = myyearEnd.concat(mymonthEnd,mydayEnd)
			console.log(comparefirebasetimeEnd)
		
			


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
				axisYType: "secondary",
				dataPoints: []
			},
			{
				type: "spline", //change it to line, area, column, pie, etc
				axisYType: "secondary",
				dataPoints: []
			}
		]
	});
	chart.render();	

	

		
	var logDHT2 = firebase.database().ref('sensor/am2320(new)').child("average");
		//startAt(3)
		//.orderByChild("Time").equalTo("2020/03/03 14:08:48")
	logDHT2.on("child_added", function(sanp) {
		if (!newItems) return;
		var row = sanp.val();
		
		row.Time = new Date(row.Time);
		chart.options.data[0].dataPoints.push({x: row.Time, y: row.Temperature});
		chart.options.data[1].dataPoints.push({x: row.Time, y: row.Humidity});
		chart.render();
		
		$("#temperature > .content").html(row.Temperature + " &deg;C");
		$("#humidity > .content").text(row.Humidity + " %");
		
		$("#status").removeClass("danger").addClass("success");
		$("#status > .content").text("ONLINE");
		
		setTimeoffline();
	});
	
	var now = new Date();
	console.log(comparefirebasetime)
		logDHT2.orderByChild("Time").startAt(comparefirebasetime + "00:00:00").endAt(comparefirebasetimeEnd + "23:59:00").once("value", function(sanp) {
		//.endAt(endDate)
		
		newItems = true;
		var dataRows = sanp.val();
		var lastRows = 0;

		console.log(dataRows);
		$.each(dataRows, function(index, row) {
			row.Time = new Date(row.Time);
			chart.options.data[0].dataPoints.push({x: row.Time, y: row.Temperature});
			chart.options.data[1].dataPoints.push({x: row.Time, y: row.Humidity});
			 lastRows = row;

		});

		console.log("2");
		chart.render();
		

		
		var Arow = lastRows;
		$("#temperature > .content").html(Arow.Temperature + " &deg;C");
		$("#humidity > .content").text(Arow.Humidity + " %");
		
		var now = new Date();
		Arow.Time = new Date(Arow.Time);
		if (Math.round(now) -  Math.round(Arow.Time) < (2 * 60 * 1000)) {
			$("#status").removeClass("danger").addClass("success");
			$("#status > .content").text("ONLINE");
		} else {
			$("#status").removeClass("success").addClass("danger");
			$("#status > .content").text("OFFLINE");
		}
		
		setTimeoffline();
	});
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

<body>

<div class="topnav">
  <a class="active" href="/index.html">Home</a>
  <a href="./everything.php">Overview</a>
  <a href="./graph.php">Daily Chart</a>
  <a href="./everything.php"target="_blank">Open in full screen</a>
  <a href="./logout.php">Logout</a>
</div>


  <div class="container">
	<h1>Temperature &amp; Humidity Dashboard <small>History</small></h1>
	<h2>Please select the range of dates that you would like to visualize</h2>
    <hr />
     
    <!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#overview" aria-controls="home" role="tab" data-toggle="tab">Overview</a></li>
		<li role="presentation"><a href="#history" aria-controls="profile" role="tab" data-toggle="tab" onclick="inputtime">>History</a></li>
	  </ul>

	  <script>
					
	  </script>
    <!-- Tab panes -->
    	<input type="date" id="mydate">
		<input type="date" id="mydateEnd">
		<button>Set Date</button> 
		<div id="chartContainer" style="height: 300px; width: 100%;"></div>
		
	
	
    </div>
  </div>
</body>
</html>
