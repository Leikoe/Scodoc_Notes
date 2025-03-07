<?php
	class Absences {
		/************************************
		* setAbsence
		*	Créé ou modifie le fichier d'absence d'un étudiant
		*
		************************************/
		public static function setAbsence($enseignant, $semestre, $matiere, $matiereComplet, $etudiant, $date, $debut, $fin, $statut){
			global $path;

			$debut = floatval($debut);
			$fin = floatval($fin);
			
			$dir = "$path/data/absences/$semestre/";
			$file = $dir.$etudiant.'.json';
	
			if(!is_dir($dir)){
				mkdir($dir, 0774, true);
			}
	
			if(!is_file($file)){ // Pas encore de fichier d'absence pour cet étudiant

				$json = [
					$date => [
						self::newAbsence($enseignant, $matiere, $matiereComplet, $debut, $fin, $statut)
					]
				];

			} else { // Fichier présent

				$json = json_decode(file_get_contents($file), true);

				if (!isset($json[$date])){	// Date non présente

					$json[$date] = [
						self::newAbsence($enseignant, $matiere, $matiereComplet, $debut, $fin, $statut)
					];

				} else { // Date présente
					
					$found = false;
					for($i=0 ; $i<count($json[$date]) ; $i++){	// Pour chaque absence de la date

						if ( $json[$date][$i]['debut'] == $debut // Même créneau
							&& $json[$date][$i]['fin'] == $fin ) {
							
							if ($json[$date][$i]['enseignant'] == $enseignant) {
								$found = true;

								if($statut == 'unset'){
									array_splice($json[$date], $i, 1);
									if(count($json[$date]) == 0){
										unset($json[$date]);
									}
								} else {
									$json[$date][$i]['statut'] = $statut;
								}
								
								break;
							} else {
								return ['problem' => 'Une absence est déjà renseigné sur ce créneau par ' . $json[$date][$i]['enseignant']];
							}

						} elseif ( $json[$date][$i]['debut'] >= $fin	// N'existe pas encore
							|| $json[$date][$i]['fin'] <= $debut ){

							continue;

						} else {	// A cheval

							return ['problem' => 'Le créneau est à cheval avec une autre absence renseignée par ' . $json[$date][$i]['enseignant']];
							
						}
					}

					if(!$found){
						$json[$date][] = self::newAbsence($enseignant, $matiere, $matiereComplet, $debut, $fin, $statut);
					}
				}
			}

			
			if(count($json) == 0){
				unlink($file);
			}else{
				file_put_contents(
					$file, 
					json_encode($json)
				);
			}

			return ['result' => 'OK'];
		}
	
		private static function newAbsence($enseignant, $matiere, $matiereComplet, $debut, $fin, $statut){
			return [
				"debut" => $debut,
				"fin" => $fin,
				"statut" => $statut,
				"justifie" => false,
				"enseignant" => $enseignant,
				"matiere" => $matiere,
				"matiereComplet" => $matiereComplet
			];
		}
	/************************************
	* getAbsence
	*	Récupère les absences d'un semestre ou d'un étudiant en fonction de si le paramètre $etudiant est défini ou non
	*
	*	Retour : 
	*		[assoc. array] absences d'un étudiant
	*		[array][assic. array] liste des absences d'un étudiant
	*
	************************************/
		public static function getAbsence($semestre, $etudiant = ''){
			global $path;
			$dir = "$path/data/absences/$semestre/";
			if($etudiant == ''){
				$output = [];
				$listFiles = [];
				if(is_dir($dir)){
					$listFiles = scandir($dir);
				}
				
				foreach($listFiles as $file){
					if($file != "." && $file != ".."){
						$output[substr($file, 0, -5)] = json_decode(file_get_contents($dir.$file));
					}
				}
			} else {
				$file = $dir.$etudiant.'.json';
				if(file_exists($file)){
					$output = json_decode(file_get_contents($file));
				} else {
					$output = '';
				}
			}
			
			return $output;
		}

	/************************************
	* setJustify
	*	Justification a true ou false
	*
	************************************/
		public static function setJustifie($semestre, $etudiant, $date, $debut, $justifie){
			global $path;
			$dir = "$path/data/absences/$semestre/";
			$file = $dir.$etudiant.'.json';
			
			$debut = floatval($debut);

			$data = json_decode(file_get_contents($file), true);

			$dates = $data[$date];

			for($i=0 ; $i<count($dates) ; $i++){
				if($dates[$i]['debut'] == $debut){
					$data[$date][$i]['justifie'] = $justifie === 'true';
					break;
				}
			}

			file_put_contents(
				$file, 
				json_encode($data)
			);

			return ['result' => 'OK'];
		}
	}
