<?php	
	function hermit_install(){
		global $wpdb, $hermit_table_name;

		if( $wpdb->get_var("show tables like '{$hermit_table_name}'") != $hermit_table_name ) {
			$wpdb->query("CREATE TABLE {$hermit_table_name} (
				id          INT(10) NOT NULL AUTO_INCREMENT,
				song_name   VARCHAR(255) NOT NULL,
				song_author VARCHAR(255) NOT NULL,
				song_url    TEXT NOT NULL,
				created     DATETIME NOT NULL,
				UNIQUE KEY id (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
		}
	}

	function hermit_uninstall(){
		global $wpdb, $hermit_table_name;

		$wpdb->query("DROP TABLE IF EXISTS {$hermit_table_name}");
	}