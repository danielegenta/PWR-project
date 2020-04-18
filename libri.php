<?php
	// sessione utilizzata per loggato e numero di libri
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Libri</title>
		<meta name="author" content="Daniele Genta" >
		<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
    	<link rel="stylesheet" type="text/css" href="./common/style.css" >
		<script src="./common/script.js"></script>
		
		<script>
			// form prestito (numero di giorni)
			function validaForm(giorni)
			{
				var result = true;
				
				// campo giorni valido?
				var regGiorni = /^[1-9]*$/;
				if (!regGiorni.test(giorni) || giorni == "")
				{
					alert("Attenzione, valore dei giorni non valido, devi inserire un valore numerico");
					result = false;
				}
				
				//almeno un checkbox?
				var checkboxes = document.querySelectorAll('input[type="checkbox"]');
				var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
				
				if (!checkedOne)
				{
					alert("Attenzione, devi selezionare almeno un libro da prendere in prestito");
					result = false;
				}
				
				return result;
			}
		</script>
    
	</head>
	<body class = "home-body">
		<?php
		
		// Restituzione
		if(isset($_REQUEST['restituisci']))
		{
			$idRestituzione = key(($_REQUEST['restituisci']));
			
			// query restituzione
			// prima di query di aggiornamento tenere traccia di quanti giorni ho tenuto il libro
			// (query di lettura)
			$query = "SELECT titolo, data from books WHERE id = ?";
			queryRead($query, "giorni_prestito", $idRestituzione);
			
			$query = "UPDATE books SET Prestito = ?, Giorni = ? , data = ? WHERE id = ?";
			queryWrite($query, "restituzione", $idRestituzione, 0 );
		}
		
		// Prestito 
		if(isset($_REQUEST['prestito']))
		{
			//validazione dati server-side
			$giorni = trim($_REQUEST["giorni"]);
			$regGiorni = "/^[1-9]*$/";
			if ( !preg_match($regGiorni,$giorni) )
				echo "<p>ERRORE: Giorni deve essere un dato numerico.</p>";
			
			// update valido
			else
			{
				foreach($_REQUEST['prestito'] as $key => $value)
				{
					// controllo limite di libri in prestito
					if ($_SESSION["libri_in_prestito"] == 3)
						echo "<p>ERRORE: Ogni utente può avere un massimo di 3 libri in prestito.</p>";
					else
					{
						$query = "UPDATE books SET Prestito = ? , Giorni = ? , data = ? WHERE id = ?";
						queryWrite($query, "prestito", $key, $giorni);
					}
				}
			}
		}
		
		// Funzione comune per query di update (restituzione e prestito)
		function queryWrite($query, $rs, $id, $giorni)
		{
			$con = mysqli_connect("127.0.0.1", "uReadWrite", "SuperPippo!!!", "biblioteca");
			if (mysqli_connect_errno()) 
			{
				echo "<p>ERRORE nell'aggiornamento dati: errore nella connessione al db.</p>";
				exit();
			}
			if ($stmt = mysqli_prepare($con, $query))
			{
				$username = $_SESSION["username"];
				if ($rs == "restituzione")
				{
					$date = date("0000-00-00 00:00:00");
					$username = "";
					mysqli_stmt_bind_param($stmt, "sdsd", $username, $giorni, $date, $id);
				}
				else if ($rs == "prestito")
				{
					$today = date("Y-m-d H:i:s");
					mysqli_stmt_bind_param($stmt, "sdsd", $username, $giorni, $today, $id);
				}
				
				mysqli_stmt_execute($stmt);
				
				// success
				if (mysqli_stmt_affected_rows($stmt) > 0)
				{
					mysqli_close($con);
					if ($rs == "restituzione")
					{
						$_SESSION["libri_in_prestito"] = $_SESSION["libri_in_prestito"]-1;
						header('Location: '. "./restituzione.php");
					}
					else if ($rs == "prestito")	
					{					
						$_SESSION["libri_in_prestito"] = $_SESSION["libri_in_prestito"]+1;
						header('Location: '. "./libri.php");
					}
				}
				else
					echo "<p>Errore nell'aggiornamento dei dati (operazione: $rs)</p>";
			}
			else	
				echo "<p>Errore nell'aggiornamento dei dati (operazione: $rs), problema nella preparazione della query.</p>";
			mysqli_close($con);
		}
		
		// funzione comune per le query di lettura
		// uso query parametrizzate
		function queryRead($query, $rs, $par)
		{
			$con = mysqli_connect("127.0.0.1", "uReadOnly", "posso_solo_leggere", "biblioteca");
		
			/* check connection */
			if (mysqli_connect_errno()) {
				printf("<p>ERRORE nella lettura, errore di connessione.</p>");
				exit();
			}
			
			if ($stmt = mysqli_prepare($con, $query))
			{
				$risultato = "";
				switch ($rs)
				{
					case "disponibili":
					case "totali":
						//nessun parametro da concatenare
						mysqli_stmt_execute($stmt);
						//risultato query = risultato che mi serve
						mysqli_stmt_bind_result($stmt, $risultato);
						mysqli_stmt_fetch($stmt);
					break;
					case "listaPrestito":
						mysqli_stmt_bind_param($stmt, "s", $par);
						mysqli_stmt_execute($stmt);
						mysqli_stmt_store_result($stmt);
						mysqli_stmt_bind_result($stmt, $id, $autori, $titolo);
						if (mysqli_stmt_num_rows($stmt) > 0)
						{
							$risultato = "<form name = 'formRestituisci' method = 'POST' action = 'libri.php'>";
							$risultato = $risultato . "<table class='table'><tr class = 'table-header'><th class = 'header_item'>Id</th><th class = 'header_item'>Autore</th><th class = 'header_item'>Titolo</th><th class = 'header_item'>Restituisci</th></tr>";
							while (mysqli_stmt_fetch($stmt))
							{
								$risultato = $risultato ."<tr class = 'table-row'>
												<td class = 'table-data'>" . $id . "</td>
												<td class = 'table-data'>" . $autori . "</td>
												<td class = 'table-data'>" . $titolo . "</td>
												<td class = 'table-data'><input type = 'submit' value = 'restituisci' name = 'restituisci[$id]'></td>
												</tr>";
							}
							$risultato = $risultato . "</table></form>";
						}
						else
							$risultato = "nessun libro attualmente in prestito";
					break;
					case "listaBiblioteca":
						mysqli_stmt_execute($stmt);
						mysqli_stmt_store_result($stmt);
						mysqli_stmt_bind_result($stmt, $id, $autori, $titolo, $prestito, $data, $giorni);
						if (mysqli_stmt_num_rows($stmt) > 0)
						{
							$risultato = "<form name = 'formPrendiInPrestito' onSubmit = 'return validaForm(giorni.value)' method = 'POST' action = 'libri.php'>";
							$risultato = $risultato . "<table class = 'table'><tr class='table-header'><th class = 'header_item'>Id</th><th class = 'header_item'>Autore</th><th class = 'header_item'>Titolo</th><th class = 'header_item'>Presta</th></tr>";
							while (mysqli_stmt_fetch($stmt))
							{
								$col = "";
								// verifico se il prestito è scaduto basandomi su informazioni date da db
								if  ( $prestito != "" )
								{	
									$startdate = $data;
									$expire = strtotime($startdate. ' + '. $giorni . ' days');
									$today = strtotime("today");
									if($today >= $expire)
										$col = "prestito scaduto";
									else
										$col = "in prestito";
								}
								else
									$col = "<input type = 'checkbox' name  = 'prestito[$id]'>";
									$risultato = $risultato ."<tr class = 'table-row'>
											<td class = 'table-data'>" . $id . "</td>
											<td class = 'table-data'>" . $autori . "</td>
											<td class = 'table-data'>" . $titolo . "</td>
											<td class = 'table-data'>$col</td>
											</tr>";
								}
							
							$risultato = $risultato . "</table>
										<p><input type = 'text' name = 'giorni' placeholder = 'Giorni di prestito...'>
										<input type = 'submit' value = 'Prestito'></p>
										</form>";
						}
						else
							$risultato = "<p>Nessun libro attualmente disponibile.</p>";
					break;
					case "giorni_prestito": 
						
						// non uso il campo giorni perchè se restituissi il libro a prestito
						// scaduto mi darebbe un valore sbagliato e anche se lo restituissi prima
						
						mysqli_stmt_bind_param($stmt, "d", $par);
						mysqli_stmt_execute($stmt);
						mysqli_stmt_store_result($stmt);
						mysqli_stmt_bind_result($stmt, $titolo, $data);
						mysqli_stmt_fetch($stmt);
						
						$now = time();
						$startDate = strtotime($data);
						$datediff = $now - $startDate;
						
						$_SESSION["restituzione_giorni_prestito"] = (int)($datediff/86400);
						$_SESSION["restituzione_titolo"] = $titolo;
						
						$risultato = "ok";
					break;
				}
				mysqli_stmt_close($stmt);
			}
			mysqli_close($con);
			return $risultato;
		}
	?>
		
		<div class = "grid-container">
			<div class = "section-head">
				<header id = "header"></header>
			
				<!-- menu -->
				<nav class="menu">
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
								// unlogged
								echo "Anonimo";
								echo "<br>libri in prestito: 0";
							}
						}
						else
						{
							// unset
							echo "Anonimo<br>";
							echo "libri in prestito: 0";
						}
					?>
				</div>
			</div>
		
		<!-- Sezione principale -->
		<main class="main-libri">
			<!-- Titolo della pagina-->
			<h1>
				Libri
			</h1>
	
			<div class="main-text">
				<?php
					if (isset($_SESSION["loggato"]) && $_SESSION["loggato"] == 1)
					{
						echo "<h2>Sezione Loggato</h2>";
						
						echo "<p class = 'main-text-libri-section'>Sezione 1: libri in prestito</p>";
						$utente = $_SESSION["username"];
						$query = "SELECT id, autori, titolo FROM Books WHERE prestito = ?";
						echo queryRead($query, "listaPrestito", $utente);
						
						
						echo "<p class = 'main-text-libri-section'>Sezione 2: libri disponibili</p>";
						$query = "SELECT id, autori, titolo, prestito, data, giorni FROM books";
						echo queryRead($query, "listaBiblioteca", null);
					}
					else
					{
						// presentazione
						echo "<h2>Sezione non loggato</h2>";
						echo "<p>Qui numero dei libri in biblioteca totale e disponibili per il prestito.";
						echo "<br>Prego effettuare il login per prendere in prestito dei libri.</p>";
						
						// libri totali 
						$query1 = "SELECT COUNT(*) as totali FROM Books";
						echo "<br>Totali: " . queryRead($query1, "totali", null);
						
						// libri disponibili per il prestito
						$query2 = "SELECT COUNT(*) as disponibili FROM Books WHERE Prestito = ''"; //libri disponibili => prestito = ""
						echo "<br>Disponibili: " . queryRead($query2, "disponibili", null);
					}
				?>
			</div>
		</main>
		
		<footer id = "footer"></footer>
		</div>
	</body>
</html>