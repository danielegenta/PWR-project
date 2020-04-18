<?php
	//serve in ogni pagina per recuperare sessione corrente
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Pagina di conferma restituzione</title>
		<meta name="author" content="Daniele Genta" >
		<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    	<link rel="stylesheet" type="text/css" href="./common/style.css" >
		<script src="./common/script.js"></script>
	</head>
	<body class = "home-body">
		<div class = "grid-container">
			<div class = "section-head">
				<!-- Header -->
				<header id = "header"></header>

				<!-- menu -->
				<nav id = "menu" class = "menu">
				  <a href="./home.php">Home</a>
				  <a href = "./common/login_status.php?login=1">Login</a>
				  <a href = "./common/login_status.php?logout=1">Logout</a>
				  <a href="./new.html">New</a>
				  <a href="./libri.php">Libri</a>
				</nav>
				
				<!-- Login status -->
				<div class = "login-status">
					<?php
						if (isset($_SESSION["loggato"]))
						{
							if ($_SESSION["loggato"] == 1)
							{
								echo $_SESSION["username"];
								echo "<br>Libri in prestito: " . $_SESSION["libri_in_prestito"];
							}
							else
							{
								//unlogged
								echo "Anonimo";
								echo "<br>libri in prestito: 0";
							}
						}
						else
						{
							//unset
							echo "Anonimo<br>";
							echo "libri in prestito: 0";
						}
					?>
				</div>
			</div>
			
			
			<!-- Sezione principale -->
			<main>
				<!-- Titolo della pagina-->
				<h1>
					Conferma Restituzione
				</h1>
		
				<div class = "main-text">
					<?php
						// L'unico modo per accedere a questa pagina è la avvenuta restituzione 
						// nella pagina libri.php, questa è solo una pagina di congerma visiva
						if (isset($_SESSION["restituzione_giorni_prestito"]) && isset($_SESSION["restituzione_titolo"]))
						{
							$giorni = $_SESSION["restituzione_giorni_prestito"];
							$titolo = $_SESSION["restituzione_titolo"];
							echo "<p>Restituzione di $titolo avvenuta con successo.<br>Libro tenuto in prestito per $giorni giorni.</p>";
						}
						else
							echo "<p>Errore nella restituzione del libro</p>";
					?>
				</div>
			</main>
			<footer id = "footer"></footer>
		</div>
	</body>
</html>