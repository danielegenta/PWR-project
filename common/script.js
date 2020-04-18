/*
*	In questo documento genero dinamicamente header e footer su tutte le pagine
*	ho deciso di mantenere gli script specifici di ogni pagina sulla pagina stessa
*	ed in questo documento le funzioni comuni
*/

// Generazione dinamica di header e footer, utile al fine di manutenzione
// funzione invocata in ogni pagina al completamento del caricamento 
document.addEventListener('DOMContentLoaded', function () 
{
	//header 
	document.getElementById("header").innerHTML = "Progetto PWR: Biblioteca";

	//footer 
	document.getElementById("footer").innerHTML = "nome del file: "+ window.location.href  +" - Autore: Daniele Genta";
});
