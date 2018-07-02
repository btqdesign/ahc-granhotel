
//funcion para conectar con firebase>





  //Aqui termina la conexcion a firebase



//Funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador

firebase.auth().onAuthStateChanged(function(user) {
if (user) {
  // Usuario con sesion iniciada

  document.getElementById("user_div").style.display = "block";
  document.getElementById("login_div").style.display = "none";

  var user = firebase.auth().currentUser;

  if(user != null){

    var email_id = user.email;
    var name = user.displayName;
    var photoUrl = user.photoURL;
    document.getElementById("user_para").innerHTML = "Bienvenido usuario : " + email_id + " " +  name;

  }

} else {
  // Si el usuario no tiene la sesion iniciada

  document.getElementById("user_div").style.display = "none";
  document.getElementById("login_div").style.display = "block";

}
});

//Aqui termina la funcion para mantener la sesion iniciada cuando se cierra la pestaña o navegador





//Aqui inicia la funcion de registrar un nuevo usuario con email y pass
function nuevo_usuario(){

document.getElementById("user_div").style.display = "none";
document.getElementById("login_div").style.display = "block";
document.getElementById("registro").style.display = "none";  

var newuserEmail = document.getElementById("new_email_field").value;
var newuserPass = document.getElementById("new_password_field").value;

firebase.auth().createUserWithEmailAndPassword(newuserEmail, newuserPass).catch(function(error) {
// Errores en caso de que no se pueda registrar
var errorCode = error.code;
var errorMessage = error.message; 
window.alert("Error : " + errorMessage);
});
var user = firebase.auth().currentUser;

user.sendEmailVerification().then(function() {
  // Email sent.
}).catch(function(error) {
  // An error happened.
});
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
  window.alert("Error : " + errorMessage);
});

}
  //aqui termina login con correo y contraseña




//Aqui inicia el cierre de sesion del usuario de los 3 metodos
function logout(){
firebase.auth().signOut();
firebase.auth().signOut().then(function() {
  //Si cierra sesion correctamente
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
    window.alert("Error : " + errorMessage);
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
}




  //Aqui inicia la funcion para ocultar la pestaña de inicio y mostrar la de registro
function pestaña_registro(){
  document.getElementById("registro").style.display = "inline-block";
  document.getElementById("login_div").style.display = "none";
}
  //Aqui termina la funcion para ocultar la pestaña de inicio y mostrar la de registro





  //Aqui inicia la funcion para ocultar cualquier pestaña y mostrar la de inicio
function pestaña_inicio(){
document.getElementById("login_div").style.display = "block";
document.getElementById("user_div").style.display = "none";
document.getElementById("registro").style.display = "none";  
}
    //Aqui termina la funcion para ocultar cualquier pestaña y mostrar la de inicio

function pestaña_recuperar(){
  document.getElementById("recuperar").style.display = "block";
  document.getElementById("login_div").style.display = "none";
  document.getElementById("user_div").style.display = "none";
  document.getElementById("registro").style.display = "none";  

}