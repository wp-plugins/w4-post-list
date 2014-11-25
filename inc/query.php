<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Users_Query extends W4PL_Query
{
	function __construct( $query_args )
	{
		$query_args['table'] = 'users';
		parent::__construct($query_args);
	}

	function query()
	{
		$this->init();
		$this->parse_query_vars();

		if( !empty( $this->errors ))
			return;

		global $wpdb;

		$this->_select = "SELECT";
		$this->_join = " FROM $this->table AS TB";
		$this->_where = " WHERE 1=1";

		if( '' != $this->get( 'column' )){
			$this->_fields .= " TB.". $this->get( 'column' ) ."";
		}
		elseif( '' != $this->get( 'columns' )){
			$this->_fields .= " TB.". implode( ", TB.", $this->get('columns')) ."";
		}
		elseif( $this->get( 'qr' ) == 'count_row' ){
			$this->_fields .= " COUNT(*)";
		}
		else{
			$this->_fields .= " TB.*";
		}

		$this->parse_interger_fields( array(
			'ID__in' => 'TB.ID'
		));
		$this->parse_interger_fields( array(
			'ID__not_in' => 'TB.ID'
		), 'NOT IN');

		$this->parse_search_fields( array(
			'display_name'	=> $this->get('display_name__like'),
			'user_email'	=> $this->get('user_email__like')
		));

		if( '' != $this->get('orderby') )
		{
			$order = $this->get( 'order' );
			$orderby = $this->get( 'orderby' );
			$this->_order .= " ORDER BY $orderby $order";
		}

		if( '' != $this->limit )
		{
			if ( '' == $this->get( 'offset' ))
			{
				$start = ( $this->page - 1 ) * $this->limit . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
			else
			{
				$this->set( 'offset', absint( $this->get( 'offset' )));
				$start = $this->get( 'offset' ) . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
		}
		if( '' != $this->limit ){
			$this->_found_rows = " SQL_CALC_FOUND_ROWS";
		}

		$this->request = $this->_select . $this->_found_rows . $this->_fields . $this->_join . $this->_where . $this->_groupby . $this->_order . $this->_limit;
		$this->request = apply_filters( 'w4pl_query_request', $this->request, $this->query_args );
		#echo $this->request;
		$this->results();
	}

	function get_results()
	{
		// let post format change the result
		return apply_filters( 'get_terms', $this->results, $this->get('taxonomy'), $this->query_args );
	}
}




class W4PL_Terms_Query extends W4PL_Query
{
	function __construct( $query_args )
	{
		$query_args['table'] = 'terms';
		parent::__construct($query_args);
	}

	function query()
	{
		$this->init();
		$this->parse_query_vars();

		if( !empty( $this->errors ))
			return;

		global $wpdb;

		$this->_select = "SELECT";
		$this->_join = " FROM $this->table AS TB";
		$this->_where = " WHERE 1=1";

		if( '' != $this->get( 'column' )){
			$this->_fields .= " TB.". $this->get( 'column' ) ."";
		}
		elseif( '' != $this->get( 'columns' )){
			$this->_fields .= " TB.". implode( ", TB.", $this->get('columns')) ."";
		}
		elseif( $this->get( 'qr' ) == 'count_row' ){
			$this->_fields .= " COUNT(*)";
		}
		else{
			$this->_fields .= " TB.*";
		}

		if( '' != $this->get( 'taxonomy' ) )
		{
			$this->_fields .= ", TT1.*";
			$this->_join .= " INNER JOIN {$wpdb->term_taxonomy} TT1 ON (TT1.term_id = TB.term_id AND TT1.taxonomy = '". $this->get('taxonomy') ."')";
		}

		$count__min = (int) $this->get('count__min');
		if( $count__min > 0 )
		{
			$this->_where .= " AND TT1.count >= $count__min";
		}

		$this->parse_interger_fields( array(
			'term_id__in' 		=> 'TB.term_id',
			'term_parent__in' 	=> 'TT1.parent'
		));
		$this->parse_interger_fields( array(
			'term_id__not_in' => 'TB.term_id'
		), 'NOT IN');

		$this->parse_text_fields( array(
			'name'			=> 'name',
			'slug'			=> 'slug'
		));

		$this->parse_search_fields( array(
			'name'			=> $this->get('name__like'),
			'slug'			=> $this->get('slug__like')
		));

		if( '' != $this->get('orderby') )
		{
			$order = $this->get('order');
			$orderby = $this->get('orderby');

			if( 'term_id__in' == $orderby ){
				$term_id__in = implode( ',', array_map('absint', $this->get('term_id__in') ) );
				$this->_order .= " ORDER BY FIELD( TB.term_id, $term_id__in )";
			}
			else{
				if( 'term_id' == $orderby ){
					$orderby = "TB.{$orderby}";
				}
				$this->_order .= " ORDER BY {$orderby} $order";
			}
		}

		if( '' != $this->limit )
		{
			if ( '' == $this->get( 'offset' ))
			{
				$start = ( $this->page - 1 ) * $this->limit . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
			else
			{
				$this->set( 'offset', absint( $this->get( 'offset' )));
				$start = $this->get( 'offset' ) . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
		}
		if( '' != $this->limit ){
			$this->_found_rows = " SQL_CALC_FOUND_ROWS";
		}

		$this->request = $this->_select . $this->_found_rows . $this->_fields . $this->_join . $this->_where . $this->_groupby . $this->_order . $this->_limit;
		$this->request = apply_filters( 'w4pl_query_request', $this->request, $this->query_args );
		#echo $this->request;
		$this->results();
	}

	function get_results()
	{
		// let post format change the result
		return apply_filters( 'get_terms', $this->results, $this->get('taxonomy'), $this->query_args );
	}
}


/*
 * W4PL Query Class
 * @source code prtected, do not copy or clone
**/


class W4PL_Query
{
	var $query_args;
	var $request;
	var $table;
	var $errors;

	var $results = '';
	var $found_item = 0;
	var $limit = '';
	var $page = 1;
	var $max_num_pages = 1;
	var $qr = 'get_results';


	function __construct( $query_args ){
		$this->query_args = $query_args;
	}
	function set( $key, $val ){
		$this->query_args[$key] = $val;
	}
	function get( $key, $default = '' ){
		return array_key_exists( $key, $this->query_args ) ? $this->query_args[$key] : $default;
	}


	function parse_query_vars()
	{
		global $wpdb;

		if( !is_array( $this->query_args ))
			$this->query_args = array();

		if( '' == $this->get( 'table' )){
			$this->errors[] = "Table Not Defined";
			return;
		}

		$allowed = array('posts', 'terms', 'users');
		if( !in_array( $this->get( 'table' ), $allowed ))
			$this->errors[] = "Quering for table is not allowed.:" . $this->get( 'table' );

		foreach( $allowed as $table ){
			$this->$table = $wpdb->prefix. $table;
		}

		$this->table = $wpdb->prefix . $this->get( 'table' );

		if( '' == $this->get( 'order' ) || !in_array( strtoupper( $this->get('order') ), array('ASC', 'DESC') ) )
			$this->set( 'order', "ASC" );

		$this->set( 'order', strtoupper( $this->get( 'order' )));

		if ( '' == $this->get( 'page' )){
			$this->set( 'page', $this->page );
		}
		else{
			$this->page = $this->get( 'page' ) < 1 ? 1 : $this->get( 'page' );
		}

		if ( '' != $this->get( 'limit' )){
			$this->limit = absint( $this->get( 'limit' ));
		}

		$this->output = $this->get( 'output' ) ? $this->get( 'output' ) : OBJECT;
	}

	function init()
	{
		$this->table = "";
		$this->output =  "";
		$this->_select = "";
		$this->_fields = "";
		$this->_found_rows = "";
		$this->_join = "";
		$this->_where = "";
		$this->_groupby = "";
		$this->_order = "";
		$this->_limit = "";
		$this->_qr = "";
	}


	function query(){}


	function parse_search_fields( $args = array() )
	{
		if( empty($args) )
			return;

		$args = array_filter($args);

		foreach( $args as $column => $term )
		{
			/*
			if( 0 === strrpos($column, '__like') )
				$column = str_replace('__like', '', $column);
			*/

			$search_terms = array();
			preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $term, $matches );
			if( is_array($matches[0]) )
			{
				foreach( $matches[0] as $s )
				{
					$search_terms[] = trim( $s, "\"'\n\r " );
				}
			}
			else
			{
				$search_terms[] = $matches[0];
			}

			$n = '%';
			$searchand = '';
			$search = '';

			foreach( (array) $search_terms as $term ){
				$term = esc_sql( like_escape( $term ));
				$search .= "{$searchand}($column LIKE '{$n}{$term}{$n}')";
				$searchand = ' OR ';
			}

			if( !empty($search) ){
				$this->_where .= " AND ({$search}) ";
			}
		}
	}


	function parse_interger_fields( $args = array(), $compare = 'IN' )
	{
		if( empty($args) )
			return;

		foreach( $args as $request => $column )
		{
			if( is_numeric($request) )
				$request = $column;

			if( '' != $this->get( $request ))
			{
				$var = $this->get( $request );
				if( empty($var) )
					continue;

				if( !is_array($var) && strpos($var, ',') )
					$var = explode(',', $var);
				elseif( is_numeric($var) )
					$var = array($var);

				$this->_where .= " AND {$column} {$compare} (". implode( ',', array_map('intval', $var) ). ")";
			}
		}
	}


	function parse_text_fields( $args = array() )
	{
		if( empty( $args ))
			return;

		foreach( $args as $request => $column )
		{
			if( is_numeric( $request ))
				$request = $column;

			if( '' != $this->get( $request )){
				$var = $this->get( $request );
				if( is_array($var) && !empty($var) )
					$this->_where .= " AND $column IN (\"". implode( '","', array_map('esc_sql', $var) ). "\")";
				else
					$this->_where .= " AND $column = '". esc_sql( $var ) ."'";
			}
		}
	}


	function parse_sortable_fields( $args = array())
	{
		if( empty($args) )
			return;
		foreach( $args as $request => $reset )
		{
			if( $request == $this->get('orderby') )
				$this->set('orderby', $reset);
			if( $request == $this->get('sb') )
				$this->set('sb', $reset);
		}
	}


	function results()
	{
		global $wpdb;

		if( !empty( $this->errors )){
			$error_obj = new WP_Error();
			foreach( $this->errors as $error )
				$error_obj->add( 'error', $error );
			return $error_obj;
		}

		if( !empty( $this->errors ))
			return new WP_Error( 'error', $this->errors );

		if( '' == $this->get('qr') ){
			if( '' != $this->_qr )
				$this->set( 'qr', $this->_qr );
			elseif( '' != $this->get( 'column' ))
				$this->set( 'qr', 'get_col' );
		}

		#$this->set( 'qr', 'get_var' );
		#echo $this->get( 'column' );
		#echo $this->get( 'qr' );
		#echo $this->_qr;

		if( !in_array( $this->get( 'qr' ), array( 'get_row', 'get_var', 'get_col', 'count_row' )))
			$this->set( 'qr', 'get_results' );

		if( $this->get( 'qr' ) == 'get_col' ){
			$result = $wpdb->get_col( $this->request );
		}
		elseif( $this->get( 'qr' ) == 'count_row' || $this->get( 'qr' ) == 'get_var' ){
			$result = $wpdb->get_var( $this->request );
		}
		elseif( $this->get( 'qr' ) == 'get_row' ){
			$result = $wpdb->get_row( $this->request, $this->output );
		}
		else{
			$result = $wpdb->get_results( $this->request, $this->output );
		}

		if( '' != $this->limit ){
			$this->found_item = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$this->max_num_pages = ceil( $this->found_item / $this->limit );
		}
		else{
			$this->found_item = count( $result );
			$this->max_num_pages = 1;
		}

		$this->results = $result;

		return $this->results;
	}

	function get_results(){
		return $this->results;
	}
}
?>