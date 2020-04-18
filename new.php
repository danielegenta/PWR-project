<?php
	//serve in ogni pagina per recuperare sessione corrente
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Pagina di conferma/errore REGISTRAZIONE</title>
		<meta name="author" content="Daniele Genta" >
		<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    	<link rel="stylesheet" type="text/css" href="./common/style.css" >
		<script src="./common/script.js"></script>
	</head>
	<body class = "home-body">
		<div class = "grid-container">
			<div class = "section-head">
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
			
			
	
			<!--sezione principale-->
			<main>
				<!-- Titolo della pagina-->
				<h1>
					Stato della Registrazione 
				</h1>
				<div class = "main-text">
					<?php
					//controlli lato server per una maggiore sicurezza
					if(isset($_REQUEST['username']) && isset($_REQUEST['password']))
					{
						$username = trim($_REQUEST['username']);
						$password = trim($_REQUEST['password']);

						$regUsername = "/^(?=(.*[\d]){1})(?=.*[A-Za-z\$])[A-Za-z\%][A-Za-z\d\%]{2,5}$/";
						$regPassword = "/^(?=.*[A-Z])(?=.*[a-z])[A-Za-z]{4,8}$/";

						//caso: input non valido,
						//fornisco suggerimento su come correggere l'errore all'utente
						if ( !preg_match($regUsername,$username) )
							echo "<p>ERRORE NELLA REGISTRAZIONE:<br> Formato username non valido! " .
								"\nFormato corretto:\nLunghezza di (4,8) caratteri;" .
								"\nalmeno un carattere maiuscolo ed uno minuscolo.</p>";
						//caso: pwd non valida,
						//fornisco suggerimento su come correggere l'errore all'utente
						elseif( !preg_match($regPassword,$password) )
						{
							echo "<p>ERRORE NELLA REGISTRAZIONE:<br>La password inserita non rispetta gli standard di sicurezza!" .
								 "\nFormato corretto:\nlunghezza di (2,5) caratteri;" .
								"\nalmeno un carattere numerico ed uno non numerico; sono ammessi caratteri alfabetici, numerici ed il carattere %." +
								"\nDeve iniziare con carattere alfabetico o con il carattere %.</p>";
						}
						else
						{
							// dati ricevuti correttamente e query a database
							//utilizzo utente con permessi di lettura, per verificare se utente esiste già
							$con = mysqli_connect("127.0.0.1", "uReadOnly", "posso_solo_leggere", "biblioteca");

							if (mysqli_connect_errno()) 
								echo "<p>ERRORE NELLA REGISTRAZIONE: errore di connessione al DBMS: ".mysqli_connect_error()."</p>\n";
							else
							{
								//uso query parametrizzata per maggiore sicurezza (sqlInjection)
								$stmt = mysqli_prepare($con, "SELECT COUNT(*) AS cnt FROM users WHERE username = ? AND pwd = ?");
								mysqli_stmt_bind_param($stmt, "ss", $username, $password);
								mysqli_stmt_execute($stmt);

								mysqli_stmt_bind_result($stmt, $cnt);
								mysqli_stmt_fetch($stmt);
								mysqli_close($con);
								
								if ($cnt > 0)
									echo "<p>ERRORE NELLA REGISTRAZIONE: esiste già un utente con questo username: $username, prego inserire un nuovo username</p>";
								else
								{
									// Inserisco il nuovo utente valido
									$con = mysqli_connect("127.0.0.1", "uReadWrite", "SuperPippo!!!", "biblioteca");
									$stmt = mysqli_prepare($con, "INSERT INTO users (username, pwd) VALUES (?, ?)");
									mysqli_stmt_bind_param($stmt, "ss", $username, $password);
									mysqli_stmt_execute($stmt);
									
									
									if (mysqli_stmt_affected_rows($stmt) > 0)
										echo "<p>CONFERMA DI AVVENUTA REGISTRAZIONE<br>L'utente: $username e' stato correttamente registrato.</p>";
									else
										echo "<p>ERRORE NELLA REGISTRAZIONE<br>Non e' stato possibile registrare l'utente: $username.</p>";
									
									mysqli_close($con);
								}
							}
						}
					}
					else
						echo "<p>Errore - Dati Mancanti</p>\n";
				?>
			</div>
		</main>
		<footer id = "footer"></footer>
	</div>
</body>
</html>