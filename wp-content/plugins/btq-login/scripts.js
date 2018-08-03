// Aqui se inicializa firebase
var config = {
	apiKey: "AIzaSyAJKAc_-VwG7Lt_LeSjbNnr8LEzms1WJxk",
	authDomain: "btq-ahm-gran-hotel.firebaseapp.com",
	databaseURL: "https://btq-ahm-gran-hotel.firebaseio.com",
	projectId: "btq-ahm-gran-hotel",
	storageBucket: "btq-ahm-gran-hotel.appspot.com",
	messagingSenderId: "241886061865"
};
firebase.initializeApp(config);


//Funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador
firebase.auth().onAuthStateChanged(function(user) {
if (user) {
  // Usuario con sesion iniciada
  var user = firebase.auth().currentUser;
  if(user != null){
    document.getElementById("botones_primarios").style.display = "none";
    var email_id = user.email;
    var name = user.displayName;
    if(name == null )
    document.getElementById("user_para").innerHTML = email_id;
    else
    document.getElementById("user_para").innerHTML = name + "<br/> " +  email_id;
  }
} else {
  // Si el usuario no tiene la sesion iniciada
  document.getElementById("user_div").style.display = "none";
}
});
//Aqui termina la funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador





//Aqui inicia la funcion de registrar un nuevo usuario con email y pass
function nuevo_usuario(){

      var newuserEmail = document.getElementById("new_email_field").value;
      var newuserPass = document.getElementById("new_password_field").value;
      firebase.auth().createUserWithEmailAndPassword(newuserEmail, newuserPass).catch(function(error) {
      // Errores en caso de que no se pueda registrar
      var errorCode = error.code;
      var errorMessage = error.message; 
      window.alert("Error:" + error.message);
      });


      document.getElementById("registro").style.display = "none";  
      document.getElementById("registro_completado").style.display = "block";    
      while (n > 0){
      setTimeout(function(){
        var n = 10;
        var l = document.getElementById("contador");
        window.setInterval(function(){
          l.innerHTML = n;
          n--;
        });
      },1000);
    }
      jQuery('#Registro').modal('hide')
      document.getElementById("user_div").style.display = "block";  
}
//Aqui termina la funcion de registrar un nuevo usuario con email y pass



 //login con correo y contraseña
function login(){

    var userEmail = document.getElementById("email_field").value;
    var userPass = document.getElementById("password_field").value;

    firebase.auth().signInWithEmailAndPassword(userEmail, userPass).catch(function(error) {
      // Errores en caso de que no pueda iniciar sesion
      var errorCode = error.code;
      var errorMessage = error.message;
      document.getElementById("botones_primarios").style.display = "none";
    });
    document.getElementById("user_div").style.display = "block";
}
  //aqui termina login con correo y contraseña




//Aqui inicia el cierre de sesion del usuario de los 3 metodos
function logout(){
    firebase.auth().signOut();
    firebase.auth().signOut().then(function() {
      //Si cierra sesion correctamente
      document.getElementById("user_div").style.display = "none";
      document.getElementById("botones_primarios").style.display = "block";
    }).catch(function(error) {
      //Si sucede algun error
    });
}
//Aqui termina el cierre de sesion del usuario de los 3 metodos




//Aqui inicia funcion para iniciar sesion con google
function google_login(){

var provider = new firebase.auth.GoogleAuthProvider();
  firebase.auth().signInWithPopup(provider).then(function(result) {
    // Te da el token de google. Se usa para iniciar en la api
    var token = result.credential.accessToken;
    // Te da la informacion del usuario
    var user = result.user;
    document.getElementById("user_div").style.display = "block";
    document.getElementById("botones_primarios").style.display = "none";
        // Si no se obtiene el token correctamente se ejecuta la siguiente funcion
    }).catch(function(error) {
    // Errores en caso de no recibir el token correctamente
    var errorCode = error.code;
    var errorMessage = error.message;
    // El correo ya esta registrado en la base de datos
    var email = error.email;
    // Si la credencial de auth ya esta usada.
    var credential = error.credential;
    // ...
    window.alert("Error:" + error.message);
});
}
//Aqui termina funcion para iniciar sesion con google





//Aqui inicia funcion para iniciar sesion con facebook

function facebook_login(){

  var provider = new firebase.auth.FacebookAuthProvider();
  
    firebase.auth().signInWithPopup(provider).then(function(result) {
    // Te da el token de inicio de sesion de facebook
    var token = result.credential.accessToken;
    // Obtiene la informacion del usuario
    var user = result.user;
    document.getElementById("botones_primarios").style.display = "none";
    document.getElementById("user_div").style.display = "block";
    // En caso de no iniciar sesion correctamente se ejecuta la siguiente funcion
  }).catch(function(error) {
    // Errores en caso de no iniciar sesion
    var errorCode = error.code;
    var errorMessage = error.message;
    // El correo ya esta registrado en la base de datos
    var email = error.email;
    // La credencial Auth ya esta usada
    var credential = error.credential;
    // ...
    window.alert("Error:" + error.message);
  });
}
//Aqui termina funcion para iniciar sesion con facebook



function recuperar_contrasena(){

    var auth = firebase.auth();
    var emailAddress =  document.getElementById("recover_email_field").value; ;

    auth.sendPasswordResetEmail(emailAddress).then(function() {
      // Email sent.
    }).catch(function(error) {
      // An error happened.
    });
    document.getElementById("recuperado").style.display = "block";
    document.getElementById("recuperar").style.display = "none";  
}



jQuery(document).ready(function(){
  jQuery('.Input').keypress(function(e){
    if(e.keyCode==13)
    jQuery('.Boton').click();
  });
});
