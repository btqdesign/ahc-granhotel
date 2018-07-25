
// Initialize Firebase
var config = {
  apiKey: "AIzaSyAJKAc_-VwG7Lt_LeSjbNnr8LEzms1WJxk",
  authDomain: "btq-ahm-gran-hotel.firebaseapp.com",
  databaseURL: "https://btq-ahm-gran-hotel.firebaseio.com",
  projectId: "btq-ahm-gran-hotel",
  storageBucket: "btq-ahm-gran-hotel.appspot.com",
  messagingSenderId: "241886061865"
};
firebase.initializeApp(config);




// Funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador
firebase.auth().onAuthStateChanged(function(user){
	if (user) {
		// Usuario con sesion iniciada
		var user = firebase.auth().currentUser;
		
		/*if (user != null) {
			var email_id = user.email;
			var name = user.displayName;
			if(name == null ) document.getElementById("user_para").innerHTML = email_id;
			else document.getElementById("user_para").innerHTML = name + "<br/> " +  email_id;
		}*/
	}
	else {
		// Si el usuario no tiene la sesion iniciada
	}
});
// Aqui termina la funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador


function registro(){
    var newuserEmail = document.getElementById("new_email_field").value;
    var newuserPassword = document.getElementById("new_password_field").value;
    
    firebase.auth().createUserWithEmailAndPassword(newuserEmail, newuserPassword).catch(function(error) {
    // Handle Errors here.
    var errorCode = error.code;
    var errorMessage = error.message;
    // ...
  });
}

function login_EP(){
    
    var Email = document.getElementById("email_field").value;
    var Password = document.getElementById("password_field").value;

    firebase.auth().signInWithEmailAndPassword(email, password).catch(function(error) {
        // Handle Errors here.
        var errorCode = error.code;
        var errorMessage = error.message;
        // ...
      });

}