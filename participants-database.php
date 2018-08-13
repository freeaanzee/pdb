<?php
	/**
	 * Plugin Name:				Participants Database
	 * Plugin URI:				https://github.com/freeaanzee/pdb
	 * Description:				Register, verify and contact participants for your WordPress hosted event.
	 * Version:					2.0.1
	 * Author:					Full Stack Ahead
	 * Author URI:				https://wwww.fullstackahead.be
	 * License:					GNU General Public License v2
	 * License URI:				https://www.gnu.org/licenses/gpl-2.0.html
	 * Text Domain:				pdb
	 * Domain Path:				/languages
	 * GitHub Plugin URI:		https://github.com/freeaanzee/pdb
	 */

	if ( ! defined('ABSPATH') ) exit;



	##############
	# ACTIVATION #
	##############

	register_activation_hook( __FILE__, 'pdb_activation' );	
	
	function pdb_activation() {
		
		if ( ! is_plugin_active('contact-form-7/wp-contact-form-7.php') or ! is_plugin_active('contact-form-7-to-database-extension/contact-form-7-db.php') ) {
			
			// Annuleer activatie indien CF7 of CFDB niet actief is
			deactivate_plugins(__FILE__);
			die( _e( 'This plugin requires <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a> and <a href="https://cfdbplugin.com" target="_blank">Contact Form DB</a> to be activated!', 'pdb' ) );

		} else {

			add_option( 'pdb_cf7_signup_id', 246 );
			add_option( 'pdb_cf7_confirm_id', 146 );
			add_option( 'pdb_cf7_retrieve_id', 247 );
			add_option( 'pdb_cf7_cancel_id', 13 );
			add_option( 'pdb_cf7_pay_id', 182 );
			add_option( 'pdb_cf7_mail_id', 341 );
			
			add_option( 'pdb_event_title', '1ste Deelnemersdatabase' );
			// Dankzij de streepjes interpreteert strtotime() dit als een Europese datum!
			add_option( 'pdb_event_date', '01-01-2019' );
			add_option( 'pdb_event_organizer', 'KoeKedozeKlan' );
			add_option( 'pdb_event_mail', get_option('admin_email') );
			add_option( 'pdb_event_url', home_url('/') );
			add_option( 'pdb_event_max_participants', 10 );
			add_option( 'pdb_event_max_reserves', 2 );
			add_option( 'pdb_event_signup_limit', '31-12-2018 12:00' );
			add_option( 'pdb_event_cancel_limit', '31-12-2018 12:00' );
			add_option( 'pdb_event_region', 'WVL' );
			add_option( 'pdb_event_fixed_category', false );
			add_option( 'pdb_event_price', 20 );
			add_option( 'pdb_event_iban', 'BE55 9730 9371 2744' );
			
			add_option( 'pdb_enable_location', true );
			add_option( 'pdb_enable_telephone', true );
			add_option( 'pdb_enable_drinks', true );
			add_option( 'pdb_enable_reasons', true );
			add_option( 'pdb_enable_remarks', true );
			add_option( 'pdb_enable_payments', false );

			add_option( 'pdb_locations', array( 'Aalst', 'Aalter', 'Aarschot', 'Aartselaar', 'Affligem', 'Alken', 'Alveringem', 'Anderlecht', 'Antwerpen', 'Anzegem', 'Ardooie', 'Arendonk', 'As', 'Asse', 'Assenede', 'Avelgem', 'Baarle-Hertog', 'Balen', 'Beernem', 'Beerse', 'Beersel', 'Begijnendijk', 'Bekkevoort', 'Beringen', 'Berlaar', 'Berlare', 'Bertem', 'Bever', 'Beveren', 'Bierbeek', 'Bilzen', 'Blankenberge', 'Bocholt', 'Boechout', 'Bonheiden', 'Boom', 'Boortmeerbeek', 'Borgloon', 'Bornem', 'Borsbeek', 'Boutersem', 'Brakel', 'Brasschaat', 'Brecht', 'Bredene', 'Bree', 'Brugge', 'Brussel', 'Buggenhout', 'Damme', 'De Haan', 'De Panne', 'De Pinte', 'Deerlijk', 'Deinze', 'Denderleeuw', 'Dendermonde', 'Dentergem', 'Dessel', 'Destelbergen', 'Diepenbeek', 'Diest', 'Diksmuide', 'Dilbeek', 'Dilsen-Stokkem', 'Drogenbos', 'Duffel', 'Edegem', 'Eeklo', 'Elsene', 'Erpe-Mere', 'Essen', 'Etterbeek', 'Evere', 'Evergem', 'Galmaarden', 'Ganshoren', 'Gavere', 'Geel', 'Geetbets', 'Genk', 'Gent', 'Geraardsbergen', 'Gingelom', 'Gistel', 'Glabbeek', 'Gooik', 'Grimbergen', 'Grobbendonk', 'Haacht', 'Haaltert', 'Halen', 'Halle', 'Ham', 'Hamme', 'Hamont-Achel', 'Harelbeke', 'Hasselt', 'Hechtel-Eksel', 'Heers', 'Heist-op-den-Berg', 'Hemiksem', 'Herent', 'Herentals', 'Herenthout', 'Herk-de-Stad', 'Herne', 'Herselt', 'Herstappe', 'Herzele', 'Heusden-Zolder', 'Heuvelland', 'Hoegaarden', 'Hoeilaart', 'Hoeselt', 'Holsbeek', 'Hooglede', 'Hoogstraten', 'Horebeke', 'Houthalen-Helchteren', 'Houthulst', 'Hove', 'Huldenberg', 'Hulshout', 'Ichtegem', 'Ieper', 'Ingelmunster', 'Izegem', 'Jabbeke', 'Jette', 'Kalmthout', 'Kampenhout', 'Kapellen', 'Kapelle-op-den-Bos', 'Kaprijke', 'Kasterlee', 'Keerbergen', 'Kinrooi', 'Kluisbergen', 'Knesselare', 'Knokke-Heist', 'Koekelare', 'Koekelberg', 'Koksijde', 'Kontich', 'Kortemark', 'Kortenaken', 'Kortenberg', 'Kortessem', 'Kortrijk', 'Kraainem', 'Kruibeke', 'Kruishoutem', 'Kuurne', 'Laakdal', 'Laarne', 'Lanaken', 'Landen', 'Langemark-Poelkapelle', 'Lebbeke', 'Lede', 'Ledegem', 'Lendelede', 'Lennik', 'Leopoldsburg', 'Leuven', 'Lichtervelde', 'Liedekerke', 'Lier', 'Lierde', 'Lille', 'Linkebeek', 'Lint', 'Linter', 'Lochristi', 'Lokeren', 'Lommel', 'Londerzeel', 'Lo-Reninge', 'Lovendegem', 'Lubbeek', 'Lummen', 'Maarkedal', 'Maaseik', 'Maasmechelen', 'Machelen', 'Maldegem', 'Malle', 'Mechelen', 'Meerhout', 'Meeuwen-Gruitrode', 'Meise', 'Melle', 'Menen', 'Merchtem', 'Merelbeke', 'Merksplas', 'Mesen', 'Meulebeke', 'Middelkerke', 'Moerbeke', 'Mol', 'Moorslede', 'Mortsel', 'Nazareth', 'Neerpelt', 'Nevele', 'Niel', 'Nieuwerkerken', 'Nieuwpoort', 'Nijlen', 'Ninove', 'Olen', 'Oostende', 'Oosterzele', 'Oostkamp', 'Oostrozebeke', 'Opglabbeek', 'Opwijk', 'Oudenaarde', 'Oudenburg', 'Oudergem', 'Oud-Heverlee', 'Oud-Turnhout', 'Overijse', 'Overpelt', 'Peer', 'Pepingen', 'Pittem', 'Poperinge', 'Putte', 'Puurs', 'Ranst', 'Ravels', 'Retie', 'Riemst', 'Rijkevorsel', 'Roeselare', 'Ronse', 'Roosdaal', 'Rotselaar', 'Ruiselede', 'Rumst', 'Schaarbeek', 'Schelle', 'Scherpenheuvel-Zichem', 'Schilde', 'Schoten', 'Sint-Agatha-Berchem', 'Sint-Amands', 'Sint-Genesius-Rode', 'Sint-Gillis', 'Sint-Gillis-Waas', 'Sint-Jans-Molenbeek', 'Sint-Joost-ten-Node', 'Sint-Katelijne-Waver', 'Sint-Lambrechts-Woluwe', 'Sint-Laureins', 'Sint-Lievens-Houtem', 'Sint-Martens-Latem', 'Sint-Niklaas', 'Sint-Pieters-Leeuw', 'Sint-Pieters-Woluwe', 'Sint-Truiden', 'Spiere-Helkijn', 'Stabroek', 'Staden', 'Steenokkerzeel', 'Stekene', 'Temse', 'Ternat', 'Tervuren', 'Tessenderlo', 'Tielt', 'Tielt-Winge', 'Tienen', 'Tongeren', 'Torhout', 'Tremelo', 'Turnhout', 'Ukkel', 'Veurne', 'Vilvoorde', 'Vleteren', 'Voeren', 'Vorselaar', 'Vorst', 'Vosselaar', 'Waarschoot', 'Waasmunster', 'Wachtebeke', 'Waregem', 'Watermaal-Bosvoorde', 'Wellen', 'Wemmel', 'Wervik', 'Westerlo', 'Wetteren', 'Wevelgem', 'Wezembeek-Oppem', 'Wichelen', 'Wielsbeke', 'Wijnegem', 'Willebroek', 'Wingene', 'Wommelgem', 'Wortegem-Petegem', 'Wuustwezel', 'Zandhoven', 'Zaventem', 'Zedelgem', 'Zele', 'Zelzate', 'Zemst', 'Zingem', 'Zoersel', 'Zomergem', 'Zonhoven', 'Zonnebeke', 'Zottegem', 'Zoutleeuw', 'Zuienkerke', 'Zulte', 'Zutendaal', 'Zwalm', 'Zwevegem', 'Zwijndrecht' ) );
			add_option( 'pdb_drinks', array( 'warme dranken', 'zware bieren', 'frisdrank', 'pintjes', 'wijn' ) );
			add_option( 'pdb_reasons', array( 'uit sympathie voor het goede doel', 'om gezellig iets te drinken', 'ter meerdere eer en glorie', 'voor de prijzentafel' ) );

		}
		
	}



	##################
	# CONTACT FORM 7 #
	##################

	require_once WP_PLUGIN_DIR.'/contact-form-7-to-database-extension/CFDBFormIterator.php';
	
	add_shortcode( 'inschrijvingsformulier', 'print_signup_form' );
	add_shortcode( 'uitschrijvingsformulier', 'print_cancel_form' );
	add_shortcode( 'bevestigingsformulier', 'print_confirm_form' );
	add_shortcode( 'betalingsformulier', 'print_pay_form' );
	add_shortcode( 'ophalingsformulier', 'print_retrieve_form' );
	add_shortcode( 'mailformulier', 'print_mail_form' );
	
	add_shortcode( 'deelnemers', 'printParticipants' );
	add_shortcode( 'statistieken', 'printStatistics' );
	
	add_shortcode( 'ploeg', 'get_team' );
	add_shortcode( 'verantwoordelijke', 'get_responsible' );
	add_shortcode( 'mail', 'get_mail' );
	
	add_shortcode( 'city', 'text_city' );
	add_shortcode( 'locations', 'select_locations' );
	add_shortcode( 'telephone', 'text_telephone' );
	add_shortcode( 'reasons', 'select_reasons' );
	add_shortcode( 'drinks', 'select_drinks' );
	add_shortcode( 'remarks', 'text_remarks' );
	add_shortcode( 'teams', 'select_teams' );
	add_shortcode( 'groups', 'select_groups' );
	
	// Verberg de CF7-configuratiefouten
	add_filter( 'wpcf7_validate_configuration', '__return_false' );

	// Voer shortcodes ook uit in formulieren
	add_filter( 'wpcf7_form_elements', 'execute_shortcodes', 10, 1 );
	
	function execute_shortcodes( $form ) {
		return do_shortcode($form);
	}

	// Zorg ervoor dat we get_query_var() kunnen gebruiken
	add_filter( 'query_vars', 'add_query_vars', 10, 1 );
	
	function add_query_vars( $vars ) {
		$vars[] = 'Token';
		$vars[] = 'Team';
		$vars[] = 'Strength';
		$vars[] = 'Responsible';
		$vars[] = 'Mail';
		$vars[] = 'Location';
		return $vars;
	}

	// Herlaad pagina zodat de lijst met teams die nog moeten betalen up to date is
	add_action( 'wp_footer', 'refresh_pay_form_on_submit' );
	
	function refresh_pay_form_on_submit() {
		?>
		<script>
			/* Andere events: wpcf7submit, wpcf7invalid, wpcf7mailfailed */
			document.addEventListener( 'wpcf7mailsent', function(event) {
				if ( event.detail.contactFormId == get_option('pdb_cf7_pay_id') ) {
					setTimeout( function() { window.location = '<?php echo get_event_url(); ?>betalen/'; }, 5000 );
				}
			}, false );
		</script>
		<?php
	}

	// Voeg custom CF7-tags toe (bij voorkeur uit te breiden naar alle velden die herbruikt worden!)
	add_action( 'wpcf7_init', 'wpcf7_add_form_tag_dynamicselect' );
 
	function wpcf7_add_form_tag_dynamicselect() {
		wpcf7_add_form_tag(	array( 'dynamicselect', 'dynamicselect*' ), 'dynamicselect_tag_handler', array( 'name-attr' => true ) );
	}

	function dynamicselect_tag_handler( $tag ) {
		$tag = new WPCF7_FormTag( $tag );
		
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );
		$class = wpcf7_form_controls_class( $tag->type );
		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = array(
			'name' => $tag->name,
			'class' => $tag->get_class_option( $class ),
			'aria-invalid' => $validation_error ? true : false,
		);

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$value = '';
		if ( $tag->name === 'Location' ) {
			if ( ! location_enabled() ) {
				return NULL;
			}

			if ( ! $tag->is_required() ) {
				$value = 'Niemandsland';
			}

			$options = get_locations();
		} elseif ( $tag->name === 'Drinks' ) {
			if ( ! drinks_enabled() ) {
				return NULL;
			}

			if ( ! $tag->is_required() ) {
				$value = 'niets doen';
			}

			$options = get_drinks();
		} elseif ( $tag->name === 'Reason' ) {
			if ( ! reasons_enabled() ) {
				return NULL;
			}

			if ( ! $tag->is_required() ) {
				$value = 'trekken we eens aan onze aap';
			}

			$options = get_reasons();
		}

		// Probleem: 'disabled'-attribuut zorgt ervoor dat de waarde totaal niet doorgestuurd wordt
		$dynamicselect = '<option value="'.$value.'" selected>(selecteer)</option>';
		foreach ( $options as $option ) {
			// get_query_var() zet %20's uit URL automatisch om maar bewaart slashes voor single quotes!
			$dynamicselect .= '<option value="'.$option.'"'.selected( $option, stripslashes( get_query_var($tag->name) ) ).'>'.$option.'</option>';
		}
		$dynamicselect = sprintf( '<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>', sanitize_html_class( $tag->name ), wpcf7_format_atts($atts), $dynamicselect, $validation_error );
		
		return $dynamicselect;
	}

	// Filters voor valideren van e-mailadressen en tekstvelden altijd toevoegen (daarna $_POST checken)
	add_filter( 'wpcf7_validate_email*', 'validate_cf7_fields', 20, 2 );
	add_filter( 'wpcf7_validate_text*', 'validate_cf7_fields', 20, 2 );
	// Omdat we de mogelijkheid toegevoegd hebben om de ploeg dynamisch in te vullen moeten we ook deze filter toepassen!
	add_filter( 'wpcf7_validate_dynamictext*', 'validate_cf7_fields', 20, 2 );
	
	function validate_cf7_fields( $result, $tag ) {
		if ( $_POST['_wpcf7'] == get_option('pdb_cf7_signup_id') ) {
			validate_duplicates( $result, $tag );
		}

		if ( $_POST['_wpcf7'] == get_option('pdb_cf7_retrieve_id') ) {
			validate_matches( $result, $tag );
		}
		
		return $result;
	}

	// Optioneel: dwing antwoorden op custom selects af 
	add_filter( 'wpcf7_validate_dynamicselect*', 'validate_cf7_selects', 20, 2 );
	
	function validate_cf7_selects( $result, $tag ) {
		if ( $tag->is_required() and empty( $_POST[ $tag['name'] ] ) ) {
			$result->invalidate( $tag['name'], wpcf7_get_message('invalid_required') );
		}

		return $result;
	}

	// Check of de ploegnaam en het e-mailadres al eens gebruikt zijn	
	function validate_duplicates( $result, $tag ) {
		$key = $tag['name'];
		$value = $_POST[$key];

		if ( $key === 'Team' and check_duplicate( format_team($value), $key ) ) {
			$result->invalidate( $key, 'Deze ploeg is al ingeschreven!' );
		}
		
		if ( $key === 'Mail' and check_duplicate( format_mail($value), $key ) ) {
			$result->invalidate( $key, 'Dit e-mailadres wordt al gebruikt!' );
		}

		return $result;
	}

	// Check of de ploegnaam, de verantwoordelijke en het e-mailadres overeenkomen met een niet-geannuleerde inschrijving
	function validate_matches( $result, $tag ) {
		$key = $tag['name'];
		$value = $_POST[$key];

		if ( $key === 'Mail' and ! check_duplicate( format_mail($value).'&&'.get_uncancelled_participants_param(), $key ) ) {
			$result->invalidate( $key, 'Onbekend e-mailadres!' );
		}
		if ( $key === 'Team' and ! check_duplicate( format_team($value).'&&'.get_uncancelled_participants_param(), $key ) ) {
			$result->invalidate( $key, 'Onbekende ploegnaam!' );
		}
		if ( $key === 'Responsible' and ! check_duplicate( format_responsible($value).'&&'.get_uncancelled_participants_param(), $key ) ) {
			$result->invalidate( $key, 'Onbekende verantwoordelijke!' );
		}
		
		// Check of 3 velden wel tot dezelfde inschrijving behoren gebeurt in format_retrieve()
		return $result;
	}
	
	function check_duplicate( $value, $field ) {
		if ( empty($value) ) {
			return false;
		}

		$iterator = new CFDBFormIterator();
		$iterator->export( get_signup_form_title(), array( 'filter' => $field.'='.$value ) );
		if ( $iterator->nextRow() === false ) {
			return false;
		} else {
			return true;
		}
	}

	// Algemene filter voor bewerken van verzonden formulierdata
	add_filter ( 'wpcf7_posted_data', 'format_cf7_fields' );

	function format_cf7_fields( $posted_data ) {
		write_log( serialize($posted_data) );
		
		switch ( $posted_data['_wpcf7'] ) {
			case get_option('pdb_cf7_signup_id'):
				return format_inscription($posted_data);

			case get_option('pdb_cf7_confirm_id'):
				return format_confirmation($posted_data);
			
			case get_option('pdb_cf7_retrieve_id'):
				return format_retrieve($posted_data);

			case get_option('pdb_cf7_cancel_id'):
				return format_cancellation($posted_data);

			case get_option('pdb_cf7_pay_id'):
				return format_payment($posted_data);

			case get_option('pdb_cf7_mail_id'):
				return do_mailing($posted_data);

			case get_option('pdb_cf7_contact_id'):
				return format_feedback($posted_data);
			
			default:
				return $posted_data;
		}
	}



	###############
	# PDB GETTERS #
	###############

	function get_signup_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_signup_id').'"]');
	}
	
	function get_confirm_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_confirm_id').'"]');
	}
	
	function get_retrieve_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_retrieve_id').'"]');
	}
	
	function get_cancel_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_cancel_id').'"]');
	}

	function get_pay_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_pay_id').'"]');
	}

	function get_mail_form() {
		return do_shortcode('[contact-form-7 id="'.get_option('pdb_cf7_mail_id').'"]');
	}
	
	function get_event_title() {
		return get_option('pdb_event_title');
	}
	
	function get_event_date( $format = 'd-m-Y' ) {
		return date_i18n( $format, strtotime( get_option('pdb_event_date') ) );
	}
	
	function get_event_organizer() {
		return get_option('pdb_event_organizer');
	}
	
	function get_event_mail() {
		return get_option('pdb_event_mail');
	}

	function get_event_sender() {
		$parts = explode( ',', get_option('pdb_event_mails') );
		return $parts[0];
	}
	
	function get_event_url() {
		return get_option('pdb_event_url');
	}
	
	function get_max_participants() {
		return intval( get_option('pdb_event_max_participants') );
	}
	
	function get_max_reserves() {
		return intval( get_option('pdb_event_max_reserves') );
	}
	
	function get_signup_limit_timestamp() {
		// CHECK OF ER AANPASSINGEN NODIG ZIJN VOOR TIJDZONE
		return strtotime( get_option('pdb_event_signup_limit') );
	}
	
	function get_cancel_limit_timestamp() {
		// CHECK OF ER AANPASSINGEN NODIG ZIJN VOOR TIJDZONE
		return strtotime( get_option('pdb_event_cancel_limit') );
	}
	
	function get_region() {
		return get_option('pdb_event_region');
	}
	
	function get_fixed_category() {
		return intval( get_option('pdb_event_fixed_category') );
	}
	
	function get_price() {
		return get_option('pdb_event_price');
	}
	
	function get_iban() {
		return get_option('pdb_event_iban');
	}
	
	function location_enabled() {
		return get_option('pdb_enable_location');
	}

	function telephone_enabled() {
		return get_option('pdb_enable_telephone');
	}

	function drinks_enabled() {
		return get_option('pdb_enable_drinks');
	}
	
	function reasons_enabled() {
		return get_option('pdb_enable_reasons');
	}

	function remarks_enabled() {
		return get_option('pdb_enable_remarks');
	}

	function payments_enabled() {
		return get_option('pdb_enable_payments');
	}
	
	function get_locations() {
		return get_option('pdb_locations');
	}

	function get_drinks() {
		return get_option('pdb_drinks');
	}
	
	function get_reasons() {
		return get_option('pdb_reasons');
	}
	
	function get_form_title( $form ) {
		if ( $form !== NULL ) {
			return $form->post_title;
		} else {
			return '';
		}
	}

	function get_signup_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_signup_id') ) );
	}
	
	function get_confirm_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_confirm_id') ) );
	}

	function get_retrieve_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_retrieve_id') ) );
	}
	
	function get_cancel_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_cancel_id') ) );
	}

	function get_pay_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_pay_id') ) );
	}
	
	function get_mail_form_title() {
		return get_form_title( get_post( get_option('pdb_cf7_mail_id') ) );
	}
	
	function get_confirmed_participants_param() {
		if ( payments_enabled() ) {
			return 'Verified=Yes&&Paid=Yes';
		} else {
			return 'Verified=Yes';
		}
	}

	function get_guaranteed_participants_param() {
		return get_confirmed_participants_param().'&&Reserve=No';
	}

	function get_unpaid_participants_param() {
		if ( payments_enabled() ) {
			return 'Verified=Yes&&Reserve=No&&Paid=No';
		} else {
			return 'Verified=Yes&&Reserve=No&&Paid=N/A';
		}
	}

	function get_uncancelled_participants_param() {
		return 'Verified!=CANCELLED';
	}

	// Geef het huidige aantal geverifieerde (en indien nodig: betaalde) deelnemers, inclusief reserveploegen
	function participants() {
		return intval( do_shortcode('[cfdb-count form="'.get_signup_form_title().'" filter="'.get_confirmed_participants_param().'"]') );
	}

	function get_team() {
		return get_form_row_data('Team');
	}
	
	function get_responsible() {
		return get_form_row_data('Responsible');
	}
	
	function get_mail() {
		return get_form_row_data('Mail');
	}

	function get_token() {
		return get_form_row_data();
	}

	function get_strength() {
		return get_form_row_data('Strength');
	}

	function trim_and_ucwords( $value ) {
		return ucwords( strtolower( trim($value) ) );
	}

	function format_team( $value ) {
		// Ook hoofdletters zetten na punten, trema's en slashes
		return preg_replace_callback( '|[./-].*?\w|', create_function( '$atts', 'return strtoupper($atts[0]);' ), trim_and_ucwords($value) );
	}

	function format_responsible( $value ) {
		// Ook hoofdletters zetten na punten, trema's en slashes
		return preg_replace_callback( '|[./-].*?\w|', create_function( '$atts', 'return strtoupper($atts[0]);' ), trim_and_ucwords($value) );
	}

	function format_mail( $value ) {
		return strtolower( trim($value) );
	}

	function format_phone( $orig_value, $slash = ' ', $delim = ' ' ) {
		// Wis alle spaties, leestekens en landcodes
		$value = preg_replace( '/[\s\-\.\/]/', '', $orig_value );
		$value = str_replace( '+32', '0', $value );
		$value = preg_replace( '/(^|\s)0032/', '0', $value );
			
		if ( mb_strlen($value) === 9 ) {
			// Formatteer vaste telefoonnummers
			if ( intval($value[1]) === 2 or intval($value[1]) === 3 or intval($value[1]) === 4 or intval($value[1]) === 9 ) {
				// Zonenummer bestaande uit twee cijfers in de grote steden!
				return substr( $value, 0, 2 ) . $slash . substr( $value, 2, 3 ) . $delim . substr( $value, 5, 2 ) . $delim . substr( $value, 7, 2 );
			} else {
				return substr( $value, 0, 3 ) . $slash . substr( $value, 3, 2 ) . $delim . substr( $value, 5, 2 ) . $delim . substr( $value, 7, 2 );
			}
		} elseif ( mb_strlen($value) === 10 ) {
			// Formatteer mobiele telefoonnummers
			return substr( $value, 0, 4 ) . $slash . substr( $value, 4, 2 ) . $delim . substr( $value, 6, 2 ) . $delim . substr( $value, 8, 2 );
		} else {
			return $orig_value;
		}
	}



	#####################
	# PDB FORM HANDLERS #
	#####################

	// Haal data op uit het inschrijvingsformulier via de URL-token
	function get_form_row_data( $column = 'Token' ) {
		// Retourneert $default (= lege string) indien GET-parameter niet aanwezig 
		if ( mb_strlen( get_query_var('Token') ) === 32 ) {
			$iterator = new CFDBFormIterator();
			$iterator->export( get_signup_form_title(), array( 'filter' => 'Token='.get_query_var('Token') ) );
			$row = $iterator->nextRow();
			if ( $row !== false ) {
				if ( array_key_exists( $column, $row ) ) {
					return $row[$column];
				} else {
					return "Onbestaande kolom!";
				}
			}
		} else {
			return "Ongeldige toegangscode!";
		}
	}

	// Behandel de inschrijving
	function format_inscription( $posted_data ) {
		// Bevolk de velden voor de mail
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['Address'] = get_event_mail();
		$posted_data['Title'] = get_event_title();
		$posted_data['Date'] = get_event_date('d/m/Y');
		
		$posted_data['Team'] = format_team( $posted_data['Team'] );
		$posted_data['Responsible'] = format_responsible( $posted_data['Responsible'] );
		$posted_data['Mail'] = format_mail( $posted_data['Mail'] );
		
		if ( telephone_enabled() ) {
			$posted_data['Telephone'] = format_phone( $posted_data['Telephone'] );
		}
		if ( remarks_enabled() ) {
			$posted_data['Remarks'] = trim($posted_data['Remarks']);
		}

		if ( payments_enabled() ) {
			$posted_data['Paid'] = 'No';
		} else {
			$posted_data['Paid'] = 'N/A';
		}

		// Creëer een code waarmee we de registratie later opnieuw kunnen opvragen
		$posted_data['Token'] = bin2hex( openssl_random_pseudo_bytes(16) );
		$posted_data['URL'] = get_event_url().'bevestigen/?Token='.$posted_data['Token'];
		$posted_data['Reserve'] = 'No';
		$posted_data['Verified'] = 'No';
		
		return $posted_data;
	}
	
	// Behandel de bevestiging
	function format_confirmation( $posted_data ) {
		// Bevolk de velden voor de mail (behalve 'Address')
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['Title'] = get_event_title();
		$posted_data['URL'] = get_event_url().'annuleren/?Token='.$posted_data['Token'];
		$posted_data['Date'] = get_event_date('d/m/Y');

		if ( mb_strlen($posted_data['Token']) === 32 ) {
			global $wpdb;
			$iterator = new CFDBFormIterator();
			$filter = 'Token='.$posted_data['Token'].'&&'.get_uncancelled_participants_param();
			$iterator->export( get_signup_form_title(), array( 'filter' => $filter ) );
			$row = $iterator->nextRow();
			
			if ( $row !== false ) {
				if ( location_enabled() ) {
					$posted_data['Location'] = $row['Location'];
					$posted_data['Helper'] = '&nbsp;uit '.$row['Location'];
				} else { 
					$posted_data['Helper'] = '';
				}
				
				// Of gewoon arrays kopïeren?
				if ( reasons_enabled() ) {
					$posted_data['Reason'] = str_replace( array( ' we ', ' onze ' ), array( ' ze ', ' hun ' ), $row['Reason'] );
				} else {
					$posted_data['Reason'] = '';
				}
				if ( drinks_enabled() ) {
					$posted_data['Drinks'] = str_replace( array( ' we ', ' onze ' ), array( ' ze ', ' hun ' ), $row['Drinks'] );
				} else {
					$posted_data['Drinks'] = '';
				}
				if ( telephone_enabled() ) $posted_data['Telephone'] = $row['Telephone']; else $posted_data['Telephone'] = '';
				if ( remarks_enabled() ) $posted_data['Remarks'] = $row['Remarks']; else $posted_data['Remarks'] = '';
				
				if ( $row['Verified'] === 'Yes' ) {
					// Werp een verzenderror op door de bestemmeling te verwijderen WERKT NIET MEER?
					$posted_data['Address'] = '';
					$posted_data['Verified'] = 'ERROR: Data row already verified';
				} else {
					// Indien de regel nog niet eerder bevestigd werd: authoriseer de bevestiging in de CF7DB-tabel
					$data = array(
						'field_value' => 'Yes',
					);
					$where = array(
						'form_name' => get_signup_form_title(),
						'field_name' => 'Verified',
						'submit_time' => $row['submit_time'],
					);
					if ( $wpdb->update( $wpdb->prefix.'cf7dbplugin_submits', $data, $where ) !== false ) {
						$posted_data['Verified'] = $data['field_value'];
					}
					
					// Voeg een waarschuwing toe indien er moet worden betaald vooraleer de inschrijving definitief is
					if ( payments_enabled() ) {
						$posted_data['Paid'] = 'De registratie wordt echter pas definitief na betaling van '.get_price().' euro op het rekeningnummer '.get_iban().'.&nbsp;';
					} else {
						$posted_data['Paid'] = '';
					}
					
					// Voeg een waarschuwing toe indien de ploeg op de reservelijst zal belanden	
					if ( participants() > get_max_participants() ) {
						if ( payments_enabled() ) {
							$posted_data['Reserve'] = 'Opgelet: omdat de quiz momenteel volzet is, zult u op de reservelijst terechtkomen. Indien u effectief uit de boot blijkt te vallen betalen wij het inschrijvingsgeld uiteraard zo snel mogelijk terug.&nbsp;';
						} else {
							$posted_data['Reserve'] = 'Opgelet: omdat de quiz momenteel volzet is, bent u op de reservelijst beland. Van zodra er voldoende ploegen uitgeschreven hebben, ontvangt u een e-mail en verschijnt er in de deelnemerslijst een tafelnummer voor de naam van uw ploeg.&nbsp;';
						}

						// Registreer de reservenstatus in de CF7DB-tabel
						$where['field_name'] = 'Reserve';
						$wpdb->update( $wpdb->prefix.'cf7dbplugin_submits', $data, $where );
					} else {
						$posted_data['Reserve'] = '';
					}
					
					// Vul nu de bestemmeling in (zodat de mail verstuurd kan worden)
					$posted_data['Address'] = get_event_mail();
				}
			}
		} else {
			// Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = '';
			$posted_data['Verified'] = 'ERROR: No data row found';
		}
		
		return $posted_data;
	}

	// Behandel de ophaling
	function format_retrieve( $posted_data ) {
		// Bevolk de velden voor de mail
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['Address'] = get_event_mail();
		$posted_data['Title'] = get_event_title();
		$posted_data['Date'] = get_event_date('d/m/Y');

		// Formatteer de invoervelden net zoals bij de inschrijving
		$posted_data['Team'] = format_team( $posted_data['Team'] );
		$temp_mail = format_mail( $posted_data['Mail'] );

		// Initialiseer variabelen dat er zeker geen match is indien niets gevonden wordt RESPONSIBLE EVENTUEEL WEGLATEN?
		$submit_team = 0;
		$submit_mail = 1;
		
		$iterator = new CFDBFormIterator();
		$filter = 'Team='.$posted_data['Team'].'&&'.get_uncancelled_participants_param();
		$iterator->export( get_signup_form_title(), array( 'filter' => $filter ) );
		$row = $iterator->nextRow();
		if ( $row !== false ) {
			$submit_time_team = $row['submit_time'];	
		}
		
		$filter = 'Mail='.$temp_mail.'&&'.get_uncancelled_participants_param();
		$iterator->export( get_signup_form_title(), array( 'filter' => $filter ) );
		$row = $iterator->nextRow();
		if ( $row !== false ) {
			$submit_time_mail = $row['submit_time'];	
		}
		
		if ( $submit_time_team === $submit_time_mail ) {
			// Laat de bestemmeling staan (zodat de mail verstuurd kan worden) als de tijden overeen komen
			$posted_data['Mail'] = $temp_mail;
			$posted_data['Token'] = $row['Token'];	
			$posted_data['URL'] = get_event_url().'annuleren/?Token='.$row['Token'];
		} else {
			// Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Mail'] = '';
			$posted_data['Verified'] = 'ERROR: Fields do not match';
		}
		
		return $posted_data;
	}

	// Behandel de uitschrijving
	function format_cancellation( $posted_data ) {
		global $wpdb;
		$iterator = new CFDBFormIterator();
		
		// Check of er een ploeg van de reservebank gehaald moet worden
		if ( participants() > get_max_participants() ) {
			$filter = get_confirmed_participants_param().'&&Reserve=Yes';
			$iterator->export( get_signup_form_title(), array( 'filter' => $filter, 'orderby' => 'Submitted asc') );
			// Plaats de oudste reserveploeg in het geheugen
			$reserve = $iterator->nextRow();
		} else {
			$reserve = false;
		}
		
		// Bevolk de velden voor de mail
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['Title'] = get_event_title();
		$posted_data['Date'] = get_event_date('d/m/Y');

		if ( mb_strlen($posted_data['Token']) === 32 ) {
			$filter = 'Token='.$posted_data['Token'].'&&'.get_uncancelled_participants_param();
			$iterator->export( get_signup_form_title(), array( 'filter' => $filter ) );
			$row = $iterator->nextRow();
			if ( $row !== false ) {
				$posted_data['Reason'] = trim($posted_data['Reason']);
				
				// Indien de regel nog niet eerder geanuuleerd werd: authoriseer de annulering in de CF7DB-tabel
				$data = array(
					'field_value' => 'CANCELLED',
				);
				$where = array(
					'form_name' => get_signup_form_title(),
					'field_name' => 'Verified',
					'submit_time' => $row['submit_time'],
				);
				if ( $wpdb->update( $wpdb->prefix.'cf7dbplugin_submits', $data, $where ) !== false ) {
					$posted_data['Verified'] = $data['field_value'];
				}
				
				// Vul nu de bestemmeling in (zodat de mail verstuurd kan worden)
				$posted_data['Address'] = get_event_mail();
				
				// Stuur een mail naar de 1ste reserve indien beschikbaar én indien de uitschrijving zelf niet van een reserveploeg komt
				if ( $reserve !== false and $row['Reserve'] !== 'Yes' ) {
					$to = $reserve['Responsible'].' <'.$reserve['Mail'].'>';
					$subject = 'Deelname '.get_event_title();
					$message = '<p>Beste '.$reserve['Responsible'].'</p><p>Goed nieuws: dankzij de uitschrijving van '.$posted_data['Team'].' kunt u alsnog deelnemen aan de '.get_event_title().' op '.get_event_date('l j F Y').'! U zit niet langer op de reservebank.</p><p>Kan ook uw ploeg zich inmiddels niet meer vrijmaken? Schrijf u dan zo snel mogelijk uit <a href="'.get_event_url().'annuleren/?Token='.$reserve['Token'].'" target="_blank">via deze link</a>.</p><p>Met vriendelijke groet</p><p>Koekedoze</p>';
					$header[] = 'From: "'.get_event_organizer().'" <'.get_event_mail().'>';
					$header[] = 'Content-Type: text/html; charset=utf-8';
					wp_mail( $to, $subject, $message, $header );
					
					// Zet het 'Reserve'-veld van deze ploeg op 'No'
					$data = array(
						'field_value' => 'No',
					);
					$where = array(
						'form_name' => get_signup_form_title(),
						'field_name' => 'Reserve',
						'submit_time' => $reserve['submit_time'],
					);
					$wpdb->update( $wpdb->prefix.'cf7dbplugin_submits', $data, $where );
				}
			}
		} else {
	    	// Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = '';
			$posted_data['Verified'] = 'ERROR: No valid token provided';
		}
		
		return $posted_data;
	}

	// Registreer de betaling
	function format_payment( $posted_data ) {
		global $wpdb;
		$iterator = new CFDBFormIterator();
		
		// Bevolk de velden voor de mail
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['URL'] = get_event_url().'resultaten/';
		$posted_data['Title'] = get_event_title();
		$posted_data['Date'] = get_event_date('d/m/Y');
		
		$filter = 'Team='.$posted_data['Team'].'&&'.get_unpaid_participants_param();
		$iterator->export( get_signup_form_title(), array( 'filter' => $filter ) );
		$row = $iterator->nextRow();
		if ( $row !== false ) {
			$posted_data['Responsible'] = $row['Responsible'];
			$posted_data['Mail'] = $row['Mail'];
			$posted_data['Token'] = $row['Token'];
			$posted_data['Reserve'] = '';
			
			// Zet het 'Paid'-veld van deze ploeg op 'Yes'
			$data = array(
				'field_value' => 'Yes',
			);
			$where = array(
				'form_name' => get_signup_form_title(),
				'field_name' => 'Paid',
				'submit_time' => $row['submit_time'],
			);
			$wpdb->update( $wpdb->prefix.'cf7dbplugin_submits', $data, $where );
			
			// Vul nu de bestemmeling in (zodat de mail verstuurd kan worden)
			$posted_data['Address'] = get_event_mail();
			wp_remote_get( 'http://blynk-cloud.com/'.BLYNK_AUTH.'/update/D8?value=1' );
		} else {
			// Werp een verzenderror op door de bestemmeling te verwijderen
			$posted_data['Address'] = '';
			$posted_data['Verified'] = 'ERROR: No data row found';	
		}
		
		return $posted_data;
	}

	function do_mailing( $posted_data ) {
		$i = 0;
		// Bevolk de velden voor de mail ECHT NODIG?
		$posted_data['Organizer'] = get_event_organizer();
		$posted_data['Address'] = get_event_mail();
		$posted_data['Title'] = get_event_title();
		$posted_data['Date'] = get_event_date('d/m/Y');
		
		switch ( $posted_data['Group'] ) {
			case 'participants':
				$filter = get_guaranteed_participants_param();
				$posted_data['Group'] = 'deelnemers';
				break;

			case 'reserves':
				$filter = get_confirmed_participants_param().'&&Reserve=Yes';
				$posted_data['Group'] = 'reserves';
				break;

			case 'both':
				$filter = get_confirmed_participants_param();
				$posted_data['Group'] = 'deelnemers én reserves';
				break;
			
			case 'debtors':
				$filter = get_unpaid_participants_param();
				$posted_data['Group'] = 'wanbetalers';
				break;

			default:
				$to = get_event_organizer().' <'.get_event_mail().'>';
				$subject = $posted_data['Subject'];
				$message = $posted_data['Message'];
					
				// Vervang de placeholders
				$message = str_replace( "#Naam#", "verantwoordelijke", $message );
				$message = str_replace( "#Ploeg#", "Quizploeg", $message );
				$message = str_replace( "#Stad#", "Fantasialand", $message );
				$message = str_replace( "#URL#", get_event_url(), $message );
				$message = str_replace( "#Code#", "123456789", $message );
				$message = str_replace( "\n", "<br/>", $message );
		
				$header[] = 'From: "'.get_event_organizer().'" <'.get_event_mail().'>';
				$header[] = 'Content-Type: text/html; charset=utf-8';
				if ( wp_mail( $to, $subject, $message, $header ) ) {
					// Tel elke succesvol verstuurde mail
					$i++;
				}
		}

		if ( isset( $filter) ) {
			$iterator = new CFDBFormIterator();
			$iterator->export( get_signup_form_title(), array( 'filter' => $filter, 'orderby' => 'Submitted asc' ) );
			$row = $iterator->nextRow();
			while ( $row !== false ) {
				$to = $row['Responsible'].' <'.$row['Mail'].'>';
				$subject = $posted_data['Subject'];
				$message = $posted_data['Message'];
				
				// Vervang de placeholders
				$message = str_replace( "#Naam#", $row['Responsible'], $message );
				$message = str_replace( "#Ploeg#", $row['Team'], $message );
				$message = str_replace( "#Stad#", $row['Location'], $message );
				$message = str_replace( "#URL#", get_event_url(), $message );
				$message = str_replace( "#Code#", $row['Token'], $message );
				$message = str_replace( "\n", "<br/>", $message );

				$header[] = 'From: "'.get_event_organizer().'" <'.get_event_mail().'>';
				$header[] = 'Content-Type: text/html; charset=utf-8';
				if ( wp_mail( $to, $subject, $message, $header ) ) {
					// Tel elke succesvol verstuurde mail
					$i++;
				}
				$row = $iterator->nextRow();
			}
		}
		
		$posted_data['Message'] = $message;
		$posted_data['Number'] = $i;
		return $posted_data;
	}

	function format_feedback( $posted_data ) {
		$posted_data['Name'] = format_responsible( $posted_data['Name'] );
		$posted_data['Mail'] = format_mail( $posted_data['Mail'] );

		//  Let op de [0]-index in $posted_data['Duplicate'] (= een checkbox!), zoniet nemen we niet enkel het tekstveld maar de hele subarray!
		if ( mb_strlen( $posted_data['Duplicate'][0] ) > 10 ) {
			$posted_data['Receiver'] = $posted_data['Name'].' <'.$posted_data['Mail'].'>';
			$posted_data['Duplicate'][0] = 'Yes';
		} else {
			$posted_data['Receiver'] = 'ERROR: No duplicate requested';
			$posted_data['Duplicate'][0] = 'No';
		}

		return $posted_data;
	}



	######################
	# PDB ELEMENT OUTPUT #
	######################

	// Druk de deelnemerslijst af
	function print_participants() {
		$str = print_participants_info();
		
		// Toon welk ploegen al in de zaal zijn (vergt auto refresh op pagina)
		// $str .= ' <span style="background-color: green; color: white; padding: 3px;">ploeg reeds aanwezig</span>&nbsp;&nbsp;<span style="background-color: red; color: white; padding: 3px;">ploeg nog onderweg</span>';
		
		if ( participants() > get_max_participants() ) {
			$limit = get_max_participants();
		} else {
			$limit = participants();
		}

		$str .= "<ol><ol>";
		for ( $i = 1; $i <= $limit; $i++ ) {
			$str .= "<li><span class='".do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Paid" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.($i-1).',1"]')."'>".do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Team" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.($i-1).',1"]');
			$str .= print_strength( intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.($i-1).',1"]') ) )."</span></li>";
		}
		$str .= "</ol></ol>";
		
		// Druk de ongenummerde reservelijst af indien meer er inschrijvingen zijn dan het maximum aantal deelnemers 
		if ( participants() > get_max_participants() ) {
			$str .= "<ul><ul>";
			for ( $i = get_max_participants(); $i < participants(); $i++ ) {
				$str .= "<li>".do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Team" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.$i.',1"]');
				$str .= print_strength( intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.$i.',1"]') ) )."</li>";
			}
			$str .= "</ul></ul>";
		}
		
		$str .= print_total_strength();
		
		return $str;
	}

	// Voorzie een tekstveld voor de locatie
	function text_city() {
		if ( location_enabled() ) {
			return "<p><b>Gemeente</b><br/><input type='text' name='Location' maxlength='32' required></p>";
		} else {
			return NULL;
		}
	}
	
	// Voorzie een tekstveld voor het telefoonnummer
	function text_telephone() {
		if ( telephone_enabled() ) {
			return "<p><b>Telefoon</b><br/><input type='text' name='Telephone' maxlength='10' required></p>";
		} else {
			return NULL;
		}
	}
	


	###################
	# PDB FORM OUTPUT #
	###################

	// Pas de tekst boven de deelnemerslijst aan naar gelang het aantal ingeschreven deelnemers
	function print_participants_info() {
		if ( participants() === 0 ) {
			$str = "Tot nu toe zijn er nog geen ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Dat kan beter, jongens: ga naar <a href='".get_event_url()."inschrijven/'>het inschrijvingsformulier</a> en breng hier verandering in!";
		}
		
		if ( participants() === 1 ) {
			$str = "Tot nu toe is er nog maar één ploeg ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Dat kan beter, jongens: ga naar <a href='".get_event_url()."inschrijven/'>het inschrijvingsformulier</a> en breng hier verandering in!";
		}
		
		if ( participants() > 1 and participants()/get_max_participants() < 0.4 ) {
			$str = "Tot nu toe zijn er nog maar ".participants()." ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Haast u naar <a href='".get_event_url()."inschrijven/'>het inschrijvingsformulier</a> om daar verandering in te brengen! Quizzen staan erom bekend statistisch gezien erg weinig terroristen (en vrouwen) aan te trekken.";
		}
		
		if ( participants() > 1 and participants()/get_max_participants() >= 0.4 and participants() < get_max_participants() ) {
			$str = "Tot nu toe zijn er al ".participants()." ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Dat wordt weer wringen, daar in Zaal Vuurtoren. Hou deze deelnemerslijst goed in de gaten!";
		}
		
		if ( participants() > 1 and participants()/get_max_participants() >= 0.8 and participants() < get_max_participants() ) {
			$str = "Er waren zo maar eventjes ".participants()." ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Dit was uiteindelijk de definitieve startlijst.";
		}
		
		if ( participants() >= get_max_participants() ) {
			$str = "Het wonder is geschied, hoewel we het zelf nauwelijks kunnen geloven is de ".get_event_title()." van ".get_event_date('l j F Y')." al volledig volzet. Dat we dat nog mogen meemaken, halleluja! ";
			$str .= ( participants() >= ( get_max_participants() + get_max_reserves() ) ) ? "Inschrijven is helaas niet meer mogelijk!" : "Inschrijven kan enkel nog op de reservelijst.";
			if ( participants() > get_max_participants() ) {
				$str .= " Alleen de ploegen in de genummerde lijst zijn al zeker van hun stek.";
			}
		}
		
		return $str;
	}

	// Toon het inschrijvingsformulier (na controle of het niet afgesloten moet worden)
	function print_signup_form() {
		$str = "";
		
		if ( time() < get_signup_limit_timestamp() ) {	
			if ( participants() == 0 ) {
				$str .= "Momenteel zijn er nog geen ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Wordt u de eerste? Vul het formulier in en u hebt een primeur beet! Bovendien mag u quasi op de schoot komen zitten van onze presentator.";
			}
			
			if ( participants() == 1 ) {
				$str .= "Momenteel is er nog maar één ploeg ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Wordt u de fan van het tweede uur? Vul het formulier in en geef de rest het nakijken! Bovendien mag u quasi op de schoot komen zitten van onze presentator.";
			}
			
			if ( participants() > 1 and participants() / get_max_participants() < 0.3 ) {
				$str .= "Momenteel zijn er nog maar ".participants()." ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Wilt u ook toetreden tot dit selecte kransje trendsetters? Vul onderstaand formulier in en uw droom wordt werkelijkheid!";
			}
			
			if ( participants() > 1 and participants() / get_max_participants() >= 0.3 and participants() < get_max_participants() ) { 
				$str .= "Momenteel zijn er al ".participants()." ploegen ingeschreven voor de ".get_event_title()." van ".get_event_date('l j F Y').". Wordt u de volgende? Vul het formulier in en u bent erbij! Opgelet: we laten maximum ".get_max_participants()." tafels toe (en een reservelijst van ".get_max_reserves()." ploegen).";
			}
			
			if ( participants() >= get_max_participants() ) {
				if ( participants() >= ( get_max_participants() + get_max_reserves() ) ) {
					$str .= "Helaas, de inschrijvingen voor de ".get_event_title()." van ".get_event_date('l j F Y')." zijn volledig afgesloten! De maximumcapaciteit van de zaal is bereikt en de reservelijst zit vol. Volgend jaar beter?<br/><br/>";
				} else {
					$str .= "Helaas, de maximumcapaciteit van de ".get_event_title()." is bereikt! U kunt enkel nog inschrijven voor de reservelijst. Als reserveploeg krijgt u voorlopig geen tafelnummer op de deelnemerspagina. Indien een eerder ingeschreven ploeg afzegt, schuift u automatisch een plaatsje op.";
				}	
			}
			
			if ( participants() < ( get_max_participants() + get_max_reserves() ) ) {
				$str .= " In ruil voor onze vragen-en-vertier betaalt u de avond zelf 20 euro inschrijvingsgeld.<br/><br/>".get_signup_form();
			}
		} else {
			$str .= "<p>Inschrijven via de site is helaas niet meer mogelijk!</p><p>Contacteer Adriaan (0485 989 256) of Frederik (0472 788 515) persoonlijk.</p><br/>";
		}
		
		return $str;
	}

	// Toon het bevestigingsformulier
	function print_confirm_form() {
		return "Hieronder vindt u een overzicht van uw gegevens. Klik op de knop om uw inschrijving te bevestigen. (Indien u op onrechtmatige wijze op deze pagina belandde, krijgt u een foutmelding te zien.)<br/><br/>".get_confirm_form();
	}

	// Toon het betalingsformulier
	function print_pay_form() {
		return "<table></table>".get_pay_form();
	}
	
	// Toon het ophalingsformulier
	function print_retrieve_form() {
		return "Bent u de bevestigingsmail met uw speciale uitschrijvingscode kwijt? Vul hieronder exact dezelfde gegevens in als bij uw inschrijving en onze eigen neurotische Peter Paulus Post bezorgt u een nieuwe link.<br/><br/>".get_retrieve_form();
	}
	
	// Toon het annuleringsformulier
	function print_cancel_form() {
		if ( time() < get_cancel_limit_timestamp() ) {
			if ( mb_strlen( get_query_var('Token') ) === 32 ) {
				$str = "Hieronder vindt u een overzicht van uw inschrijvingsgegevens. Klik op de knop om uw knusse zitje op de ".get_event_title()." te annuleren. Opgepast: u zal onmiddellijk van de deelnemerslijst geschrapt worden! Opnieuw inschrijven met deze ploegnaam wordt onmogelijk.<br/><br/>".get_cancel_form();
			} else {
				$str = "Omdat u op onrechtmatige wijze op deze pagina belandde, krijgt u hieronder een foutmelding te zien. Snor de annulatielink in uw bevestigingsmail op, of ga naar <a href='../ophalen/'>de ophaalpagina</a> om een nieuwe link aan te vragen. U hoort het: de KoeKedozeKlan doet er alles aan om de diefstal van uw begeerde identiteit te voorkomen!<br/><br/>".get_cancel_form();
			}
		} else {
    		$str = "<p>Annuleren via de site is helaas niet meer mogelijk!</p><p>Contacteer Adriaan (0485 989 256) of Frederik (0472 788 515) persoonlijk.</p><br/>";
    	}
		
		return $str;
	}
	
	// Toon het mailformulier
	function print_mail_form() {
		return "Enkel ploegen die hun e-mailadres verifieerden door op de link in de bevestigingsmail te klikken, worden opgenomen in de mailing. De mail kan gepersonaliseerd worden met de variabelen #Naam#, #Ploeg#, #Stad#, #URL# en #Code#. Ook gebruik van HTML-opmaak is mogelijk. U krijgt achteraf te zien hoeveel e-mails er verstuurd werden. Als afzender gebruikt het formulier de naam en het e-mailadres van de organisator (zoals opgegeven in functions.php).<br/><br/>".get_mail_form();
	}
	
	// Druk de statistieken af van de bevestigde (en betaalde) niet-reserveploegen
	function print_statistics() {
		if ( participants() > get_max_participants() ) {
			$max = get_max_participants();
		} else {
			$max = participants();
		}

		// Dit vermijdt ook dat we straks door 0 delen!
		if ( participants() > 0 ) {
			$str = '<div id="one-column">';
			for ( $i = 1; $i <= $max; $i++ ) {
				$str .= $i.'. '.do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Team" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.($i-1).',1"]');
				$str .= print_strength( intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.($i-1).',1"]') ) ).'<br/>';
			}

			if ( participants() > get_max_participants() ) {
				for ( $i = get_max_participants(); $i < participants(); $i++ ) {
					$str .= '<br/>&bull; '.do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Team" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.$i.',1"]');
					$str .= print_strength( intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_confirmed_participants_param().'" orderby="Submitted asc" limit="'.$i.',1"]') ) );
				}
			}
			$str .= '</div>';

			if ( reasons_enabled() ) {
				$str .= '<br/><p><b>Acties na fout antwoord</b></p>';
				
				$tot_cnt = 0;
				$stats_reasons = array();
				foreach ( get_reasons() as $reason ) {
					$cnt = intval( do_shortcode('[cfdb-count form="'.get_signup_form_title().'" filter="'.get_guaranteed_participants_param().'&&Reason='.$reason.'" limit="'.$max.'"]') );
					$pct = $cnt / $max * 100;
					$stats_reasons[] = ucfirst($reason).': '.number_format_i18n( $pct, 0 ).' %';
					$tot_cnt += $cnt; 
				}
				
				// Het verschil tussen het totaal aantal deelnemers (zonder reserves) en de som van alle antwoorden zijn de niet-antwoorden			
				$non_pct = ( $max - $tot_cnt ) / $max * 100;
				$stats_reasons[] = 'Geen antwoord: '.number_format_i18n( $non_pct, 0 ).' %';

				$str .= '<ul><li>'.implode( '</li><li>', $stats_reasons ).'</li></ul>';
			}
			
			if ( drinks_enabled() ) {
				$str .= '<br/><p><b>Criminele intenties</b></p>';
				
				$tot_cnt = 0;
				$stats_drinks = array();
				foreach ( get_drinks() as $drink ) {
					$cnt = intval( do_shortcode('[cfdb-count form="'.get_signup_form_title().'" filter="'.get_guaranteed_participants_param().'&&Drinks='.$drink.'" limit="'.$max.'"]') );
					$pct = $cnt / $max * 100;
					$stats_drinks[] = ucfirst($drink).': '.number_format_i18n( $pct, 0 ).' %';
					$tot_cnt += $cnt; 
				}
				
				// Het verschil tussen het totaal aantal deelnemers (zonder reserves) en de som van alle antwoorden zijn de niet-antwoorden			
				$non_pct = ( $max - $tot_cnt ) / $max * 100;
				$stats_drinks[] = 'Geen antwoord: '.number_format_i18n( $non_pct, 0 ).' %';

				$str .= '<ul><li>'.implode( '</li><li>', $stats_drinks ).'</li></ul>';
			}
		} else {
			$str = '<p>Geen inschrijvingen, geen statistieken!</p>';		
		}
		
		return $str;
	}
	
	// Toon het totaal aantal sterktepunten, de voorlopige categorie en de te verdienen vaste punten (afhankelijk van de provincie waar de quiz plaatsvindt)
	function print_total_strength() {
		$fixed_pts = array(
			0	=>	array(4,3,2,1),
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
			50	=>	array(80,64,53,44,37,31,26,22,18,15,12,10,8,7,6,5,4,3,2,1),
		);

		// Provinciale correcties tijdens seizoen 2017-2018
		$corr_pts = array(
			'WVL'	=>	0,
			'OVL'	=>	0,
			'VLB'	=>	0,
			'LIM'	=>	0,
			'ANT'	=>	0,
		);
		
		$num = array( 'nul', 'enige', 'twee', 'drie', 'vier', 'vijf', 'zes', 'zeven', 'acht', 'negen', 'tien' );
		
		// Tel het totale aantal sterktepunten van bevestigde (en indien vereist: betaalde) niet-reserveploegen
		$pts = intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_guaranteed_participants_param().'" limit="0,'.get_max_participants().'" function="sum"]') );

		// Tel het aantal ploegen met één of meerdere sterktepunten (sterkst mogelijke tiental => ordenen volgens dalende sterktepunten!)
		if ( get_max_participants() > 10 ) {
			$limit = 10;
		} else {
			$limit = get_max_participants();
		}
		$cnt = intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" filter="'.get_guaranteed_participants_param().'&&Strength!=0" limit="0,'.$limit.'" orderby="Strength desc" function="count"]') );
		
		// Tel de sterktepunten van de tien sterkste ploegen en stel de voorlopige categorie in
		$maxpts = intval( do_shortcode('[cfdb-value form="'.get_signup_form_title().'" show="Strength" filter="'.get_guaranteed_participants_param().'" limit="0,'.$limit.'" orderby="Strength desc" function="sum"]') );
		$cat = $maxpts;

		// Is het aantal sterktepunten afgetopt?
		$is_topped = ( $pts > $maxpts ) ? true : false;
		
		// Is er een provinciale correctie toegepast?
		$is_prov = false;
		
		if ( $cat < 30 and $corr_pts[get_region()] !== 0 ) {
			// Voer de provinciale correctie door
			$cat += $corr_pts[get_region()];
			if ( $cat > 30 ) {
				// We mochten slechts aanvullen tot maximum 30 sterktepunten!
				$cat = 30;
			}
			$is_prov = true;
		}
		
		// Is de quiz een vaste categorie?
		$is_fixed = ( get_fixed_category() !== 0 ) ? true : false;
		
		// Verhoog (indien nodig) de categorie tot het minimum voor de ingestelde vaste categorie
		if ( get_fixed_category() === 1 and $cat < 50 ) $cat = 50;
		if ( get_fixed_category() === 2 and $cat < 40 ) $cat = 40;
		if ( get_fixed_category() === 3 and $cat < 30 ) $cat = 30;
		
		$delim = ', ';
		$manna = implode( $delim, $fixed_pts[$cat] );
		// Laatste komma vervangen door 'en'
		$manna = substr_replace( $manna, ' en ', strrpos( $manna, $delim ), strlen($delim) );

		# Print de categoriebepaling
		$str = "";
		$prelim = ( time() < get_signup_limit_timestamp() ) ? "voorlopig " : "";
		if ( participants() > 0 and $pts === 0 ) $str = "Er zijn ".$prelim."nog geen sterktepunten aanwezig. ";
		if ( $pts === 1 ) $str = "Er is ".$prelim."nog maar één sterktepunt aanwezig. ";
		if ( $pts >= 2 ) $str = "In totaal zijn er ".$prelim.$pts." sterktepunten aanwezig".( ( participants() > get_max_participants() ) ? " onder de ploegen die zeker zijn van deelname. " : ". " );
		if ( participants() > 0 ) {
			if ( $is_fixed ) {
				$str .= "Aangezien het hier om een VC".get_fixed_category()."-quiz gaat, zal deze quiz sowieso een Cat. ".$cat." worden";
			} else {
				if ( !$is_prov and !$is_topped ) $str .= "Het lijkt ";
				if ( $is_prov or $is_topped ) $str .= "Na ";
				if ( $is_prov ) $str .= "de provinciale correctie toe te passen";
				if ( $is_prov and $is_topped ) $str .= " én ";
				if ( $is_topped ) $str .= "enkel de sterktepunten van de tien sterkste ploegen mee te tellen";
				if ( $is_prov or $is_topped ) $str .= ", lijkt het ";
				$str .= "erop dat deze quiz een Cat. ".$cat." zal worden";
				if ( $is_topped ) $str .= " (in de veronderstelling dat die tien ploegen ook effectief in de top 20 eindigen)";
			}
			$str .= ". Er zijn in die situatie respectievelijk ".$manna." vaste punten te verdienen met de eerste ".$length." plaatsen.";
			if ( $pts > 0 ) {
				$str .= " Daar kom".($maxpts == 1 ? "t" : "en")." in het ideale geval nog eens ".$maxpts;
				$str .= ( $maxpts == 1 ) ? " variabel VQR-punt" : " variabele VQR-punten";
				$str .= " bovenop, wanneer u als kleine David de ".$num[$cnt]." Goliath";
				if ( $cnt !== 1 ) $str .= "s";
				$str .= " met ";
				// Probleem: $cnt bevat het aantal ploegen met sterktepunten, maar telt niet hun totale aantal
				// if ( $cnt === 1 ) $str .= "een ";
				if ( $pts > $maxpts ) $str .= "de meeste ";
				$str .= "sterktepunt";
				if ( $cnt !== 1 ) $str .= "en allemaal";
				$str .= " achter u laat.";
			}
			$str .= ( time() < get_cancel_limit_timestamp() ) ? '<br/><br/>Wenst u in een vlaag van zinsverbijstering toch weer uit te schrijven? Gebruik in dat geval de speciale link die u terugvindt in uw bevestigingsmail. Bent u die mail, als onverbeterlijke sloddervos, kwijtgespeeld? Geen probleem, ga naar <a href="'.get_event_url().'ophalen/">de ophaalpagina</a> om de link opnieuw te verzenden. Zeg nu nog dat de KKK niet vergevingsgezind is!' : '';
		}
			
		return $str;
	}
	
	// Druk het aantal sterktepunten van de ploeg af
	function print_strength( $points ) {
		if ( $points === 0 ) {
			return NULL;
		} else {
			$stars = '*';
			for ( $i = 1; $i < $points; $i++ ) {
				$stars .= '*';
			};
			return ' ('.$stars.')';
		}
	}

	// Creëer een dynamisch dropdownmenu met alle teams die nog niet betaald hebben
	function select_teams() {
		$iterator = new CFDBFormIterator();
		$iterator->export( get_signup_form_title(), array( 'filter' => get_unpaid_participants_param(), 'orderby' => 'Team asc' ) );
		$str = '<select name="Team"><option value="">(selecteer)</option>';
		while ( $row = $iterator->nextRow() ) {
			$str .= '<option value="'.esc_attr($row['Team']).'">'.$row['Team'].'</option>';
		}
		$str .= '</select>';
		return $str;
	}
	
	// Creëer een dropdownmenu met alle doelgroepen
	function select_groups() {
		$str = '<select name="Group">';
		$str .= '<option value="">de organisator (als test)</option>';
		$str .= '<option value="participants">alle deelnemende ploegen</option>';
		$str .= '<option value="reserves">alle ploegen op de reservelijst</option>';
		$str .= '<option value="both">zowel de deelnemers als de reserves</option>';
		if ( payments_enabled() ) $str .= '<option value="debtors">alle inschrijvers die nog niet betaald hebben</option>';
		$str .= '</select>';
		return $str;
	}



	################
	# DEACTIVATION #
	################

	register_uninstall_hook( __FILE__, 'pdb_uninstall' );	
	
	function pdb_uninstall() {
		delete_option('pdb_cf7_signup_id');
		delete_option('pdb_cf7_confirm_id');
		delete_option('pdb_cf7_retrieve_id');
		delete_option('pdb_cf7_cancel_id');
		delete_option('pdb_cf7_pay_id');
		delete_option('pdb_cf7_mail_id');
		
		delete_option('pdb_event_title');
		delete_option('pdb_event_date');
		delete_option('pdb_event_organizer');
		delete_option('pdb_event_mail');
		delete_option('pdb_event_url');
		delete_option('pdb_event_max_participants');
		delete_option('pdb_event_max_reserves');
		delete_option('pdb_event_signup_limit');
		delete_option('pdb_event_cancel_limit');
		delete_option('pdb_event_region');
		delete_option('pdb_event_fixed_category');
		delete_option('pdb_event_price');
		delete_option('pdb_event_iban');
		
		delete_option('pdb_enable_location');
		delete_option('pdb_enable_telephone');
		delete_option('pdb_enable_drinks');
		delete_option('pdb_enable_reasons');
		delete_option('pdb_enable_remarks');
		delete_option('pdb_enable_payments');
		
		delete_option('pdb_locations');
		delete_option('pdb_drinks');
		delete_option('pdb_reasons');	
	}
?>