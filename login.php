<?php
	/*
	* Questa pagina non viene normalmente visualizzata, in quanto: i tentativi di login 
	* non validi vengono fermati dalla pagina login.html attraverso la funzione validaForm
	* per una maggiore sicurezza ho implmentato anche i controlli lato server ed i relativi
	* messaggi di errore. (caso di dati manipolati...) e i tentativi validi portano alla visualizzazion
	* della pagina libri.php
	**/

	//serve in ogni pagina per recuperare sessione corrente
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Pagina di errore LOGIN</title>
		<meta name="author" content="Daniele Genta" >
		<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    	<link rel="stylesheet" type="text/css" href="./common/style.css" >
		<script src="./common/script.js"></script>
	</head>
	<body class="home-body">
		<div class = "grid-container">
				<div class = "section-head">
				
					<!-- Header e footer dinamici (script.js) -->
					<header id="header"></header>
					
					<!-- menu -->
					<nav id = "menu" class = "menu">
					  <a href="./home.php">Home</a>
					  <a href = "./common/login_status.php?login=1">Login</a>
					  <a href = "./common/login_status.php?logout=1">Logout</a>
					  <a href="./new.html">New</a>
					  <a href="./libri.php">Libri</a>
					</nav>
				</div>
				
				<!--sezione principale-->
				<main>
					<!-- Titolo della pagina-->
					<h1>
						Errore di login
					</h1>
					<div class = "main-text">
						<?php
							function query($con, $query, $username, $password, $op)
							{
								// valore di return (-1 => errore)
								$cnt = -1;
								if ($stmt = mysqli_prepare($con, $query))
								{
									if ( $op == "esiste_user")
										mysqli_stmt_bind_param($stmt, "ss", $username, $password);
									else if ($op == "libri_in_prestito")
										mysqli_stmt_bind_param($stmt, "s", $username);
									mysqli_stmt_execute($stmt);
									mysqli_stmt_bind_result($stmt, $cnt);
									mysqli_stmt_fetch($stmt);
								}
								mysqli_close($con);
								return $cnt;
							}
							
							// Controlli lato server per una maggiore sicurezza
							if(isset($_REQUEST['username']) && isset($_REQUEST['password']))
							{
								$username = trim($_REQUEST['username']);
								$password = trim($_REQUEST['password']);

								$regUsername = "/^(?=(.*[\d]){1})(?=.*[A-Za-z\$])[A-Za-z\%][A-Za-z\d\%]{2,5}$/";
								$regPassword = "/^(?=.*[A-Z])(?=.*[a-z])[A-Za-z]{4,8}$/";

								if ( !preg_match($regUsername,$username) )
									echo "<p>ERRORE NEL LOGIN: Formato username non valido! ".
										"\nFormato corretto:\nLunghezza di (4,8) caratteri;" .
										"\nalmeno un carattere maiuscolo ed uno minuscolo</p>";
								else if( !preg_match($regPassword,$password) )
									echo "<p>ERRORE NEL LOGIN: password non valida." .
										"\nFormato corretto:\nlunghezza di (2,5) caratteri;" .
										"\nalmeno un carattere numerico ed uno non numerico; sono ammessi caratteri alfabetici, numerici ed il carattere %." .
										"\nDeve iniziare con carattere alfabetico o con il carattere %.</p>";
								
								// dati ricevuti correttamente e query a database
								else
								{
									// utilizzo utente con privilegi di sola lettura
									$con = mysqli_connect("127.0.0.1", "uReadOnly", "posso_solo_leggere", "biblioteca");

									if (mysqli_connect_errno()) 
										echo "<p>ERRORE NEL LOGIN: Errore connessione al DBMS: ".mysqli_connect_error()."</p>\n";
									else
									{
										//uso query parametrizzata per maggiore sicurezza (sqlInjection)
										$cnt = query($con, "SELECT COUNT(*) FROM users WHERE username = ? AND pwd = ?", $username, $password, "esiste_user");
										if ($cnt > 0)
										{
											$con = mysqli_connect("127.0.0.1", "uReadOnly", "posso_solo_leggere", "biblioteca");
											$cnt1 = query($con, "SELECT COUNT(*) AS cnt FROM books WHERE prestito = ?", $username, "", "libri_in_prestito");
											if ($cnt1 != -1)
											{
												// tengo traccia di chi è loggato (elimino sessione a logout)
												// (solo se entrambe le query non vanno in errore)
												$_SESSION["loggato"] = 1;
												$_SESSION["username"] = $username;
												
												$_SESSION["libri_in_prestito"] = $cnt1;
												
												//username per autofill
												//utilizzo cookie per scadenza fra due giorni
												$date_of_expiry = time() + (3600*48) ; //secondi in due giorni
												setcookie("username", $username, $date_of_expiry);
												
												// Se login successful reindirizzo in automatico a pagina libri
												header('Location: '. "./libri.php");
											}
											else
												echo "ERRORE NEL LOGIN: problema nella lettura dei libri in prestito";
										}
										else
											echo "ERRORE NEL LOGIN: nessuno username corrispondente.";
									}
								}
							}
							else
								echo "<p>ERRORE NEL LOGIN: Dati Mancanti.</p>\n";
						?>
					</div>
				</main>
			<footer id = "footer"></footer>
		</div>
	</body>
</html>