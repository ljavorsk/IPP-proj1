<?php

/*
  * IPP project 2020
  * file: parse.php
  * author: Lukas Javorsky (xjavor20)
  * email: xjavor20@stud.fit.vutbr.cz
*/

$numberOfInstructions = 0;  // --loc
$numberOfComments = 0;  // --comments
$numberOfLabels = 0;    // --labels
$numberOfJumps = 0;     // --jumps

$wrongParamErr = 10;
$inputFileErr = 11;
$outputFileErr = 12;
$wrongHeaderErr = 21;
$wrongOpCodeErr = 22;
$lexOrSyntaxErr = 23;
$internalErr = 99;

$instructions = array (
    //0 args                            
    "CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK",

    //1 arg
    "DEFVAR", "CALL", "PUSHS", "POPS", "WRITE", "LABEL", "JUMP", "EXIT", "DPRINT",

    //2 args
    "MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT",

    //3 args
    "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", "CONCAT",
    "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

$uniqLabels = array();

    class calls {
        function err_msg($message, $rc){
            fwrite(STDERR, "Error: $message");
            exit($rc);
        }
    }

    $stats = false;
    $longopts = array(
      "stats:",
      "loc",
      "comments",
      "loc",
      "jumps",
      "help"
    );

    // Checking validity of every argument
    foreach ($argv as $arg){
        if (preg_match( '/--stats=.+/', $arg) );
        else if (preg_match( '/--help$/', $arg) );
        else if (preg_match( '/--comments$/', $arg) );
        else if (preg_match( '/--loc$/', $arg) );
        else if (preg_match( '/--labels$/', $arg) );
        else if (preg_match( '/--jumps$/', $arg) );
        else if (preg_match( '/parse.php/', $arg));
        else {
            calls::err_msg("Unknown option\n", $wrongParamErr);
        }
    }

    $my_args = getopt("", $longopts);
    if (array_search("--help", $argv)){
        if (count($argv) == 2){
            echo "USAGE: php7.4 parse.php [OPTIONS]\n\n";
            echo "DESCRIPTION:    ";
            echo "Parse.php script is reading the source code in IPPcode20 from stdin and
                runs the lexical and syntax analyzis. After successful
                analyzis script prints the XML code to stdout.
                If there is any lexical or syntax error, script will end
                with specific error code.\n";
            echo "OPTIONS:\n";
            echo "      --help  Prints short help for this script\n";
            echo "      --stats=file [TYPE]    Shows stats of specific types\n";
            echo "                             If the type is not filled, file will be left blank\n";
            echo "TYPES:\n";
            echo "      --loc   TODO\n";
            echo "      --comments   TODO\n";
            echo "      --labels   TODO\n";
            echo "      --jumps   TODO\n";
            exit(0);
        }
        else {
            return calls::err_msg("--help cannot take any more arguments.\n", $wrongParamErr);
        }
    }

    if (count($argv) >= 2 && count($argv) < 7){
        if (preg_grep('/--stats=.*/', $argv)){
            $stats = true;
            $file = $my_args["stats"];
            $loc_order = 0;
            $comments_order = 0;
            $labels_order = 0;
            $jumps_order = 0;

            if (array_search("--loc", $argv)){
                $temp = array_keys($my_args);
                $loc_pos = array_search("loc", $temp);
            }
            if (array_search("--comments", $argv)){
                $temp = array_keys($my_args);
                $comments_order = array_search("loc", $temp);
            }
            if (array_search("--labels", $argv)){
                $temp = array_keys($my_args);
                $labels_order = array_search("loc", $temp);
            }
            if (array_search("--jumps", $argv)){
                $temp = array_keys($my_args);
                $jumps_order = array_search("loc", $temp);
            }

            $statFile = fopen($file, "w");
            if(!$statFile){
                calls::err_msg("File for statistics cannot be opened\n", $outputFileErr);
            }
        }
        else {
            calls::err_msg("You're trying to specify stats without '--stats' option\n", $wrongParamErr);
        }
    }
    else {
        calls::err_msg("Too many arguments\n", $wrongParamErr);
    }

    $input = fopen("php://stdin","r");
    if ($input === false){
        calls::err_msg("Failed to read from stdin\n", $inputFileErr);
    }

    // Header check
    $currentLine = (fgets($input)); // Reads first line from STDIN
    $currentLine = trim($currentLine); // Trims whitespaces from beginning and the end of the line
    $commentLine = explode('#', $currentLine);

    if (sizeof($commentLine)) // Checks for comments
        $numberOfComments++;

    $header = strtoupper(trim($commentLine[0]));
    if (strcmp($header,".IPPCODE20") != 0) {    // Verify the header
        calls::err_msg("Wrong header on input\n", $wrongHeaderErr);
    }

    // XML header
    $xmlWrite = xmlwriter_open_memory();
    xmlwriter_set_indent($xmlWrite, 1);
    $res = xmlwriter_set_indent_string($xmlWrite, '   ');

    xmlwriter_start_document($xmlWrite, '1.0', 'UTF-8');

    // Program
    xmlwriter_start_element($xmlWrite, 'program');

    // Attributes
    xmlwriter_start_attribute($xmlWrite, 'language');
    xmlwriter_text($xmlWrite, 'IPPcode20');
    xmlwriter_end_attribute($xmlWrite);


    // End of the XML document
    xmlwriter_end_element($xmlWrite);
    xmlwriter_end_document($xmlWrite);
    echo xmlwriter_output_memory($xmlWrite);
?>