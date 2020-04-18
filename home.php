<?php
	/*
		Spiegazione LOGIN e LOGOUT: dato che da ogni pagina posso effettuare login e logout (o non far nulla)
		decido di implementare una pagina lato server (/common/login_status.php) la quale gestirà in automatico
		le richieste di login, questo mi permette una maggior manutenibilità (non ripeto il codice in ogni pagina)
		e leggibilità
	*/
	
	// sessione utilizzata per loggato
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();
	
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Home</title>
		<meta name="author" content="Daniele Genta" >
		<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
		<!-- foglio di stile comune a tutte le pagine (common/style.css) -->
    	<link rel="stylesheet" type="text/css" href="./common/style.css" >
		<!-- foglio di script comune a tutte le pagine (common/script.js) -->
		<script src="./common/script.js"></script>
    </head>
	<body class= "home-body">
	<div class = "grid-container">
	
		<!-- barra superiore (header menu e login status)-->
		<div class = "section-head">
		
			<!-- Header e footer dinamici (common/script.js) -->
			<header id="header"></header>
		
			<!-- Menu -->
			<nav id = "menu" class = "menu">
			  <a href="./home.php">Home</a>
			  <!--in html & codificata con &amp; , secondo amp utile in php-->
			  <a href = "./common/login_status.php?login=1">Login</a>
			  <a href = "./common/login_status.php?logout=1">Logout</a>
			  <a href="./new.html">New</a>
			  <a href="./libri.php">Libri</a>
			</nav>
			
			<!-- Login status -->
			<div class = "login-status">
				<?php
					// Session[loggato] => sono loggato o no
					// Session[username] => username con cui sono loggato
					// Session[libri_in_prestito] => libri attualmente in prestito ad utente loggato
					if (isset($_SESSION["loggato"]))
					{
						if ($_SESSION["loggato"] == 1)
						{
							// user logged
							echo $_SESSION["username"];
							echo "<br>Libri in prestito: " . $_SESSION["libri_in_prestito"];
						}
						else
						{
							// user not-logged
							echo "Anonimo";
							echo "<br>libri in prestito: 0";
						}
					}
					else
					{
						// user unset
						echo "Anonimo";
						echo "<br>libri in prestito: 0";
					}
				?>
			</div>
		</div>
		
		<!-- Sezione principale -->
		<main>
			<!-- Titolo della pagina-->
			<h1>
				Homepage Biblioteca
			</h1>
			
			<!-- Descrizione del sito-->
			<div class = "main-text-home">
				<p>Presentazione generale del sito:</p>
				Questo sito permette la gestione di una biblioteca.
				E' possibile, registrandosi e loggandosi, tenere traccia dei libri presi in prestito.
				Accedendo alla sezione libri è possibile restituire i libri attualmente in prestito e 
				prenderne di nuovi, oltre a consultare quanti e quali libri siano effettivamente disponibili
				o in prestito ad altri utenti.
			</div>
		</main>
		
		<!-- Footer dinamico -->
		<footer id = "footer"></footer>
	</div>
	</body>
</html>