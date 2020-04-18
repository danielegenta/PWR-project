<?php
	/*
	* Pagina di supporto al login e logout
	* caso login: se non ancora loggato, reindirizzo a login altrimenti ritorno a pagina chiamante
	* caso logout: torno a pagina chiamante dopo aver effettuato il logout (cancello completamente
	* 				la sessione ed i suoi parametri).
	*/

// sessione utilizzata per loggato
if (session_status() !== PHP_SESSION_ACTIVE)
	session_start();

// ho richiesto il login
if (isset($_GET['login'])) 
{
    login();
}

// ho richiesto il logout
else if (isset($_GET['logout'])) 
{
    logout();
}



// Se non sono loggato sono reindirizzato alla paggina di login
// se sono loggato già rimango sulla stessa pagina
function login()
{
	// login attivo solo se non sono loggato
	if (!isset($_SESSION["loggato"]))
		header('Location: '. "../login.html");
	else
		redirect();
}
		
// logout attivo solo se sono loggato
// IMPORTANTE: prima di distruggere la sessione faccio l'unset di tutti i parametri di sessione utilizzati		
function logout()
{
	if (isset($_SESSION["loggato"]))
	{
		// cancello le variabili della sessione
		$_SESSION = array();
		
		// cancella il cookie
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
		
		unset($_SESSION["loggato"]);
		unset($_SESSION["username"]);
		unset($_SESSION["libri_in_prestito"]);
		
		// cancello sessione da disco
		session_destroy();
	}
	//in ogni caso torno a pagina precedente
	redirect();
}

function redirect()
{
	// nel caso di login successful non entro in questo codice perchè sono reindirizzato a libri,
	// nel caso sia già loggato ritorno alla pagina che ha chiamato questa procedura
	if (isset($_SERVER['HTTP_REFERER']))
	{	
		//redirect a pagina chiamante
		header('Location: '. $_SERVER['HTTP_REFERER']);
	}
}


?>