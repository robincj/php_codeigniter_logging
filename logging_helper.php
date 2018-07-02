<?php

/**
 * Handy logging wrapper functions
 *
 * Add as a helper to the CodeIgniter application/config/autoload.php e.g.
 * $autoload['helper'] = array('url', 'form', 'cookie', 'array', 'string', 'logging');
 *
 * Writes error message to log.
 * If arg 2 is true then current portal user id is prepended to message. Default is true.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @param bool $loguser
 * @return string
 */
function logerror($msg, $loguser = true) {
	return log_message_f ( 'error', $msg, 1, $loguser );
}
/**
 * Builds an error message from the Throwable (Error, Exception, ErrorException etc.) and writes the error message to the log.
 * Additional message can be supplied as arg 2 and this will be prepended to the exceptin message.
 * If arg 3 is true then current portal user id is prepended to message. Default is true.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @param bool $loguser
 * @return string
 */
function log_exception(Throwable $exception, $msg = '', $loguser = true) {
	$msg = $msg . " - " . $exception->__toString ();
	return logerror ( $msg, $loguser );
}
/**
 * Writes debug message to log.
 * If arg 2 is true then current portal user id is prepended to message. Default is true.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @param bool $loguser
 * @return string
 */
function logdebug($msg, $loguser = false) {
	return log_message_f ( 'debug', $msg, 1, $loguser );
}
/**
 * Writes info message to log.
 * If arg 2 is true then current portal user id is prepended to message. Default is false.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @param bool $loguser
 * @return string
 */
function loginfo($msg, $loguser = true) {
	return log_message_f ( 'info', $msg, 1, $loguser );
}

/**
 * Writes info message to log, with WARNING prepended to the message.
 * If arg 2 is true then current portal user id is prepended to message. Default is false.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @param bool $loguser
 * @return string
 */
function logwarn($msg, $loguser = true) {
	return log_message_f ( 'info', "WARNING " . $msg, 1, $loguser );
}
/**
 * Writes currently logged in user id and username to log.
 * Message can be optionally added as arg.
 * Message is automatically prepended with caller class::function name
 *
 * @param string $msg
 * @return string
 */
function loguser($msg = '') {
	$msg = "USERNAME:" . get_instance ()->session->userdata ( 'username' ) . " $msg";
	return log_message_f ( 'info', $msg, 1, 1 );
}
/**
 *
 * @param string $lvl
 * @param string $msg
 * @param number $back
 * @param string $loguser
 * @return string The logged message
 */
function log_message_f($lvl = 'debug', $msg = '', $back = 0, $loguser = false) {
	if (is_log_level_active ( $lvl )) {
		if ($loguser)
			$msg = "UID:" . get_instance ()->session->userdata ( 'id' ) . " $msg";
		$msg = log_indent () . myclassfname ( ++ $back ) . " " . $msg;
		log_message ( $lvl, $msg );
		return $msg;
	}
}
/**
 * Writes a print_r() output of the given array to the log.
 * If an object is provided rather than an array then it will just log the object's properties (get_object_vars()), not methods.
 *
 * @param array $array
 * @param string $msg
 * @param string $lvl
 */
function log_print_r($array, $msg = '', $lvl = 'debug') {
	if (is_log_level_active ( $lvl )) {
		if (is_object ( $array ))
			$array = get_object_vars ( $array );
		log_message_f ( $lvl, "$msg - print_r:  " . print_r ( $array, 1 ), 1 );
	}
}
/**
 *
 * @param array $array
 * @param string $msg
 * @param string $lvl
 */
function log_implode($array, $msg = '', $lvl = 'debug') {
	if (is_log_level_active ( $lvl ))
		log_message_f ( $lvl, "$msg - array:  " . flatten_to_string ( ', ', $array ), 1 );
}
/**
 * Logs the name and value of a given variable name
 * varname must be a string of the variable's name e.g.
 * log_var('myvar');
 * Not actually much use unless the variable is in the scope of this function (which means you have to include this file within the scope of the variable!)
 *
 * @param string $varname
 */
function log_var($varname, $msg = '', $lvl = 'debug') {
	if (is_log_level_active ( $lvl )) {
		return false;
		if ($msg)
			log_message_f ( $lvl, $msg, 1 );
		log_message_f ( $lvl, "Variable '$varname' has value: " . ((is_array ( $$varname ) || is_object ( $$varname )) ? print_r ( get_object_vars ( $$varname ), 1 ) : $$varname), 1 );
	}
}
/**
 * Log the start of a function.
 * Put log_startf() at the start of a function/method to record a START line in the logs.
 * If arg 1 is true then the function args will be dumped using print_r().
 * Arg 2 can be a message to record in the log immediately after the START info.
 * Arg 3 can be the logging level (debug, info, error), default is debug
 *
 * @param string $dump_logargs
 * @param string $msg
 * @param string $lvl
 */
function log_startf($dump_logargs = false, $msg = '', $lvl = 'debug') {
	if (is_log_level_active ( $lvl )) {
		$args = $caller = '';
		$backtrace = debug_backtrace ( 0, 2 ) [1];
		if ($dump_logargs) {
			$caller = "  : " . $backtrace ['file'] . " (" . $backtrace ['line'] . ") ";
			$args = $backtrace ['args'];
			$args = is_multidim ( $args ) ? print_r ( $args, 1 ) : "'" . flatten_to_string ( "', '", $args ) . "'";
		}
		log_message ( $lvl, log_indent () . "START: " . myclassfname ( $backtrace ) . "($args)$caller" );
		if ($msg)
			log_message_f ( $lvl, $msg, 1 );
	}
}
/**
 * Log the end of a function.
 * Put log_endf() at the end of a function/method to record an END line in the logs.
 * If arg 1 is true then the function results will be dumped using print_r().
 * Arg 2 can be a message to record in the log immediately before the END info.
 * Arg 3 can be the logging level (debug, info, error), default is debug
 *
 * @param string $dump_logargs
 * @param string $msg
 * @param string $lvl
 */
function log_endf($results = NULL, $msg = '', $lvl = 'debug') {
	$ret = $results;
	if (is_log_level_active ( $lvl )) {
		$resultmsg = '';
		if ($results !== NULL) {
			if (is_object ( $results ))
				$results = print_r ( get_object_vars ( $results ), 1 );
			elseif (is_array ( $results ))
				$resultmsg = (! is_multidim ( $results ) && ! is_assoc ( $results )) ? "'" . implode ( "', '", $results ) . "'" : print_r ( $results, 1 );
			else
				$resultmsg = $results;
		}
		log_message ( $lvl, log_indent () . "END: " . myclassfname ( 1 ) . "() $msg $resultmsg" );
	}
	return $ret;
}
/**
 * Returns the function/method name of the calling function.
 * Arg sets the number of function call levels to backtrace to to get the name from or the backtrace sub-array you want to use.
 *
 * @param number|array $back
 * @return string
 */
function myfname($back = 0) {
	$trace = $back;
	if (! is_array ( $back )) {
		++ $back;
		$trace = debug_backtrace ( 2, $back + 1 ) [$back];
	}
	return $trace ['function'];
}
/**
 * Returns the class name of the calling function.
 * Arg sets the number of function call levels to backtrace to to get the name from.
 *
 * @param number $back
 * @return string
 */
function myclassname($back = 0) {
	$trace = $back;
	if (! is_array ( $back )) {
		++ $back;
		$trace = debug_backtrace ( 2, $back + 1 ) [$back];
	}
	return array_key_exists ( 'class', $trace ) ? $trace ['class'] : '';
}
/**
 * Returns the class name and function name of the calling function.
 * Returned string is formatted with a double colon separating the two values, like "classname::functionname"
 * Arg sets the number of function call levels to backtrace to to get the names from, or the backtrace sub-array you want to use (avoids multiple calls to debug_backtrace()).
 *
 * @param number|array $back
 * @return string
 */
function myclassfname($back = 0) {
	// return myclassname ( ++ $back ) . "::" . myfname ( $back );
	$trace = $back;
	if (! is_array ( $back )) {
		++ $back;
		$trace = debug_backtrace ( 2, $back + 1 ) [$back];
	}
	$class = array_key_exists ( 'class', $trace ) ? $trace ['class'] : '';
	$type = array_key_exists ( 'type', $trace ) ? $trace ['type'] : ' ';
	$func = $trace ['function'];
	return $class . $type . $func;
}
/**
 * Logs the calling class::function name and it's caller class::function name.
 *
 * @param string $msg
 *        	Optionally, if no arg 2, then the level can be given as the first arg instead of a message.
 * @param string $lvl
 *        	logging level, default is debug.
 * @return string Returns the message that is written to the log, if one is written for the current logging level.
 *         Function name in returned string is formatted with a double colon separating the two values, like "classname::functionname"
 */
function log_callerf($msg = '', $lvl = 'debug') {
	// see if first arg is the level rather than a message
	if (! $lvl && log_level_number ( $msg ) !== NULL) {
		$lvl = $msg;
		$msg = '';
	}
	if (is_log_level_active ( $lvl )) {
		$msg = log_indent () . myclassfname ( 1 ) . " was called by " . myclassfname ( 2 ) . "() $msg";
		log_message ( $lvl, $msg );
		return $msg;
	}
}
/**
 * Logs a formatted stack trace of all calling functions, generated using PHP debug_backtrace().
 *
 * @param string $msg
 *        	Optionally, if no arg 2, then the level can be given as the first arg instead of a message.
 * @param string $lvl
 *        	logging level, default is debug.
 * @return string[] Returns an array of these function names.
 *         Returned function names are formatted with a double colon separating the two values, like "classname::functionname"
 */
function log_backtracef($msg = '', $lvl = 'debug') {
	// see if first arg is the level rather than a message
	if (! $lvl && log_level_number ( $msg ) !== NULL) {
		$lvl = $msg;
		$msg = '';
	}
	// Line is the line number in the file that the function was called FROM
	if (is_log_level_active ( $lvl )) {
		$trace = debug_backtrace ( 2 );
		array_shift ( $trace );
		$funcs = array ();
		foreach ( $trace as $x ) {
			$class = element ( 'class', $x, '' );
			$function = array_key_exists ( 'function', $x ) ? $x ['function'] : '[unknown function]';
			$type = element ( 'type', $x, ' ' );
			$funcs [] = $class . $type . $function . " (" . $x ['file'] . " - " . $x ['line'] . ")";
		}
		log_message_f ( $lvl, "Backtrace: " . PHP_EOL . "   " . implode ( "," . PHP_EOL . "   ", $funcs ), 1 ) . " $msg";
		return $funcs;
	}
}
/**
 * Logs the last database query used by the supplied Codeigniter database object.
 *
 * @param object $dbobj
 */
function log_last_query($dbobj, $msg = '', $lvl = 'debug') {
	if (is_log_level_active ( $lvl )) {
		return log_message_f ( $lvl, "$msg - SQL QUERY: " . $dbobj->last_query (), 1 );
	}
}
/**
 * Log elapsed execution time and max_execution_time
 *
 * @param string $msg
 *        	Optionally, if no arg 2, then the level can be given as the first arg instead of a message.
 * @param string $lvl
 *        	logging level, default is debug.
 * @return string The logged message
 */
function log_execution_time($msg = '', $lvl = 'debug') {
	// see if first arg is the level rather than a message
	if (! $lvl && log_level_number ( $msg ) !== NULL) {
		$lvl = $msg;
		$msg = '';
	}
	if (is_log_level_active ( $lvl )) {
		$elapsed = microtime ( TRUE ) - $_SERVER ['REQUEST_TIME_FLOAT'];
		$msg = "Elapsed execution time: $elapsed, max_execution_time: " . ini_get ( 'max_execution_time' ) . ".  $msg";
		return log_message_f ( $lvl, "$msg", 1 );
	}
}
/**
 * Given a log level name, return the number
 * | 0 = Disables logging, Error logging TURNED OFF
 * | 1 = Error Messages (including PHP errors)
 * | 2 = Debug Messages
 * | 3 = Informational Messages
 * | 4 = All Messages
 */
function log_level_number($lvl) {
	if (is_numeric ( $lvl ))
		return $lvl;
	$levels = array (
			'off' => 0,
			'error' => 1,
			'debug' => 2,
			'info' => 3,
			'all' => 4 
	);
	return array_key_exists ( $lvl, $levels ) ? $levels [$lvl] : NULL;
}
/**
 * Returns a string space characters equalling in number the number of function calls listed in debug_backtrace
 *
 * @param string $char
 * @param int $mod
 * @return string
 */
function log_indent($char = ' ', $mod = -2) {
	return str_repeat ( $char, count ( debug_backtrace () ) + $mod );
}
/**
 *
 * @param int|string $lvl
 * @return boolean
 */
function is_log_level_active($lvl) {
	if (! array_key_exists ( 'log_threshold', $GLOBALS ))
		$GLOBALS ['log_threshold'] = get_instance ()->config->item ( 'log_threshold' );
	return (log_level_number ( $lvl ) <= $GLOBALS ['log_threshold']);
}
/**
 * For quick crude debugging - dump (print_r()) the content of the arguments to the screen, wrapped in a pre tag.
 *
 * @param array $a
 */
function dump(...$a) {
	echo "<pre>";
	echo myclassfname ( 2 ) . " called by " . myclassfname ( 3 ) . "()" . PHP_EOL;
	foreach ( $a as $arg ) {
		print_r ( $arg );
		echo PHP_EOL . "------" . PHP_EOL;
	}
	echo "</pre>";
}
/**
 * For quick crude debugging - dump (print_r()) the content of the arguments to the screen, wrapped in a pre tag, and exit.
 *
 * @param array $a
 */
function dumpx(...$a) {
	dump ( ...$a );
	exit ();
}
