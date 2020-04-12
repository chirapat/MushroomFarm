console.log("banana");

<script src="https://www.gstatic.com/firebasejs/7.8.0/firebase-app.js"></script>
const firebaseConfig = {
    apiKey: "AIzaSyBKoHODu0sCdbpEe6d1XmnUd_2_T0Ss5Mk",
    authDomain: "seniorproject-muic.firebaseapp.com",
    databaseURL: "https://seniorproject-muic.firebaseio.com",
    projectId: "seniorproject-muic",
    storageBucket: "seniorproject-muic.appspot.com",
    messagingSenderId: "253133476903",
    appId: "1:253133476903:web:96407886ab1cab0b"
  };
  firebase.initializeApp(config);

 
  
  var database = firebase.database();

  var userId = firebase.auth().currentUser.uid;
    return firebase.database().ref('/seniorproject-muic/sensor/am2320(new)/bus1/-Luhqq_dp7DnfJPLWNbb/' + userId).once('value').then(function(snapshot) {
    var temperature = (snapshot.val() && snapshot.val().Temperature) || 'Temperature';
    var humidity = (snapshot.val() && snapshot.val().Humidity) || 'Humidity';
    var light = (snapshot.val() && snapshot.val().Luminosity)|| 'Luminosity';

    
  });


function writeNewPost(humidity, temperature, light) {
    // A post entry.
    var postData = {
      humidity: humidity,
      temperature: temperature,
      light: light,
 
    };
  
    // Get a key for a new Post.
    var newPostKey = firebase.database().ref().child('posts').push().key;
  
    // Write the new post's data simultaneously in the posts list and the user's post list.
    var updates = {};
    updates['/posts/' + newPostKey] = postData;
    updates['/user-posts/' + uid + '/' + newPostKey] = postData;
  
    return firebase.database().ref().update(updates);
  }


//eglalita@gmail.com


  // Initialize Firebase

//  <script src="https://www.gstatic.com/firebasejs/7.8.0/firebase-app.js"></script>


//   // Your web app's Firebase configuration
//   var firebaseConfig = {
//     apiKey: "AIzaSyBKoHODu0sCdbpEe6d1XmnUd_2_T0Ss5Mk",
//     authDomain: "seniorproject-muic.firebaseapp.com",
//     databaseURL: "https://seniorproject-muic.firebaseio.com",
//     projectId: "seniorproject-muic",
//     storageBucket: "seniorproject-muic.appspot.com",
//     messagingSenderId: "253133476903",
//     appId: "1:253133476903:web:96407886ab1cab0b"
//   };
//   // Initialize Firebase
//   firebase.initializeApp(firebaseConfig);

//   firebase.initializeApp(config);
//   var database = firebase.database();
//   let ref = firebase.database().ref("seniorproject-muic");
//   let self = this;
//   ref.child(this.sensor).child('am2320(new)').child(bus1).on( "value",function(snapshot){
  
//     console.log(snapshot.val().info)

//   });
  