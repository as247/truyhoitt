<?php
define('EZSQL_VERSION', 'CM1.0');

/**
 * @since 0.71
 */
define('OBJECT', 'OBJECT', true);

/**
 * @since {@internal Version Unknown}}
 */
define('OBJECT_K', 'OBJECT_K', false);

/**
 * @since 0.71
 */
define('ARRAY_A', 'ARRAY_A', false);

/**
 * @since 0.71
 */
define('ARRAY_N', 'ARRAY_N', false);
class cdb {
	var $num_queries = 0;
	var $charset;
	var $prefix;
	var $real_escape=false;
	var $last_query='';
	var $tables=array('laptop','pc','printer','product');
	function cmdb($dbuser, $dbpassword, $dbname, $dbhost) {
		return $this->__construct($dbuser, $dbpassword, $dbname, $dbhost);
	}
	function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
		register_shutdown_function(array(&$this, "__destruct"));
		if ( defined('DB_CHARSET') )
			$this->charset = DB_CHARSET;
			
		$this->db = @mysql_connect($dbhost, $dbuser, $dbpassword, true);
		if (!$this->db) {
			$this->_die(sprintf('
<h1>Không thể kết nối với cơ sở dữ liệu</h1>
<p>Có thể tên đăng nhập hoặc mật khẩu trong tập tin <code>c-config.php</code> không chính xác hoặc không thể kết nối đến máy chủ cơ sở dữ liệu tại <code>%s</code>. Điều này có thể do máy chủ cơ sở dữ liệu đang không chạy.</p>
<ul>
	<li>Hãy kiểm tra tên đăng nhập và mật khẩu!</li>
	<li>Hãy kiểm tra tên máy chủ!</li>
	<li>Hãy kiểm tra xem máy chủ cơ sở dữ liệu có đang chạy hay không!</li>
</ul>
<p>Nếu bạn vẫn không giải quyết được vấn đề này hãy liên hệ với người quản lý máy chủ.</p>
', $dbhost));
			return;
		}
	
		if ( !empty($this->charset) ) {
			if ( function_exists('mysql_set_charset') ) {
				mysql_set_charset($this->charset, $this->db);
			}
		}
		

		$this->select($dbname);
	}
	function __destruct() {
		return true;
	}
	function set_prefix($prefix) {

		if ( preg_match('|[^a-z0-9_]|i', $prefix) )
			$this->_die('Invalid database prefix');

		$old_prefix = $this->prefix;
		$this->prefix = $prefix;

		foreach ( (array) $this->tables as $table )
			$this->$table = $this->prefix . $table;

		if ( defined('CUSTOM_USER_TABLE') )
			$this->users = CUSTOM_USER_TABLE;


		return $old_prefix;
	}
	function select($db) {
		if (!@mysql_select_db($db, $this->db)) {
			$this->ready = false;
			$this->_die(sprintf('
<h1>Không thể chọn được cơ sở dữ liệu</h1>
<p>Đã kết nối tới máy chủ cơ sở dữ liệu (tên đăng nhập và mật khẩu đã cho là đúng) nhưng không thể chọn được cơ sở dữ liệu <code>%1$s</code>.</p>
<ul>
<li>Bạn có chắc cơ sở dữ liệu này tồn tại?</li>
<li>Tên đăng nhập <code>%2$s</code> có quyền sử dụng cơ sở dữ liệu <code>%1$s</code> không?</li>
<li>Trên một số hệ thống, tên của cơ sở dữ liệu được bắt đầu với tên đăng nhập, cơ sở dữ liệu của bạn có thể là <code>tên-đăng-nhập_%1$s</code>. Liệu đây có phải là trường hợp của bạn?</li>
</ul>
<p>Nếu bạn không biết cách cài đặt cơ sở dữ liệu, bạn hãy <strong>liên lạc với người quản lý máy chủ</strong>.</p>', $db, DB_USER));
			return;
		}
	}
	function _weak_escape($string) {
		return addslashes($string);
	}

	function _real_escape($string) {
		if ( $this->dbh && $this->real_escape )
			return mysql_real_escape_string( $string, $this->dbh );
		else
			return addslashes( $string );
	}

	function _escape($data) {
		if ( is_array($data) ) {
			foreach ( (array) $data as $k => $v ) {
				if ( is_array($v) )
					$data[$k] = $this->_escape( $v );
				else
					$data[$k] = $this->_real_escape( $v );
			}
		} else {
			$data = $this->_real_escape( $data );
		}

		return $data;
	}

	/**
	 * Escapes content for insertion into the database using addslashes(), for security
	 *
	 * @since 0.71
	 *
	 * @param string|array $data
	 * @return string query safe string
	 */
	function escape($data) {
		if ( is_array($data) ) {
			foreach ( (array) $data as $k => $v ) {
				if ( is_array($v) )
					$data[$k] = $this->escape( $v );
				else
					$data[$k] = $this->_weak_escape( $v );
			}
		} else {
			$data = $this->_weak_escape( $data );
		}

		return $data;
	}

	/**
	 * Escapes content by reference for insertion into the database, for security
	 *
	 * @since 2.3.0
	 *
	 * @param string $s
	 */
	function escape_by_ref(&$string) {
		$string = $this->_real_escape( $string );
	}
	function prepare($query = null) { // ( $query, *$args )
		if ( is_null( $query ) )
			return;
		$args = func_get_args();
		array_shift($args);
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset($args[0]) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
		$query = str_replace('"%s"', '%s', $query); // doublequote unquoting
		$query = str_replace('%s', "'%s'", $query); // quote the strings
		array_walk($args, array(&$this, 'escape_by_ref'));
		return @vsprintf($query, $args);
	}
	function query($query) {
        $return_val = 0;
		$this->flush();

		// Log how the function was called
		$this->func_call = "\$db->query(\"$query\")";

		// Keep track of the last query for debug..
		$this->last_query = $query;
		$this->result = @mysql_query($query, $this->db);
		++$this->num_queries;

		if ( preg_match("/^\\s*(insert|delete|update|replace|alter) /i",$query) ) {
			$this->rows_affected = mysql_affected_rows($this->db);
			// Take note of the insert_id
			if ( preg_match("/^\\s*(insert|replace) /i",$query) ) {
				$this->insert_id = mysql_insert_id($this->db);
			}
			// Return number of rows affected
			$return_val = $this->rows_affected;
		} else {
			$i = 0;
			while ($i < @mysql_num_fields($this->result)) {
				$this->col_info[$i] = @mysql_fetch_field($this->result);
				$i++;
			}
			$num_rows = 0;
			while ( $row = @mysql_fetch_object($this->result) ) {
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			@mysql_free_result($this->result);

			// Log number of rows the query returned
			$this->num_rows = $num_rows;

			// Return number of rows selected
			$return_val = $this->num_rows;
		}

		return $return_val;
	}
	function insert($table, $data, $format = null) {
		$formats = $format = (array) $format;
		$fields = array_keys($data);
		$formatted_fields = array();
		foreach ( $fields as $field ) {
			if ( !empty($format) )
				$form = ( $form = array_shift($formats) ) ? $form : $format[0];
			elseif ( isset($this->field_types[$field]) )
				$form = $this->field_types[$field];
			else
				$form = '%s';
			$formatted_fields[] = $form;
		}
		$sql = "INSERT INTO `$table` (`" . implode( '`,`', $fields ) . "`) VALUES ('" . implode( "','", $formatted_fields ) . "')";
		return $this->query( $this->prepare( $sql, $data) );
	}
	function update($table, $data, $where, $format = null, $where_format = null) {
		if ( !is_array( $where ) )
			return false;

		$formats = $format = (array) $format;
		$bits = $wheres = array();
		foreach ( (array) array_keys($data) as $field ) {
			if ( !empty($format) )
				$form = ( $form = array_shift($formats) ) ? $form : $format[0];
			elseif ( isset($this->field_types[$field]) )
				$form = $this->field_types[$field];
			else
				$form = '%s';
			$bits[] = "`$field` = {$form}";
		}

		$where_formats = $where_format = (array) $where_format;
		foreach ( (array) array_keys($where) as $field ) {
			if ( !empty($where_format) )
				$form = ( $form = array_shift($where_formats) ) ? $form : $where_format[0];
			elseif ( isset($this->field_types[$field]) )
				$form = $this->field_types[$field];
			else
				$form = '%s';
			$wheres[] = "`$field` = {$form}";
		}

		$sql = "UPDATE `$table` SET " . implode( ', ', $bits ) . ' WHERE ' . implode( ' AND ', $wheres );
		return $this->query( $this->prepare( $sql, array_merge(array_values($data), array_values($where))) );
	}
	function get_row($query = null, $output = OBJECT, $y = 0) {
		$this->func_call = "\$db->get_row(\"$query\",$output,$y)";
		if ( $query )
			$this->query($query);
		else
			return null;

		if ( !isset($this->last_result[$y]) )
			return null;

		if ( $output == OBJECT ) {
			return $this->last_result[$y] ? $this->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : null;
		} elseif ( $output == ARRAY_N ) {
			return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : null;
		} else {
			$this->print_error(" \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
		}
	}
	function get_col($query = null , $x = 0) {
		if ( $query )
			$this->query($query);

		$new_array = array();
		// Extract the column values
		for ( $i=0; $i < count($this->last_result); $i++ ) {
			$new_array[$i] = $this->get_var(null, $x, $i);
		}
		return $new_array;
	}
	function get_var($query=null, $x = 0, $y = 0) {
		$this->func_call = "\$db->get_var(\"$query\",$x,$y)";
		if ( $query )
			$this->query($query);

		// Extract var out of cached results based x,y vals
		if ( !empty( $this->last_result[$y] ) ) {
			$values = array_values(get_object_vars($this->last_result[$y]));
		}

		// If there is a value return it else return null
		return (isset($values[$x]) && $values[$x]!=='') ? $values[$x] : null;
	}
	function get_results($query = null, $output = OBJECT) {
		$this->func_call = "\$db->get_results(\"$query\", $output)";

		if ( $query )
			$this->query($query);
		else
			return null;

		if ( $output == OBJECT ) {
			// Return an integer-keyed array of row objects
			return $this->last_result;
		} elseif ( $output == OBJECT_K ) {
			// Return an array of row objects with keys from column 1
			// (Duplicates are discarded)
			foreach ( $this->last_result as $row ) {
				$key = array_shift( get_object_vars( $row ) );
				if ( !isset( $new_array[ $key ] ) )
					$new_array[ $key ] = $row;
			}
			return $new_array;
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			// Return an integer-keyed array of...
			if ( $this->last_result ) {
				$i = 0;
				foreach( (array) $this->last_result as $row ) {
					if ( $output == ARRAY_N ) {
						// ...integer-keyed row arrays
						$new_array[$i] = array_values( get_object_vars( $row ) );
					} else {
						// ...column name-keyed row arrays
						$new_array[$i] = get_object_vars( $row );
					}
					++$i;
				}
				return $new_array;
			}
		}
	}
	function _die($message){
		c_exit($message);
	}
	function flush() {
		$this->last_result = array();
		$this->col_info = null;
		$this->last_query = null;
	}
	
	
}

if ( ! isset($cdb) ) {
	$cdb = new cdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}
?>
