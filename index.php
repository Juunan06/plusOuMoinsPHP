<?php
session_start();

/**
* Fonction replay() : permet de relancer la partie, que ce soit en cours de jeu ou si le joueur gagne
*/
function replay(){
	unset($_SESSION);
	unset($_REQUEST);
	session_destroy();
	$nbrEssais = 0;
	$nbrMystere = -1;
	$win = 0;
	//$lastChoice ="Le joueur n'a pas encore fait de choix";
	upLastChoice("Le joueur n'a pas encore fait de choix");
	session_start();
}

/**
* Fonction upLastChoice() : permet de mettre à jour la variable qui contient le choix du joueur lors du dernier coup
* @param mixed indique la valeur que l'on souhaite donner au dernier coup du joueur
*/
function upLastChoice($newChoice){
	$lastChoice = $newChoice;
	$_SESSION['lastChoice'] = $newChoice;
}

// Si le bouton Réinitialiser est cliqué
if ( isset($_POST['replay']) && !empty($_POST['replay']) ){
	replay();	
}

// Lors de l'arrivé sur la page ou après appuie du bouton de reset
if (empty($_SESSION['nbrMystere'])){

	$_SESSION['nbrMystere'] = rand(0,1000);
	$_SESSION['essais'] = 0;
	$nbrMystere = $_SESSION['nbrMystere'];
	$nbrEssais = $_SESSION['essais'];
	$message = "Début de partie, faites un choix !";
	$lastChoice ="Le joueur n'a pas encore fait de choix";
	$userNbr = NULL;
	$win = 0;

}else{ // Si la partie est déjà initialisée
	$win = 0;
	$nbrMystere = $_SESSION['nbrMystere'];
	$nbrEssais = $_SESSION['essais'];

	// On récupère la valeur du dernier coup du joueur en session, si elle existe
	if (isset($_SESSION['lastChoice'])){
		$lastChoice = $_SESSION['lastChoice'];
	}

	// Lorsque l'on clique pour valider son choix
	if ( isset($_POST['userNbr'])) {
		//echo "/----- SESSION : ".var_dump($_SESSION)."<br />";
		$userNbr = htmlspecialchars($_POST['userNbr']); // On récupère le choix de l'utilisateur
		$message = ''; // On set le message à afficher à vide
		
		// Si l'utilisateur à bien rentré un chiffre
		if (is_numeric($userNbr)){
			$nbrEssais++;
			$_SESSION['essais'] = $nbrEssais;

			// On enregistre son choix si le dernier choix enregistrer n'existe pas ou n'est pas numérique
			if (isset($lastChoice)){
				if (!is_numeric($lastChoice)){
					$lastChoice = $userNbr;
				}
			}else{
				$lastChoice = $userNbr;
			}
			
			// On gère les cas en fonction du chiffre rentré par le joueur
			switch ($userNbr) {
				case ( ($userNbr == $nbrMystere) && ($userNbr <= 1000)  && ($userNbr >= 0) ):
					$message .= "Bravo, tu as trouvé le nombre mystère en ".$nbrEssais." essai(s)";
					$win = 1; // On arrête la partie
					break;
				case ( ($userNbr < $nbrMystere) && ($userNbr <= 1000)  && ($userNbr >= 0) ):
					// Gestion du cas ou le joueur ne fait pas un choix logique
					if ($lastChoice > $userNbr && $lastChoice < $nbrMystere) {
						$message .= "T'es vraiment con! Au coup d'avant tu avais mis : ".$lastChoice." et c'était déjà trop petit!<br />";
					}else{
						$message .= "";
					}
					$message .= "Le nombre mystère est plus GRAND";
					break;
				case ( ($userNbr > $nbrMystere) && ($userNbr <= 1000)  && ($userNbr >= 0) ):
					// Gestion du cas ou le joueur ne fait pas un choix logique
					if ($lastChoice < $userNbr && $lastChoice > $nbrMystere) {
						$message .= "T'es vraiment con! Au coup d'avant tu avais mis : ".$lastChoice." et c'était déjà trop grand!<br />";
					}else{
						$message .= "";
					}
					$message .= "Le nombre mystère est plus PETIT";
					break;

				
				default:
					$message .= "T'es con, tu n'as pas rentré un chiffre compris entre 0 et 1000! <br /> Pour la peine je te compte quand même l'essai !";
					break;
			}

			upLastChoice($userNbr); // On met à jour le dernier choix utilisateur
		}else{
			$message = "T'es con, tu n'as pas rentré un chiffre !";
		}
	}else{
		$message .= "T'es con, tu n'as rien rentré !";
		$userNbr = '';
		upLastChoice($_SESSION['lastChoice']);
	}	

}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jeu du plus ou moins</title>
    <meta name="description" content="Jeu du plus ou moins">
    <!-- FavIcon -->
    <link rel="icon" type="image/png" href="#" />
    <!-- Ajout de polices -->
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!-- <link rel="stylesheet" href="./css/style.css"> -->
  <style>
  	div{
  		padding: 40px;
  		color: #FBFBFB;
  	}

  	#verificationZone{
  		color: black;
  	}

  	#btnReset{
  		margin-top: 20px;
  	}
  </style>

</head>
<body>
	<section class="container-fluid">
		<div class="col-xl-8 offset-xl-2 bg-success text-center">
			Bienvenue sur le jeu du plus ou moins spécial Gilles !<br />
			La règle est simple, tu dois trouver le nombre mystère, il est compris entre 0 et 1000. <br />
			A chaque fois que tu propose un chiffre, je te dirais si tu es au dessus ou en dessous du chiffre mystère.
			<?php
			if($nbrEssais !== 0){
			?>
			<form action='' method='POST' id="btnReset">
				<input type='submit' name='replay' value='Réinitialiser' class='btn btn-warning' />
			</form>
			<?php }	?>
		</div>
		<div class="col-xl-8 offset-xl-2 bg-secondary d-flex flex-row">
			<div class="col-xl-6 d-flex justify-content-center">
				<form action="" method="POST">
					<?php if($win == 0){ ?>
						<label for="userNbr">Entre ton choix :</label>
						<input type="text" name="userNbr">					
						<button type="submit" class="btn btn-success">J'ai choisi!</button>
					<?php } ?>
				</form>
			</div>
			<div class="col-xl-6 d-flex justify-content-center">
				<p class="alert alert-info"><?php echo $message ?></p>
			</div>
		</div>
		<div class="col-xl-8 offset-xl-2 bg-warning" id="verificationZone">
			<strong>Zone de vérification : (pour debug et test)</strong><br />
			<strong>Nombre Mystère :</strong> <?php echo $nbrMystere; ?><br />
			<strong>Choix utilisateur :</strong> <?php echo  ($userNbr != NULL)?$userNbr:'AUCUN'; ?><br />
			<strong>Dernier Choix utilisateur :</strong> <?php echo (isset($lastChoice))?$lastChoice:'AUCUN'; ?><br />
			<strong>Nombre d'essais effectués :</strong> <?php echo $nbrEssais; ?><br />
			******************************************<br />
			<strong>Var Dump $_SESSION :</strong>
			<?php var_dump($_SESSION) ?><br />
			******************************************<br />
			<strong>Var Dump $_REQUEST :</strong>
			<?php var_dump($_REQUEST) ?><br />
		</div>

		
	</section>


<!-- Scripts Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<!-- Scripts JS persos -->
    </body>

</html>