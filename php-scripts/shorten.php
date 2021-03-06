<?php
	//Import the Database configurations
	include("DB_Connection.php");
	include("DB_Utilities.php");

	// official Link on tool labs server to php scripts
	$LINK_TL_SCRIPT = "tools.wmflabs.org/durl-shortener/php-scripts/shorten.php";

	// official link on tool labs to server
	$LINK_TL = "tools.wmflabs.org/durl-shortener/shortener.php";

	// official Link on localhost server
	$LINK_LH_SCRIPT = "localhost:3000/php-scripts/shorten.php";

	// official Link on localhost server to php scripts
	$LINK_LH = "localhost:3000/shortener.php";

	// get the current link when visited
	$link = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	if ( $LINK_TL_SCRIPT === $link ) {

		$database_obj = new DB_Connection( 'host', 'username', 'password' );

	} else if ( $LINK_LH_SCRIPT === $link ) {

		$database_obj = new DB_Connection( null, null, null );

	} else {

	}

	//make sure the post is parsed and data is gotten
	if(isset($_POST['link']) && !empty($_POST['link'])) {
		$long_link = $_POST['link'];
		
		// check if long_link already exist in the datadase
		$search = "SELECT * FROM urls WHERE long_url = '$long_link'";
		
		$connection = $database_obj->db_connection();
			
		$database_obj->db_select($connection);

		$db_utilities = new DB_Utilities();

		$res = $db_utilities->db_query($search);

		if( !$res ) {
		
			die( "Error running query" . $db_utilities->error() );
			
		}
		
		if( $db_utilities->db_num_rows( $res ) > 0 ){
			
			$results = $db_utilities->db_fetch_row( $res );
			
		    echo json_encode( array( "shortUrl"=> $results[1] ) );
		    
		}else{
		
			$hash = substr( strtolower( preg_replace( '/[0-9_\/]+/','', base64_encode( sha1( $long_link ) ) ) ), 0, 8 );

			if ( $LINK_TL_SCRIPT === $link ) {

				$short_link = $LINK_TL . '/' . $hash;


			} else if ( $LINK_LH_SCRIPT === $link ) {

				$short_link = $LINK_LH . '/' . $hash;

			} else {

			}

			$query = "INSERT INTO urls (id, short_url, long_url) VALUES ('', '$short_link', '$long_link');";

			$res = $db_utilities->db_query($query);

			if(!$res) {
				die("Error running query" . $db_utilities->error());
			}

		    echo json_encode(array("shortUrl"=> $short_link, "hash"=>$hash));
    	}
    }
