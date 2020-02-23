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

$missingParamErr = 10;
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

foreach ($argv as $index => $cmdArg){
    if ($index == 0)
        continue;
    $cmdArgNoEqual = explode('=',$cmdArg);
    switch ($cmdArgNoEqual[0]){
        case "--help":
            echo "USAGE:\n
                  php7.4 parse.php [OPTIONS] 
                  DESCRIPTION:\n
                  Parse.php script is reading the source code in IPPcode20 from stdin and
                  runs the lexical and syntax analyzis. After successful
                  analyzis script prints the XML code to stdout.
                  If there is any lexical or syntax error, script will end
                  with specific error code.\n";
            exit(0);
            break;

?>