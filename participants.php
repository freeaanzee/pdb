<?php
	
	# Frederik Neirynck 2015-2015
	# Version 1.4.1
	
	# WHAT'S NEW:	- categoriebepaling aangepast aan het nieuwe VQR-systeem
	#				- verbeteringen aan het 'first come, first served'-principe
	#				- terugbetalingsboodschap weergeven indien er geannuleerd wordt bij quiz met verplichte (en reeds geregistreerde) betaling
	#				- statistieken bevatten niet langer de reserveploegen
	#				- automatische reload na registreren van betaling
	#				- enkel de zinvolle mailopties worden nog getoond
	#				- de 19 Brusselse gemeenten toegevoegd 
	#				- code compacter geschreven
	
	# TO DO:		- optionele gebruikerstrings toevoegen
	#				- echte WP-plugin bouwen
	
	# WATCH OUT:	- voer deze query uit om een tabel zelf te kunnen bewerken: ALTER TABLE data_cf7dbplugin_submits ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY
	#				- bij MySQL-query's moeten veldwaarden door single quotes omringd worden (= text) behalve submit_time (= number)
	#				- trailing spaces in CF7-velden worden verwijderd, gebruik '&nbsp;' voor de zekerheid
	
	require_once ABSPATH.'wp-config.php';
	require_once ABSPATH.'wp-content/plugins/contact-form-7-to-database-extension/CFDBFormIterator.php';
	
	$exp = new CFDBFormIterator();
	$opt = new Options($GLOBALS['pymt']);
	$url = explode( '?', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$ID = url_to_postid($url[0]);
	
	# Verifieer en behandel de inschrijvingen
	if ( $ID == get_page_by_title($opt->getSignupTitle())->ID ) {
		add_filter ( 'wpcf7_validate_email*', 'duplicateValidationCheck', 1, 2 );
		add_filter ( 'wpcf7_validate_text*', 'duplicateValidationCheck', 1, 2 );
		add_filter ( 'wpcf7_posted_data', 'formatInscription', 5, 1 );
	}
	
	# Behandel de bevestigingen
	if ( $ID == get_page_by_title($opt->getConfirmTitle())->ID ) {
		add_filter ( 'wpcf7_posted_data', 'formatConfirmation', 5, 1 );
	}
	
	# Registreer de betalingen
	if ( $ID == get_page_by_title($opt->getPayTitle())->ID ) {
		add_filter ( 'wpcf7_posted_data', 'formatPayment', 5, 1 );
	}
	
	# Verifieer de ophalingen
	if ( $ID == get_page_by_title($opt->getRetrieveTitle())->ID ) {
		add_filter ( 'wpcf7_validate_email*', 'duplicateValidationMatch', 1, 2 );
		add_filter ( 'wpcf7_validate_text*', 'duplicateValidationMatch', 1, 2 );
		add_filter ( 'wpcf7_posted_data', 'formatRetrieve', 5, 1 );
	}
	
	# Verifieer en behandel de uitschrijvingen
	if ( $ID == get_page_by_title($opt->getCancelTitle())->ID ) {;
		add_filter ( 'wpcf7_posted_data', 'formatCancellation', 5, 1 );
	}
	
	# Verstuur de mails
	if ( $ID == get_page_by_title($opt->getMailTitle())->ID ) {;
		add_filter ( 'wpcf7_posted_data', 'doMailing', 5, 1 );
	}

	# Check of de ploegnaam en het e-mailadres al eens gebruikt zijn	
	function duplicateValidationCheck($result, $tag) {
		$type = $tag['name'];
		$value = strtolower(trim($_POST[$type]));
		
		if ( $type == 'Team' and !empty($value) ) {
			$value = ucWordsAndReplace($value);
			if ( checkDuplicate('Team', $value) ) {
				$result->invalidate($type, 'Deze ploeg is al ingeschreven!');
			}
		}
		
		if ( $type == 'Mail' and !empty($value) ) {
			if ( checkDuplicate('Mail', $value) ) {
				$result->invalidate($type, 'Dit e-mailadres wordt al gebruikt!');
			}
		}
		
		return $result;
	}
	
	# Check of de ploegnaam, de verantwoordelijke en het e-mailadres overeenkomen met de inschrijving
	function duplicateValidationMatch($result, $tag) {
		global $opt;
		$type = $tag['name'];
		$value = strtolower(trim($_POST[$type]))."&&".$opt->notCancelled();
		
		if ( $type == 'Team' and !empty($value) ) {
			$value = ucWordsAndReplace($value);
			if ( !checkDuplicate('Team', $value) ) {
				$result->invalidate($type, 'Onbekende ploegnaam!');
			}
		}

		if ( $type == 'Responsible' and !empty($value) ) {
			$value = ucWordsAndReplace($value);
			if ( !checkDuplicate('Responsible', $value) ) {
				$result->invalidate($type, 'Onbekende verantwoordelijke!');
			}
		}
		
		if ( $type == 'Mail' and !empty($value) ) {
			if ( !checkDuplicate('Mail', $value) ) {
				$result->invalidate($type, 'Onbekend e-mailadres!');
			}
		}
		
		return $result;
	}
	
	function checkDuplicate($key,$value) {
		global $opt, $exp;
		if ( empty($value) ) {
			return false;
		} else {
			$exp->export($opt->getSignupTitle(), array( 'filter' => $key."=".$value ));
			return ( !$exp->nextRow() ? false : true );
		}
	}
	
	# Geef het huidige aantal geverifieerde (en indien nodig: betaalde) deelnemers, inclusief reserveploegen
	function participants() {
		global $opt;
		return do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'"]');
	}
	
	# Stroomlijn de inschrijving met CF7
	function formatInscription($posted_data) {
		global $opt;
		$posted_data['Organizer'] = $opt->getOrganizer();
		$posted_data['Address'] = $opt->getAddress();
		$posted_data['URL'] = $opt->getUrl();
		$posted_data['Title'] = $opt->getQuiz();
		$posted_data['Date'] = $opt->getDate();
		
		$posted_data['Team'] = ucWordsAndReplace(strtolower(trim($posted_data['Team'])));
		$posted_data['Responsible'] = ucWordsAndReplace(strtolower(trim($posted_data['Responsible'])));
		$posted_data['Mail'] = strtolower(trim($posted_data['Mail']));
		
		if ( $opt->showTelephone() ) {
			$temp_tel = trim($posted_data['Telephone']);
			if ( strlen($temp_tel) == 9 ) {
				$posted_data['Telephone'] = substr($temp_tel, 0, 3)." ".substr($temp_tel, 3, 2)." ".substr($temp_tel, 5, 2)." ".substr($temp_tel, 7, 2);
			}
			if ( strlen($temp_tel) == 10 ) {
				$posted_data['Telephone'] = substr($temp_tel, 0, 4)." ".substr($temp_tel, 4, 3)." ".substr($temp_tel, 7, 3);
			}
		}
		
		if ( $opt->showRemarks() and trim($posted_data['Remarks']) != false ) $posted_data['Remarks'] = '"'.ucfirst(strtolower(trim($posted_data['Remarks']))).'"';
		$posted_data['Token'] = bin2hex(openssl_random_pseudo_bytes(16));
		$posted_data['Paid'] = $GLOBALS['pymt'] ? "No" : "N/A";
		$posted_data['Reserve'] = "No";
		$posted_data['Verified'] = "No";
		
		return $posted_data;
	}
	
	# Behandel de bevestiging met CF7
	function formatConfirmation($posted_data) {
		global $opt, $exp, $table_prefix;
		
		# Bevolk de velden die in de mail vermeld zullen worden 
		$posted_data['Organizer'] = $opt->getOrganizer();
		$posted_data['URL'] = $opt->getUrl();
		$posted_data['Title'] = $opt->getQuiz();
		$posted_data['Date'] = $opt->getDate();
		
		if ( isset($_GET['Code']) and strlen($_GET['Code']) == 32 ) {
			$posted_data['Token'] = $_GET['Code'];
			$filter = "Token=".$posted_data['Token']."&&".$opt->notCancelled();
			$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
			$row = $exp->nextRow();
			if ( !!$row ) {
				if ( $opt->showLocation() ) {
					$posted_data['Location'] = $row['Location'];
					$posted_data['Helper'] = "&nbsp;uit ".$row['Location'];
				} else { 
					$posted_data['Helper'] = "";
				}
				
				$posted_data['Reason'] = $opt->showReason() ? $row['Reason'] : "";
				$posted_data['Drinks'] = $opt->showDrinks() ? $row['Drinks'] : "";
				$posted_data['Remarks'] = $opt->showRemarks() ? $row['Remarks'] : "";
				
				if ( $row['Verified'] == "Yes") {
					# Werp een verzenderror op door de bestemmeling te verwijderen
					$posted_data['Address'] = "";
					$posted_data['Verified'] = "ERROR";
				} else {
					# Authoriseer de bevestiging indien de regel nog niet eerder bevestigd werd
					$submit_time = $row['submit_time'];
					$wpdb->update(
						$table_prefix.'cf7dbplugin_submits',
						array( 'field_value' => 'Yes' ),
						array( 'form_name' => $opt->getSignupTitle(), 'field_name' => 'Verified', 'submit_time' => $submit_time ),
					);
					$cur_time = microtime(true);
					$wpdb->update(
						$table_prefix.'cf7dbplugin_submits',
						array( 'sumit_time' => $cur_time ),
						array( 'form_name' => $opt->getSignupTitle(), 'submit_time' => $submit_time ),
					);
					$posted_data['Verified'] = "Yes";
					
					# Voeg een waarschuwing toe indien er moet worden betaald vooraleer de inschrijving definitief is
					if ( $opt->forcePayment() ) {
						$paid = "De registratie wordt echter pas definitief na betaling van ".$opt->getPrice()." euro op het rekeningnummer ".$opt->getAccount().".&nbsp;";
						$posted_data['Paid'] = $paid;
					} else {
						$posted_data['Paid'] = "";
					}
					
					# Voeg een waarschuwing toe indien de ploeg op de reservelijst zal belanden
					if ( do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="Verified=Yes"]') > $opt->maxParticipants() ) {
						$posted_data['Reserve'] = $opt->forcePayment() ? "Opgelet: omdat de quiz momenteel volzet is, zult u op de reservelijst terechtkomen. Indien u effectief uit de boot blijkt te vallen betalen wij het inschrijvingsgeld uiteraard zo snel mogelijk terug.&nbsp;" : "Opgelet: omdat de quiz momenteel volzet is, bent u op de reservelijst beland. U kunt zelf uw status opvolgen in de deelnemerslijst: van zodra er een nummer voor de naam van uw ploeg staat, bent u erbij!&nbsp;";
						$wpdb->update(
							$table_prefix.'cf7dbplugin_submits',
							array( 'field_value' => 'Yes' ),
							array( 'form_name' => $opt->getSignupTitle(), 'field_name' => 'Reserve', 'submit_time' => $cur_time ),
						);
					} else {
						$posted_data['Reserve'] = "";
					}
					
					# Vul als alles goed verlopen is de bestemmeling in (zodat de mail verstuurd kan worden)
					$posted_data['Address'] = $opt->getAddress();
				}
			}
		} else {
			# Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = "";
			$posted_data['Verified'] = "ERROR";	
		}
		
		return $posted_data;
	}
	
	# Registreer de betaling met CF7
	function formatPayment($posted_data) {
		global $opt, $exp, $table_prefix;
		
		# Bevolk de velden die in de mail vermeld zullen worden
		$posted_data['Organizer'] = $opt->getOrganizer();
		$posted_data['URL'] = $opt->getUrl();
		$posted_data['Title'] = $opt->getQuiz();
		$posted_data['Date'] = $opt->getDate();
    	
    	$filter = "Team=".$posted_data['Team']."&&".$opt->notPaid();	
		$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
		$row = $exp->nextRow();
		if ( !!$row ) {
			$posted_data['Responsible'] = $row['Responsible'];
			$posted_data['Mail'] = $row['Mail'];
			$posted_data['Token'] = $row['Token'];
			
			# Registreer de betaling in de database
			$submit_time = $row['submit_time'];
			$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			mysqli_set_charset($con, 'latin1');
			mysqli_query($con,"UPDATE {$table_prefix}cf7dbplugin_submits SET field_value='Yes' WHERE form_name='{$opt->getSignupTitle()}' AND field_name='Paid' AND submit_time={$submit_time}");
			$cur_time = microtime(true);
			mysqli_query($con,"UPDATE {$table_prefix}cf7dbplugin_submits SET submit_time={$cur_time} WHERE form_name='{$opt->getSignupTitle()}' AND submit_time={$submit_time}");
					
			if ( participants() > $opt->maxParticipants() ) {
				# Voeg een waarschuwing toe indien de ploeg op de reservelijst beland is
				$posted_data['Reserve'] = "Opgelet: omdat de quiz momenteel volzet is, staat u voorlopig op de reservelijst. U kunt zelf uw status opvolgen in de deelnemerslijst: van zodra er een nummer voor de naam van uw ploeg staat, bent u erbij!&nbsp;";
				$posted_data['Reserve'] .= $opt->forcePayment() ? "Indien u uit de boot blijkt te vallen betalen wij het inschrijvingsgeld uiteraard meteen terug.&nbsp;" : "";
				mysqli_query($con,"UPDATE {$table_prefix}cf7dbplugin_submits SET field_value='Yes' WHERE form_name='{$opt->getSignupTitle()}' AND field_name='Reserve' AND submit_time={$cur_time}");
			} else {
				# Zet 'Reserve' terug op 'No' indien de ploeg sinds haar inschrijving opgeschoven is naar de vaste deelnemers
				if ( $row['Reserve'] == 'Yes' ) {
					$posted_data['Reserve'] = "Bovendien staat u inmiddels niet langer bij de reserveploegen!&nbsp;";
					mysqli_query($con,"UPDATE {$table_prefix}cf7dbplugin_submits SET field_value='No' WHERE form_name='{$opt->getSignupTitle()}' AND field_name='Reserve' AND submit_time={$cur_time}");
				} else {
					$posted_data['Reserve'] = "";
				}
			}

			# Vul als alles goed verlopen is de bestemmeling in (zodat de mail verstuurd kan worden)
			$posted_data['Address'] = $opt->getAddress();
			mysqli_close($con);
		} else {
			# Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = "";
			$posted_data['Verified'] = "ERROR";	
		}
		
		return $posted_data;
	}
	
	# Behandel de ophaling met CF7
	function formatRetrieve($posted_data) {
		global $opt, $exp;
		
		# Bevolk de velden die in de mail vermeld zullen worden
		$posted_data['Organizer'] = $opt->getOrganizer();
		$posted_data['Address'] = $opt->getAddress();
		$posted_data['URL'] = $opt->getUrl();
		$posted_data['Title'] = $opt->getQuiz();
		$posted_data['Date'] = $opt->getDate();
		
		# Formatteer de invoervelden net zoals bij de inschrijving
		$posted_data['Team'] = ucWordsAndReplace(strtolower(trim($posted_data['Team'])));
		$posted_data['Responsible'] = ucWordsAndReplace(strtolower(trim($posted_data['Responsible'])));
		$posted_data['Mail'] = strtolower(trim($posted_data['Mail']));
		
		# Initialiseer zodanig dat er zeker geen match is indien niets gevonden wordt
		$submit_team = 0;
		$submit_responsible = 1;
		$submit_mail = 2;
		
		$filter = "Team=".$posted_data['Team']."&&".$opt->notCancelled();
		$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
		$row = $exp->nextRow();
		if ( !!$row ) {
			$submit_team = $row['submit_time'];	
		}
		
		$filter = "Responsible=".$posted_data['Responsible']."&&".$opt->notCancelled();
		$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
		$row = $exp->nextRow();
		if ( !!$row ) {
			$submit_responsible = $row['submit_time'];	
		}
		
		$filter = "Mail=".$posted_data['Mail']."&&".$opt->notCancelled();
		$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
		$row = $exp->nextRow();
		if ( !!$row ) {
			$submit_mail = $row['submit_time'];	
		}
		
		if ( $submit_team == $submit_mail and $submit_responsible == $submit_mail ) {
			# Laat de bestemmeling staan (zodat de mail verstuurd kan worden) als de tijden overeen komen
			$posted_data['Token'] = $row['Token'];
		} else {
	   		# Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Mail'] = "ERROR";
			$posted_data['Token'] = "Fields do not match";
		}
		
		return $posted_data;
	}
	
	# Behandel de uitschrijving met CF7
	function formatCancellation($posted_data) {
		global $opt, $exp, $table_prefix;
		
		# Check of er een ploeg van de reservebank gehaald moet worden
		if ( participants() > $opt->maxParticipants() ) {
			$filter = $opt->isComing()."&&Reserve=Yes";
			$exp->export($opt->getSignupTitle(), array( 'filter' => $filter, 'orderby' => 'Submitted asc'));
			$reserve = $exp->nextRow();
		} else {
			$reserve = false;
		}
		
		# Bevolk de velden die in de mail vermeld zullen worden
		$posted_data['Organizer'] = $opt->getOrganizer();
		$posted_data['URL'] = $opt->getUrl();
		$posted_data['Title'] = $opt->getQuiz();
		$posted_data['Date'] = $opt->getDate();
		
		if ( isset($_GET['Code']) and strlen($_GET['Code']) == 32 ) {
			$filter = "Token=".$_GET['Code']."&&".$opt->notCancelled();
			$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
			$row = $exp->nextRow();
			if ( !!$row ) {
				$posted_data['Reason'] = ( trim($posted_data['Reason']) != false ) ? '"'.ucfirst(strtolower(trim($posted_data['Reason']))).'"' : "";
				
				# Registreer de annulering in de database
				$submit_time = $row['submit_time'];
				$wpdb->update(
					$table_prefix.'cf7dbplugin_submits',
					array( 'field_value' => 'CANCELLED' ),
					array( 'form_name' => $opt->getSignupTitle(), 'field_name' => 'Verified', 'submit_time' => $submit_time ),
				);
				
				# Vul als alles goed verlopen is de bestemmeling in (zodat de mail verstuurd kan worden)
				$posted_data['Address'] = $opt->getAddress();

				# Geef een terugbetalingsboodschap weer indien de ploeg ook al betaald had
				$posted_data['Paid'] = ( $row['Paid'] == 'Yes' ) ? "Aangezien u tijdig annuleerde, storten wij het reeds betaalde inschrijvingsgeld zo snel mogelijk terug op uw rekening.&nbsp;" : "";
				
				# Annuleer de mail naar de 1ste reserve sowieso indien de uitschrijving zelf al van een reserveploeg komt
				if ( $row['Reserve'] == 'Yes' ) $reserve = false;
				
				if ( !!$reserve ) {
					# Mail de 1ste reserveploeg
					$to = $reserve['Responsible'].' <'.$reserve['Mail'].'>';
					$subject = 'Deelname '.$opt->getQuiz();
					$message = '<p>Beste '.$reserve['Responsible'].'</p><p>Goed nieuws: dankzij de uitschrijving van '.$posted_data['Team'].' kunt u alsnog deelnemen aan de '.$opt->getQuiz().'! U zit niet langer op de reservebank.</p><p>Kan ook uw ploeg zich inmiddels niet meer vrijmaken? Schrijf u dan zo snel mogelijk uit <a href="'.$opt->getUrl().'annuleren/?Code='.$reserve['Token'].'" target="_blank">via deze link</a>.</p><p>Met vriendelijke groet</p><p>'.$opt->getOrganizer().'</p>';
					$header[] = 'From: "'.$opt->getOrganizer().'" <'.$opt->getAddress().'>';
					$header[] = 'Content-Type: text/html';
					wp_mail($to, $subject, $message, $header);
					
					# Zet het 'Reserve'-veld op 'No'
					$submit_time = $reserve['submit_time'];
					$wpdb->update(
						$table_prefix.'cf7dbplugin_submits',
						array( 'field_value' => 'No' ),
						array( 'form_name' => $opt->getSignupTitle(), 'field_name' => 'Reserve', 'submit_time' => $submit_time ),
					);
				}
			}
		} else {
	    	# Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = "";
			$posted_data['Verified'] = "ERROR";
		}
		
		return $posted_data;
	}
	
	function doMailing($posted_data) {
		global $opt, $exp;
		
		$i = 0;
		$message = trim($posted_data['Message']);
		$subject = trim($posted_data['Subject']);
		$header[] = 'From: "'.$opt->getOrganizer().'" <'.$opt->getAddress().'>';
		$header[] = 'Content-Type: text/html';
		
		if ( $posted_data['Group'] == 0 ) {
			$posted_data['Group'] = "organisator";
			$to = $opt->getOrganizer().' <'.$opt->getAddress().'>';
				
			# Vul de variabelen in
			$message = "<p>".$message."</p>";
			$message = str_replace("#Naam#", "verantwoordelijke", $message);
			$message = str_replace("#Ploeg#", "Quizploeg", $message);
			$message = str_replace("#Stad#", "Fantasialand", $message);
			$message = str_replace("#URL#", $opt->getUrl(), $message);
			$message = str_replace("#Code#", "123456789", $message);
			$message = str_replace("\n\n", "</p><p>", $message);

			if ( wp_mail($to, $subject, $message, $header) ) $i++;
		} else {
			# Bevolk de velden die in de mail vermeld zullen worden
			$posted_data['Organizer'] = $opt->getOrganizer();
			$posted_data['Address'] = $opt->getAddress();
			$posted_data['Title'] = $opt->getQuiz();
			$posted_data['Date'] = $opt->getDate();
		
			if ( $posted_data['Group'] == 1 ) {
				$filter = $opt->isComing()."&&Reserve=No";
				$posted_data['Group'] = "deelnemers";
			}
			if ( $posted_data['Group'] == 2 ) { 
				$filter = $opt->isComing()."&&Reserve=Yes";
				$posted_data['Group'] = "reserves";
			}
			if ( $posted_data['Group'] == 3 ) { 
				$filter = $opt->isComing();
				$posted_data['Group'] = "deelnemers en reserves";
			}
			if ( $posted_data['Group'] == 4 ) { 
				$filter = $opt->notPaid();
				$posted_data['Group'] = "wanbetalers";
			}
			$exp->export($opt->getSignupTitle(), array( 'filter' => $filter, 'orderby' => 'Submitted asc' ));
			for ( $row = $exp->nextRow(); !!$row; $row = $exp->nextRow() ) {
				$to = $row['Responsible'].' <'.$row['Mail'].'>';
				$message = $posted_data['Message'];
				
				# Vul de variabelen in
				$message = "<p>".$message."</p>";
				$message = str_replace("#Naam#", $row['Responsible'], $message);
				$message = str_replace("#Ploeg#", $row['Team'], $message);
				$message = str_replace("#Stad#", $row['Location'], $message);
				$message = str_replace("#URL#", $opt->getUrl(), $message);
				$message = str_replace("#Code#", $row['Token'], $message);
				$message = str_replace("\n\n", "</p><p>", $message);

				if ( wp_mail($to, $subject, $message, $header) ) $i++;
			}
		}
		
		$posted_data['Message'] = $message;
		$posted_data['Number'] = $i;
		return $posted_data;
	}
	
	# Toon het inschrijvingsformulier (na controle of het niet afgesloten moet worden)
	function printSignup() {
		global $opt;
    	
    	if ( time() < strtotime( $opt->getSignupDate() ) ) {	
			if ( participants() == 0 ) {
				$str = "Momenteel zijn er nog geen ploegen ingeschreven voor de ".$opt->getQuiz().". Wordt u de eerste? Vul het formulier in en u hebt een primeur beet!";
			}
			
			if ( participants() == 1 ) {
				$str = "Momenteel is er nog maar één ploeg ingeschreven voor de ".$opt->getQuiz().". Wordt u de fan van het tweede uur? Vul het formulier in en geef de rest het nakijken!";
			}
			
			if ( participants() > 1 and participants()/$opt->maxParticipants() < 0.3 ) {
				$str = "Momenteel zijn er nog maar ".participants()." ploegen ingeschreven voor de ".$opt->getQuiz().". Wilt u ook toetreden tot dit selecte kransje trendsetters?<br>Vul dit formulier in en uw droom wordt werkelijkheid!";
			}
			
			if ( participants() > 1 and participants()/$opt->maxParticipants() >= 0.3 and participants() < $opt->maxParticipants() ) { 
				$str = "Momenteel zijn er al ".participants()." ploegen ingeschreven voor de ".$opt->getQuiz().". Wordt u de volgende? Vul het formulier in en u bent erbij! Opgelet: we laten maximum ".$opt->maxParticipants()." tafels toe (en een reservelijst van ".$opt->maxReserves()." ploegen).";
			}
			
			if ( participants() >= $opt->maxParticipants() ) {
				$str = ( participants() >= ( $opt->maxParticipants() + $opt->maxReserves() ) ) ? "Helaas, de inschrijvingen voor de ".$opt->getQuiz()." zijn volledig afgesloten! De maximumcapaciteit van de zaal is bereikt en de reservelijst zit vol. Volgend jaar beter?" : "Helaas, de maximumcapaciteit van de ".$opt->getQuiz()." is bereikt! U kunt enkel nog inschrijven voor de reservelijst. Als reserveploeg krijgt u voorlopig geen tafelnummer op de deelnemerspagina. Indien een eerder ingeschreven ploeg afzegt, schuift u wel automatisch een plaatsje op.";
			}
			
			if ( participants() < ( $opt->maxParticipants()+$opt->maxReserves() ) ) {
				$str .= $opt->formSignup();
			}
    	} else {
    		$str = "Inschrijven via de site is niet meer mogelijk. Contacteer de organisator persoonlijk.";
    	}
		
		return $str;
	}
	
	# Toon het bevestigingsformulier
	function printConfirm() {
		global $opt;
		return "Hieronder vindt u een overzicht van uw gegevens.<br>Klik op de knop om uw inschrijving te bevestigen.<br><br>".$opt->formConfirm();
	}
	
	# Toon het betalingsformulier
	function printPay() {
		global $opt;
		return "Selecteer hieronder de ploeg die betaald heeft voor de ".$opt->getQuiz().".<br><br>".$opt->formPay();
	}
	
	# Toon het ophalingsformulier
	function printRetrieve() {
		global $opt;
		return "Bent u de bevestigingsmail met uw speciale uitschrijvingscode kwijt?<br>Vul hieronder exact dezelfde gegevens in als bij uw inschrijving en wij sturen u een nieuwe link.<br><br>".$opt->formRetrieve();
	}
	
	# Toon het annuleringsformulier
	function printCancel() {
		global $opt;
		$str = "";
		
		if ( time() < strtotime( $opt->getCancelDate() ) ) {
			if ( strlen(get_query_var('Code')) == 32 ) $str .= "Hieronder vindt u een overzicht van uw inschrijvingsgegevens.<br>Klik op de knop om uw zitje op de ".$opt->getQuiz()." te annuleren.<br>";
			$str .= "Opgepast: u zal onmiddellijk van de deelnemerslijst geschrapt worden!<br>Opnieuw inschrijven met deze ploegnaam wordt onmogelijk.<br>".$opt->formCancel();
		} else {
    		$str .= "Annuleren via de site is niet meer mogelijk.<br>Contacteer de organisator persoonlijk.";
    	}
		
		return $str;
	}
	
	# Toon het mailformulier
	function printMail() {
		global $opt;
		return "Met behulp van dit formulier contacteert u in één klik alle deelnemers, bijvoorbeeld om hen te herinneren aan de datum of om nog enkele praktische tips mee te geven over de locatie en het startuur. U kunt ook een bericht sturen naar alle reserveploegen of alle wanbetalers (indien vooraf betalen verplicht werd). Enkel ploegen die hun e-mailadres verifieerden door op de link in de bevestigingsmail te klikken, worden opgenomen in de mailing.<br><br>De mail kan bovendien gepersonaliseerd worden met de variabelen #Naam#, #Ploeg#, #Stad#, #URL# en #Code#. Ook gebruik van HTML-opmaak is mogelijk. De e-mailadressen zelf blijven beschermd in de database, maar u krijgt wel te zien hoeveel e-mails er verstuurd werden. Als afzender gebruikt het formulier de naam en het e-mailadres van de organisator (zoals opgegeven in functions.php). Test de codes en lay-out door de mail eerst naar uzelf te sturen!<br><br>".$opt->formMail();
	}

	# Druk de deelnemerslijst af
	function printParticipants() {
		global $opt;
		$str = printInfo()."<br>";
		$upper = ( participants() > $opt->maxParticipants() ) ? $opt->maxParticipants() : participants();

		for ( $i = 1; $i <= $upper; $i++ ) {
			$str .= "<br>".$i.". ".do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Team" filter="'.$opt->isComing().'" orderby="Submitted asc" limit="'.($i-1).',1"]');
			$str .= printStrength(do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Strength" filter="'.$opt->isComing().'" orderby="Submitted asc" limit="'.($i-1).',1"]'));
		}
		
		# Druk de reservelijst af indien meer er inschrijvingen zijn dan het maximum aantal deelnemers 
		if ( participants() > $opt->maxParticipants() ) {
			$str .= "<br>";
			$max = participants();
			for ( $i = $opt->maxParticipants(); $i < $max; $i++ ) {
				$str .= "<br>&bull; ".do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Team" filter="'.$opt->isComing().'" orderby="Submitted asc" limit="'.$i.',1"]');
				$str .= printStrength(do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Strength" filter="'.$opt->isComing().'" orderby="Submitted asc" limit="'.$i.',1"]'));
			}
		}
		
		$str .= "<br><br>".printTotalStrength();
		
		return $str;
	}
	
	# Druk de statistieken af van de bevestigde (en betaalde) niet-reserveploegen
	function printStatistics() {
		global $opt;
		$str = "";
		$max = ( participants() > $opt->maxParticipants() ) ? $opt->maxParticipants() : participants();
		
		if ( $opt->showDrinks() ) {
			$pct = ( participants() != 0 ) ? round( do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=No&&Drinks=N/A" orderby="Submitted asc" limit="'.$max.'"]')/$max*100 , 0 ) : 0;
			$str = "<b>Drank</b><br>Geen antwoord: ".$pct."%";
			
			$drinks = $opt->getDrinks();
			for ( $i = 0; $i < count($drinks); $i++ ) {
				$pct = ( participants() != 0 ) ? round( do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=No&&Drinks='.$drinks[$i].'" orderby="Submitted asc" limit="'.$max.'"]')/$max*100 , 0 ) : 0;
				$str .= "<br>".ucfirst($drinks[$i]).": ".$pct."%";
			}
			$str .= "<br><br>";
		}
		
		if ( $opt->showReason() ) {
			$pct = ( participants() != 0 ) ? round( do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=No&&Reason=N/A" orderby="Submitted asc" limit="'.$max.'"]')/$max*100 , 0 ) : 0;
			$str .= "<b>Motivatie</b><br>Geen antwoord: ".$pct."%";
			
			$reasons = $opt->getReasons();
			for ( $i = 0; $i < count($reasons); $i++ ) {
				$pct = ( participants() != 0 ) ? round( do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=No&&Reason='.$reasons[$i].'" orderby="Submitted asc" limit="'.$max.'"]')/$max*100 , 0 ) : 0;
				$str .= "<br>".ucfirst($reasons[$i]).": ".$pct."%";
			}
		}
		
		return $str;
	}
	
	# Pas de tekst boven de deelnemerslijst aan naar gelang het aantal ingeschreven deelnemers
	function printInfo() {
		global $opt;
		
		if ( participants() == 0 ) {
			$str = "Tot nu toe zijn er nog geen ploegen ingeschreven voor de ".$opt->getQuiz()." van ".$opt->getDate().".";
		}
		
		if ( participants() == 1 ) {
			$str = "Tot nu toe is er nog maar één ploeg ingeschreven voor de ".$opt->getQuiz()." van ".$opt->getDate().".";
		}
		
		if ( participants() > 1 and participants()/$opt->maxParticipants() < 0.4 ) {
			$str = "Tot nu toe zijn er nog maar ".participants()." ploegen ingeschreven voor de ".$opt->getQuiz()." van ".$opt->getDate().".<br>Haast u naar <a href='../inschrijven/'>het inschrijvingsformulier</a> om daar verandering in te brengen!";
		}
		
		if ( participants() > 1 and participants()/$opt->maxParticipants() >= 0.4 and participants() < $opt->maxParticipants() ) {
			$str = "Tot nu toe zijn er al ".participants()." ploegen ingeschreven voor de ".$opt->getQuiz()." van ".$opt->getDate().".";
		}
		
		if ( participants() > 1 and participants()/$opt->maxParticipants() >= 0.8 and participants() < $opt->maxParticipants() ) {
			$str = "Er zijn maar liefst al ".participants()." ploegen ingeschreven voor de ".$opt->getQuiz()." van ".$opt->getDate().".";
		}
		
		if ( participants() >= $opt->maxParticipants() ) {
			$str = "De ".$opt->getQuiz()." van ".$opt->getDate()." is volledig volzet, ";
			if ( participants() >= ( $opt->maxParticipants() + $opt->maxReserves() ) ) {
				$str .= "inschrijven is niet meer mogelijk!";
			} else {
				$str .= "inschrijven kan enkel nog op de reservelijst.";
			}
			if ( participants() > $opt->maxParticipants() ) {
				$str .= " Alleen de ploegen in de genummerde lijst zijn al zeker van hun stek.";
			}
		}
		
		if ( participants() != 0 ) {
			$str .= " Wenst u uit te schrijven? Gebruik daarvoor de speciale link die u kreeg in uw bevestigingsmail. Bent u die mail kwijtgespeeld? Ga naar <a href='../ophalen/'>de ophaalpagina</a> om de link opnieuw te verzenden.";
		}
		
		return $str;
	}
	
	# Toon het totaal aantal sterktepunten, de voorlopige categorie en de te verdienen vaste punten (afhankelijk van de provincie waar de quiz plaatsvindt)
	function printTotalStrength() {
		global $opt;
		$vast = array( 	0	=>	array(4,3,2,1),
						1	=>	array(4,3,2,1),
						2	=>	array(4,3,2,1),
						3	=>	array(5,4,3,2,1),
						4	=>	array(5,4,3,2,1),
						5	=>	array(6,4,3,2,1),
						6	=>	array(6,5,4,3,2,1),
						7	=>	array(7,5,4,3,2,1),
						8	=>	array(8,6,4,3,2,1),
						9	=>	array(9,7,5,4,3,2,1),
						10	=>	array(10,7,5,4,3,2,1),
						11	=>	array(11,8,6,5,4,3,2,1),
						12	=>	array(12,9,7,5,4,3,2,1),
						13	=>	array(13,10,7,5,4,3,2,1),
						14	=>	array(14,10,8,6,5,4,3,2,1),
						15	=>	array(15,11,8,6,5,4,3,2,1),
						16	=>	array(16,12,9,6,5,4,3,2,1),
						17	=>	array(17,13,10,7,6,5,4,3,2,1),
						18	=>	array(18,14,11,8,6,5,4,3,2,1),
						19	=>	array(19,15,12,9,7,6,5,4,3,2,1),
						20	=>	array(20,16,13,10,8,6,5,4,3,2,1),
						21	=>	array(21,16,13,10,8,6,5,4,3,2,1),
						22	=>	array(22,17,14,11,9,7,6,5,4,3,2,1),
						23	=>	array(23,18,14,11,9,7,6,5,4,3,2,1),
						24	=>	array(24,19,15,12,9,7,6,5,4,3,2,1),
						25	=>	array(25,20,16,12,10,8,7,6,5,4,3,2,1),
						26	=>	array(26,21,16,13,11,9,7,6,5,4,3,2,1),
						27	=>	array(27,22,17,13,11,9,7,6,5,4,3,2,1),
						28	=>	array(28,22,18,14,11,9,8,7,6,5,4,3,2,1),
						29	=>	array(29,23,18,14,11,9,8,7,6,5,4,3,2,1),
						30	=>	array(30,24,19,15,12,10,9,8,7,6,5,4,3,2,1),
						31	=>	array(31,25,20,16,13,11,9,8,7,6,5,4,3,2,1),
						32	=>	array(32,26,21,17,14,12,10,8,7,6,5,4,3,2,1),
						33	=>	array(34,27,22,18,15,12,10,9,8,7,6,5,4,3,2,1),
						34	=>	array(36,28,23,20,16,14,12,10,8,7,6,5,4,3,2,1),
						35	=>	array(38,29,24,21,18,15,12,10,8,7,6,5,4,3,2,1),
						36	=>	array(40,31,25,22,19,16,13,11,9,8,7,6,5,4,3,2,1),
						37	=>	array(42,33,26,23,20,17,14,11,9,8,7,6,5,4,3,2,1),
						38	=>	array(44,34,27,24,21,18,15,12,10,9,8,7,6,5,4,3,2,1),
						39	=>	array(46,36,29,25,22,19,16,13,10,9,8,7,6,5,4,3,2,1),
						40	=>	array(48,38,30,26,22,19,16,13,10,9,8,7,6,5,4,3,2,1),
						41	=>	array(50,39,32,27,23,19,16,13,11,10,9,8,7,6,5,4,3,2,1),
						42	=>	array(52,41,33,28,23,19,16,13,11,10,9,8,7,6,5,4,3,2,1),
						43	=>	array(54,43,35,29,24,20,17,14,12,10,9,8,7,6,5,4,3,2,1),
						44	=>	array(56,44,36,30,25,21,17,15,13,11,10,9,8,7,6,5,4,3,2,1),
						45	=>	array(58,46,38,32,26,21,17,15,13,11,10,9,8,7,6,5,4,3,2,1),
						46	=>	array(60,48,40,33,27,22,18,16,14,12,10,9,8,7,6,5,4,3,2,1),
						50	=>	array(80,64,53,44,37,31,26,22,18,15,12,10,8,7,6,5,4,3,2,1)	);
		$cor = array(	'WVL'	=>	5,
						'OVL'	=>	3,
						'VLB'	=>	2,
						'LIM'	=>	2,
						'ANT'	=>	0	);
		$num = array('nul','enige','twee','drie','vier','vijf','zes','zeven','acht','negen','tien');
		
		# Tel het totaal aantal sterktepunten van bevestigde (en betaalde) niet-reserveploegen
		$pts = do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Strength" filter="'.$opt->isComing().'&&Reserve=No" limit="0,'.$opt->maxParticipants().'" function="sum"]');

		# Tel het aantal ploegen (maximum tien) met één of meerdere sterktepunten
		$limit = ( $opt->maxParticipants() > 10 ) ? 10 : $opt->maxParticipants();
		$cnt = do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Strength!=0&&Reserve=No" limit="0,'.$limit.'" orderby="Strength desc" function="count"]');
		
		# Tel de sterktepunten van de tien sterkste ploegen en stel de voorlopige categorie in
		$maxpts = do_shortcode('[cfdb-value form="'.$opt->getSignupTitle().'" show="Strength" filter="'.$opt->isComing().'&&Reserve=No" limit="0,'.$limit.'" orderby="Strength desc" function="sum"]');
		$cat = $maxpts;
		
		# Geef aan of de quiz een vaste categorie is, of dat er een provinciale correctie of een limiet op het aantal ploegen nodig is
		$vastfix = ( $opt->getFixedCategory() != 0 ) ? true : false;
		$provfix = ( $cat < 30 and $cor[$opt->getRegion()] != 0 ) ? true : false;
		$maxfix = ( $pts > $maxpts ) ? true : false;

		# Voer de provinciale correctie door (aanvullen tot maximum 30 sterktepunten!)
		if ( $cat < 30 ) {
			if ( (30-$cat) >= $cor[$opt->getRegion()] ) {
				$cat += $cor[$opt->getRegion()];
			} else {
				$cat = 30;
			}
		}
		
		# Verhoog (indien nodig) de categorie tot het minimum voor de ingestelde vaste categorie
		if ( $opt->getFixedCategory() == 1 && $cat < 50 ) $cat = 50;
		if ( $opt->getFixedCategory() == 2 && $cat < 40 ) $cat = 40;
		if ( $opt->getFixedCategory() == 3 && $cat < 30 ) $cat = 30;
		
		# Print het puntenlijstje
		$manna = "";
		$length = count($vast[$cat]);
		for ( $n = 0; $n < $length-2 ; $n++ ) {
			$manna .= $vast[$cat][$n].", ";
		}
		$manna .= $vast[$cat][$length-2]." en ".$vast[$cat][$length-1];
		
		# Print de categoriebepaling
		$str = "";
		$prelim = ( time() < strtotime( $opt->getSignupDate() ) ) ? "voorlopig " : "";
		if ( participants() > 0 and $pts == 0 ) $str = "Er zijn ".$prelim."nog geen sterktepunten aanwezig. ";
		if ( $pts == 1 ) $str = "Er is ".$prelim."nog maar één sterktepunt aanwezig. ";
		if ( $pts >= 2 ) $str = "In totaal zijn er ".$prelim.$pts." sterktepunten aanwezig. ";
		if ( participants() > 0 ) {
			if ( $vastfix ) {
				$str .= "Aangezien het hier om een VC".$opt->getFixedCategory()."-quiz gaat, zal deze quiz sowieso een Cat. ".$cat." worden";
			} else {
				if ( !$provfix and !$maxfix ) $str .= "Het lijkt ";
				if ( $provfix or $maxfix ) $str .= "Na ";
				if ( $provfix ) $str .= "de provinciale correctie toe te passen";
				if ( $provfix and $maxfix ) $str .= " én ";
				if ( $maxfix ) $str .= "enkel de sterktepunten van de tien sterkste ploegen mee te tellen";
				if ( $provfix or $maxfix ) $str .= ", lijkt het ";
				$str .= "erop dat deze quiz een Cat. ".$cat." zal worden";
				if ( $maxfix ) $str .= " (in de veronderstelling dat die tien ploegen ook effectief in de top 20 eindigen)";
			}
			$str .= ". Er zijn in die situatie respectievelijk ".$manna." vaste punten te verdienen met de eerste ".$length." plaatsen.";
			if ( $pts > 0 ) {
				$str .= " Daar kom".($maxpts == 1 ? "t" : "en")." in het ideale geval nog eens ".$maxpts;
				$str .= ( $maxpts == 1 ) ? " variabel VQR-punt" : " variabele VQR-punten";
				$str .= " bovenop, wanneer u als kleine David de ".$num[$cnt]." Goliath";
				if ( $cnt != 1 ) $str .= "s";
				$str .= " met ";
				if ( $cnt == 1 ) $str .= "een ";
				if ( $pts > $maxpts ) $str .= "de meeste ";
				$str .= "sterktepunt";
				if ( $cnt != 1 ) $str .= "en allemaal";
				$str .= " achter u laat.";
			}
		}
			
		return $str;
	}

	# Druk het aantal sterktepunten van de ploeg af
	function printStrength($cell) {
		if ( $cell == 0 ) {
			return null;
		} else {
			$stars = "*";
			for ( $i = 1; $i < $cell; $i++ ) {
				$stars .= "*";
			};
			return " (".$stars.")";
		}
	}
	
	# Haal de datagegevens op via het token in de URL
	function getData($atts) {
		global $opt, $exp;
		
		if ( isset($_GET['Code']) and strlen($_GET['Code']) == 32 ) {
			$filter = "Token=".$_GET['Code'];
			$exp->export($opt->getSignupTitle(), array( 'filter' => $filter ));
			$row = $exp->nextRow();
		} else {
			$row = false;
		}
		return ( !$row ? "Ongeldige toegangscode!" : $row[$atts['key']] );
	}
		
	# Maak een tekstveld voor de locatie
	function text_city() {
		global $opt;
		return ( $opt->showLocation() ? "<p><b>Gemeente</b><br><input type='text' name='Location' maxlength='32' required></p>" : null );
	}
	
	# Creëer een dropdownmenu met alle Vlaamse gemeentes
	function select_locations() {
		global $opt;
		if ( $opt->showLocation() ) {
			$str = "<p><b>Locatie</b><br><select name='Location' id='Location' onchange='document.getElementById(\"Location\").value=this.value;'><option value='Niemandsland'>(selecteer)</option>";
			$locations = $opt->getLocations();
			for ( $i = 0; $i < count($locations); $i++ ) {
				$str .= "<option value='$locations[$i]'>$locations[$i]</option>";
			}
			$str .= "</select></p>";
			return $str;
		} else {
			return null;
		}
	}
	
	# Maak een tekstveld voor het telefoonnummer
	function text_telephone() {
		global $opt;
		return ( $opt->showTelephone() ? "<p><b>Telefoon</b><br><input type='text' name='Telephone' maxlength='10' required></p>" : null );
	}
		
	# Creëer een dynamisch dropdownmenu met de mogelijke motivaties
	function select_reasons() {
		global $opt;
		if ( $opt->showReason() ) {
			$str = "<p><b>Wij nemen in de eerste plaats deel</b><br><select name='Reason' id='Reason' onchange='document.getElementById(\"Reason\").value=this.value;'><option value='N/A'>(selecteer)</option>";
			$reasons = $opt->getReasons();
			for ( $i = 0; $i < count($reasons); $i++ ) {
				$str .= "<option value='$reasons[$i]'>$reasons[$i]</option>";
			}
			$str .= "</select></p>";
			return $str;
		} else {
			return null;
		}
	}
	
	# Creëer een dynamisch dropdownmenu met de mogelijke dranken
	function select_drinks() {
		global $opt;
		if ( $opt->showDrinks() ) {
			$str = "<p><b>Wij drinken voornamelijk</b><br><select name='Drinks' id='Drinks' onchange='document.getElementById(\"Drinks\").value=this.value;'><option value='N/A'>(selecteer)</option>";
			$drinks = $opt->getDrinks();
			for ( $i = 0; $i < count($drinks); $i++ ) {
				$str .= "<option value='$drinks[$i]'>$drinks[$i]</option>";
			}
			$str .= "</select></p>";
			return $str;
		} else {
			return null;
		}
	}
	
	# Maak een tekstveld voor de opmerkingen
	function text_remarks() {
		global $opt;
		return ( $opt->showRemarks() ? "<p><b>Opmerkingen</b><br><input type='text' name='Remarks' autocomplete='off'></p>" : null );
	}
	
	# Creëer een dynamisch dropdownmenu met alle teams die nog niet betaald hebben
	function select_teams() {
		global $opt, $exp;
		$exp->export($opt->getSignupTitle(), array( 'filter' => $opt->notPaid(), 'orderby' => 'Team asc' ));
		$str = "<select name='Team' id='Team' onchange='document.getElementById(\"Team\").value=this.value;'><option value='N/A'>(selecteer)</option>";
		while ( $row = $exp->nextRow() ) {
			$str .= "<option id='".$row['Team']."' value='".$row['Team']."'>".$row['Team']."</option>";
		}
		$str .= "</select>";
		return $str;
	}
	
	# Creëer een dropdownmenu met mogelijke doelgroepen
	function select_groups() {
		global $opt;
		$deelnemers = do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=No"]');
		$reserves = do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->isComing().'&&Reserve=Yes"]');
		$wanbetalers = do_shortcode('[cfdb-count form="'.$opt->getSignupTitle().'" filter="'.$opt->notPaid().'"]');
		$str = "<select name='Group' id='Group' onchange='document.getElementById(\"Group\").value=this.value;'>";
		$str .= "<option value='0'>de organisator (als test)</option>";
		if ( $deelnemers > 0 ) $str .= "<option value='1'>alle deelnemende ploegen (".$deelnemers.")</option>";
		if ( $reserves > 0 ) $str .= "<option value='2'>alle ploegen op de reservelijst (".$reserves.")</option>";
		if ( $reserves > 0 ) $str .= "<option value='3'>zowel de deelnemers als de reserves (".($deelnemers+$reserves).")</option>";
		if ( $opt->forcePayment() and $wanbetalers > 0 ) $str .= "<option value='4'>alle inschrijvers die nog niet betaald hebben (".$wanbetalers.")</option>";
		$str .= "</select>";
		return $str;
	}

	# Zet een hoofdletter aan elk woord en na een punt of trema
	function ucWordsAndReplace($str) {
		return preg_replace_callback( '|[./-].*?\w|' , create_function('$atts', 'return strtoupper($atts[0]);') , ucwords($str) );
	}
	
	function execute_shortcodes($form) {
		return do_shortcode($form);
	}
	
	function add_query_vars($vars) {
  		$vars[] = "Code";
  		return $vars;
	}
	
	add_shortcode ( 'inschrijvingsformulier', 'printSignup' );
	add_shortcode ( 'uitschrijvingsformulier', 'printCancel' );
	add_shortcode ( 'bevestigingsformulier', 'printConfirm' );
	add_shortcode ( 'betalingsformulier', 'printPay' );
	add_shortcode ( 'ophalingsformulier', 'printRetrieve' );
	add_shortcode ( 'mailformulier', 'printMail' );
	
	add_shortcode ( 'deelnemers', 'printParticipants' );
	add_shortcode ( 'statistieken', 'printStatistics' );
	add_shortcode ( 'data', 'getData' );
	
	add_shortcode ( 'city', 'text_city' );
	add_shortcode ( 'locations', 'select_locations' );
	add_shortcode ( 'telephone', 'text_telephone' );
	add_shortcode ( 'reasons', 'select_reasons' );
	add_shortcode ( 'drinks', 'select_drinks' );
	add_shortcode ( 'remarks', 'text_remarks' );
	add_shortcode ( 'teams', 'select_teams' );
	add_shortcode ( 'groups', 'select_groups' );
	
	add_filter ( 'wpcf7_form_elements', 'execute_shortcodes', 10, 1 );
	add_filter( 'query_vars', 'add_query_vars', 10, 1 );
   	
?>
