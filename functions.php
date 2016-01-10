<?php
	
	function theme_enqueue_styles() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	}

	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

	# Frederik Neirynck 2015
	# koekedozeklan.be
	# lecouperet.net
	# Versie 1.4.1
	
	# Deze module mag vrij gebruikt (en aangepast) worden maar geef mij een seintje of link naar onze site als je er tevreden over bent
	# Lees de installatiegids op www.koekedozeklan.be/database en volg de wijzigingen via www.koekedozeklan.be/demo
	
	##############################################################
	#  VERANDER DE WAARDEN VAN DE VARIABELEN NAAR UW VOORKEUREN  #
	#  DEZE INSTELLINGEN WORDEN BEWAARD BIJ TOEKOMSTIGE UPDATES  #
	##############################################################
	
	# Wijzig in 'true' indien er betaald moet worden vooraleer de ploeg op de deelnemerslijst verschijnt
	$pymt = true;

	class Options {
		# Ga naar 'Contact' -> 'Contactformulieren' en noteer de ID's uit de shortcode van de formulieren 'Annuleren', 'Betalen', 'Bevestigen', 'Inschrijven', 'Mailen' en 'Ophalen'
		private $cf7_cancel = '13';
		private $cf7_pay = '182';
		private $cf7_confirm = '146';
		private $cf7_signup = '246';
		private $cf7_mail = '341';
		private $cf7_retrieve = '247';
	
		# Naam van de quiz
		private $quiz = '1ste Deelnemersdatabase';
		
		# Datum van de quiz (voluit)
		private $date = '1 januari 2016';
		
		# Naam van de organisator
		private $organizer = 'KoeKedozeKlan';
		
		# E-mailadres van de organisator
		private $address = 'info@koekedozeklan.be';
		
		# Basisadres van de website (inclusief 'http://' en trailing slash '/')
		private $url = 'http://www.koekedozeklan.be/demo/';
		
		# Maximum aantal deelnemers
		private $max_participants = '5';
		
		# Maximum aantal reserveploegen
		private $max_reserves = '5';
		
		# Deadline voor inschrijvingen (MM/DD/JJJJ)
		private $signup_date = '12/31/2015';
		
		# Deadline voor (terugbetaalde) annuleringen (MM/DD/JJJJ)
		private $cancel_date = '01/01/2016';
	
		# Provincie waar de quiz plaatsvindt: West-Vlaanderen ('WVL'), Oost-Vlaanderen ('OVL'), Antwerpen ('ANT'), Vlaams-Brabant ('VLB') of Limburg ('LIM')
		private $region = 'WVL';
		
		# Indien het een VC-quiz is: wijzig de nul in het nummer van de vaste categorie
		private $fixed = '0';
		
		# Inschrijvingsbedrag in euro
		private $price = '20';
		
		# Rekeningnummer voor de betalingen
		private $account = 'BE55 9730 9371 2744';
	
		# Vraag een locatie ('true') of niet ('false')
		private $show_location = true;
	
		# Vraag een telefoonnummer ('true') of niet ('false')
		private $show_telephone = true;
		
		# Vraag naar de motivatie ('true') of niet ('false')
		private $show_reason = true;
		# Som een willekeurig aantal motivaties op
		private $reasons = array('uit sympathie voor het goede doel', 'om gezellig iets te drinken', 'ter meerdere eer en glorie', 'voor de prijzentafel');
		
		# Vraag naar de drankvoorkeur ('true') of niet ('false')
		private $show_drinks = true;
		# Som een willekeurig aantal dranken op
		private $drinks = array('warme dranken', 'zware bieren', 'frisdrank', 'pintjes', 'wijn');
		
		# Laat opmerkingen toe ('true') of niet ('false')
		private $show_remarks = true;
		
		###############################
		#  HIERONDER NIETS WIJZIGEN   #
		#  TENZIJ U ER IETS VAN KENT  #
		###############################

		private $locations = array('Aalst', 'Aalter', 'Aarschot', 'Aartselaar', 'Affligem', 'Alken', 'Alveringem', 'Anderlecht', 'Antwerpen', 'Anzegem', 'Ardooie', 'Arendonk', 'As', 'Asse', 'Assenede', 'Avelgem', 'Baarle-Hertog', 'Balen', 'Beernem', 'Beerse', 'Beersel', 'Begijnendijk', 'Bekkevoort', 'Beringen', 'Berlaar', 'Berlare', 'Bertem', 'Bever', 'Beveren', 'Bierbeek', 'Bilzen', 'Blankenberge', 'Bocholt', 'Boechout', 'Bonheiden', 'Boom', 'Boortmeerbeek', 'Borgloon', 'Bornem', 'Borsbeek', 'Boutersem', 'Brakel', 'Brasschaat', 'Brecht', 'Bredene', 'Bree', 'Brugge', 'Brussel', 'Buggenhout', 'Damme', 'De Haan', 'De Panne', 'De Pinte', 'Deerlijk', 'Deinze', 'Denderleeuw', 'Dendermonde', 'Dentergem', 'Dessel', 'Destelbergen', 'Diepenbeek', 'Diest', 'Diksmuide', 'Dilbeek', 'Dilsen-Stokkem', 'Drogenbos', 'Duffel', 'Edegem', 'Eeklo', 'Elsene', 'Erpe-Mere', 'Essen', 'Etterbeek', 'Evere', 'Evergem', 'Galmaarden', 'Ganshoren', 'Gavere', 'Geel', 'Geetbets', 'Genk', 'Gent', 'Geraardsbergen', 'Gingelom', 'Gistel', 'Glabbeek', 'Gooik', 'Grimbergen', 'Grobbendonk', 'Haacht', 'Haaltert', 'Halen', 'Halle', 'Ham', 'Hamme', 'Hamont-Achel', 'Harelbeke', 'Hasselt', 'Hechtel-Eksel', 'Heers', 'Heist-op-den-Berg', 'Hemiksem', 'Herent', 'Herentals', 'Herenthout', 'Herk-de-Stad', 'Herne', 'Herselt', 'Herstappe', 'Herzele', 'Heusden-Zolder', 'Heuvelland', 'Hoegaarden', 'Hoeilaart', 'Hoeselt', 'Holsbeek', 'Hooglede', 'Hoogstraten', 'Horebeke', 'Houthalen-Helchteren', 'Houthulst', 'Hove', 'Huldenberg', 'Hulshout', 'Ichtegem', 'Ieper', 'Ingelmunster', 'Izegem', 'Jabbeke', 'Jette', 'Kalmthout', 'Kampenhout', 'Kapellen', 'Kapelle-op-den-Bos', 'Kaprijke', 'Kasterlee', 'Keerbergen', 'Kinrooi', 'Kluisbergen', 'Knesselare', 'Knokke-Heist', 'Koekelare', 'Koekelberg', 'Koksijde', 'Kontich', 'Kortemark', 'Kortenaken', 'Kortenberg', 'Kortessem', 'Kortrijk', 'Kraainem', 'Kruibeke', 'Kruishoutem', 'Kuurne', 'Laakdal', 'Laarne', 'Lanaken', 'Landen', 'Langemark-Poelkapelle', 'Lebbeke', 'Lede', 'Ledegem', 'Lendelede', 'Lennik', 'Leopoldsburg', 'Leuven', 'Lichtervelde', 'Liedekerke', 'Lier', 'Lierde', 'Lille', 'Linkebeek', 'Lint', 'Linter', 'Lochristi', 'Lokeren', 'Lommel', 'Londerzeel', 'Lo-Reninge', 'Lovendegem', 'Lubbeek', 'Lummen', 'Maarkedal', 'Maaseik', 'Maasmechelen', 'Machelen', 'Maldegem', 'Malle', 'Mechelen', 'Meerhout', 'Meeuwen-Gruitrode', 'Meise', 'Melle', 'Menen', 'Merchtem', 'Merelbeke', 'Merksplas', 'Mesen', 'Meulebeke', 'Middelkerke', 'Moerbeke', 'Mol', 'Moorslede', 'Mortsel', 'Nazareth', 'Neerpelt', 'Nevele', 'Niel', 'Nieuwerkerken', 'Nieuwpoort', 'Nijlen', 'Ninove', 'Olen', 'Oostende', 'Oosterzele', 'Oostkamp', 'Oostrozebeke', 'Opglabbeek', 'Opwijk', 'Oudenaarde', 'Oudenburg', 'Oudergem', 'Oud-Heverlee', 'Oud-Turnhout', 'Overijse', 'Overpelt', 'Peer', 'Pepingen', 'Pittem', 'Poperinge', 'Putte', 'Puurs', 'Ranst', 'Ravels', 'Retie', 'Riemst', 'Rijkevorsel', 'Roeselare', 'Ronse', 'Roosdaal', 'Rotselaar', 'Ruiselede', 'Rumst', 'Schaarbeek', 'Schelle', 'Scherpenheuvel-Zichem', 'Schilde', 'Schoten', 'Sint-Agatha-Berchem', 'Sint-Amands', 'Sint-Genesius-Rode', 'Sint-Gillis', 'Sint-Gillis-Waas', 'Sint-Jans-Molenbeek', 'Sint-Joost-ten-Node', 'Sint-Katelijne-Waver', 'Sint-Lambrechts-Woluwe', 'Sint-Laureins', 'Sint-Lievens-Houtem', 'Sint-Martens-Latem', 'Sint-Niklaas', 'Sint-Pieters-Leeuw', 'Sint-Pieters-Woluwe', 'Sint-Truiden', 'Spiere-Helkijn', 'Stabroek', 'Staden', 'Steenokkerzeel', 'Stekene', 'Temse', 'Ternat', 'Tervuren', 'Tessenderlo', 'Tielt', 'Tielt-Winge', 'Tienen', 'Tongeren', 'Torhout', 'Tremelo', 'Turnhout', 'Ukkel', 'Veurne', 'Vilvoorde', 'Vleteren', 'Voeren', 'Vorselaar', 'Vorst', 'Vosselaar', 'Waarschoot', 'Waasmunster', 'Wachtebeke', 'Waregem', 'Watermaal-Bosvoorde', 'Wellen', 'Wemmel', 'Wervik', 'Westerlo', 'Wetteren', 'Wevelgem', 'Wezembeek-Oppem', 'Wichelen', 'Wielsbeke', 'Wijnegem', 'Willebroek', 'Wingene', 'Wommelgem', 'Wortegem-Petegem', 'Wuustwezel', 'Zandhoven', 'Zaventem', 'Zedelgem', 'Zele', 'Zelzate', 'Zemst', 'Zingem', 'Zoersel', 'Zomergem', 'Zonhoven', 'Zonnebeke', 'Zottegem', 'Zoutleeuw', 'Zuienkerke', 'Zulte', 'Zutendaal', 'Zwalm', 'Zwevegem', 'Zwijndrecht');
		
		private $signup_form = 'Inschrijven';
		private $confirm_form = 'Bevestigen';
		private $pay_form = 'Betalen';
		private $retrieve_form = 'Ophalen';
		private $cancel_form = 'Annuleren';
		private $mail_form = 'Mailen';
		private $not_paid = 'Paid=No';
		private $not_cancelled = 'Verified!=CANCELLED';
		private $is_coming = null;
		private $force_payment = null;
		
		function __construct($payment) {
			$this->force_payment = $payment;
			$this->is_coming = ( $payment ? 'Verified=Yes&&Paid=Yes' : 'Verified=Yes' );
		}
		
		function formSignup() { return do_shortcode('[contact-form-7 id="'.$this->cf7_signup.'" title="'.$this->signup_form.'"]'); }
		function formConfirm() { return do_shortcode('[contact-form-7 id="'.$this->cf7_confirm.'" title="'.$this->confirm_form.'"]'); }
		function formPay() { return do_shortcode('[contact-form-7 id="'.$this->cf7_pay.'" title="'.$this->pay_form.'"]'); }
		function formRetrieve() { return do_shortcode('[contact-form-7 id="'.$this->cf7_retrieve.'" title="'.$this->retrieve_form.'"]'); }
		function formCancel() { return do_shortcode('[contact-form-7 id="'.$this->cf7_cancel.'" title="'.$this->cancel_form.'"]'); }
		function formMail() { return do_shortcode('[contact-form-7 id="'.$this->cf7_mail.'" title="'.$this->mail_form.'"]'); }
		function getSignupTitle() { return $this->signup_form; }
		function getConfirmTitle() { return $this->confirm_form; }
		function getPayTitle() { return $this->pay_form; }
		function getRetrieveTitle() { return $this->retrieve_form; }
		function getCancelTitle() { return $this->cancel_form; }
		function getMailTitle() { return $this->mail_form; }
		function notPaid() { return $this->not_paid."&&".$this->not_cancelled."&&Verified=Yes"; }
		function notCancelled() { return $this->not_cancelled; }
		function getQuiz() { return $this->quiz; }
		function getDate() { return $this->date; }
		function getOrganizer() { return $this->organizer; }
		function getAddress() { return $this->address; }
		function getUrl() { return $this->url; }
		function maxParticipants() { return $this->max_participants; }
		function maxReserves() { return $this->max_reserves; }
		function getSignupDate() { return $this->signup_date; }
		function getCancelDate() { return $this->cancel_date; }
		function getRegion() { return $this->region; }
		function getFixedCategory() { return $this->fixed; }
		function getPrice() { return $this->price; }
		function getAccount() { return $this->account; }
		function showLocation() { return $this->show_location; }
		function getLocations() { return $this->locations; }
		function showTelephone() { return $this->show_telephone; }
		function showReason() { return $this->show_reason; }
		function getReasons() { return $this->reasons; }
		function showDrinks() { return $this->show_drinks; }
		function getDrinks() { return $this->drinks; }
		function showRemarks() { return $this->show_remarks; }
		function forcePayment() { return $this->force_payment; }
		function isComing() { return $this->is_coming; }
	}

	require_once get_stylesheet_directory().'/participants.php';
	
?>
