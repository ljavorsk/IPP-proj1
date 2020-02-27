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

$uniqueLabels = array();     // Array for unique labels for bonus solution

    /**
     * Prints the error message on stderr
     * @param $message: error message
     * @param $rc: exit code
     */
    function err_msg($message, $rc){
        fwrite(STDERR, "Error: $message\n");
        exit($rc);
    }

    /**
     * Checks if the variable have correct form
     * @param $variable: var value
     * @return: true if succeeded, false if fails
     */
    function checkVar($variable){
        if (preg_match('/^[L,T,G]F@[a-zA-Z_\-\$&%*!?][0-9a-zA-Z_\-\$&%*!?]*$/',$variable))
            return true;
        else
            return false;
    }

    /**
     * Checks if the constant have correct form
     * @param $constant: const value
     * @return: true if succeeded, false if fails
     */
    function checkConst($constant){
        if (preg_match('/^nil@nil$/', $constant));
        else if (preg_match('/^bool@(true|false)$/', $constant));
        else if (preg_match('/^int@\S+$/', $constant));
        else if (preg_match('/^(string@([^\\\\\#\s]|(\\\\\d{3}))*)$/', $constant));
        else{
            return false;
        }
        return true;
    }

    /**
     * Checks if the label have correct form
     * @param $label: label value
     * @return: true if succeeded, false if fails
     */
    function checkLabel($label){
        if (preg_match('/^[a-zA-Z_\-\$&%*!?][0-9a-zA-Z_\-\$&%*!?]*$/',$label))
            return true;
        else
            return false;
    }

    /**
     * Checks if the type have correct form
     * @param $type: type value
     * @return: true if succeeded, false if fails
     */
    function checkType($type){
        if (preg_match('/^(int|string|bool)$/',$type))
            return true;
        else
            return false;
    }

    /**
     * Decides if the error is wrong opcode or syntax
     * @param $instruction: instruction
     * @param $unknownOpCodetErr: error message
     * @param $instArgErr: error message
     */
    function decideSyntaxOrWrongOpcodeErr($instruction, $unknownOpCodetErr, $instArgErr){
        global $instructions, $wrongOpCodeErr, $lexOrSyntaxErr;
        if (!in_array($instruction, $instructions))
            err_msg($unknownOpCodetErr, $wrongOpCodeErr);
        else
            err_msg($instArgErr, $lexOrSyntaxErr);
    }

    /**
     * Creates xml format for argument
     * @param $argOrder: order of argument
     * @param $argType: type of argument
     * @param $argValue: value of argument
     */
    function xmlCreateArgument($argOrder,$argType,$argValue){
        global $xmlWrite;

        xmlwriter_start_element($xmlWrite, "arg$argOrder");
        xmlwriter_start_attribute($xmlWrite, 'type');
        xmlwriter_text($xmlWrite, "$argType");
        xmlwriter_end_attribute($xmlWrite);
        xmlwriter_text($xmlWrite, "$argValue");
        xmlwriter_end_element($xmlWrite);
    }

    /**
     * Analyzing the instruction on the line and creates it's xml format if everything passes
     * @param $lineCodeArray: array of strings on current line
     * @param $instruction: opcode of instruction
     */
    function whatInstruction($lineCodeArray, $instruction){
        global $numberOfInstructions, $numberOfLabels, $numberOfJumps, $uniqueLabels, $lexOrSyntaxErr, $wrongOpCodeErr, $xmlWrite;

        $instArgErr = "Number of arguments or argument syntax error.
        (Instruction number: $numberOfInstructions, instruction name: $instruction).";

        $unknownOpCodetErr = "Unknown operation code.
        (Instruction number: $numberOfInstructions, instruction name: $instruction";

        switch(sizeof($lineCodeArray)){
            case 1:     // No argument instruction
                switch($instruction){
                    case "CREATEFRAME":
                    case "PUSHFRAME":
                    case "POPFRAME":
                    case "BREAK":
                        break;  // No action needed
                    case "RETURN":
                        $numberOfJumps++;   // Bonus
                        break;
                    default:
                        decideSyntaxOrWrongOpcodeErr($instruction, $unknownOpCodetErr, $instArgErr);
                }
                break;
            case 2:     // One argument
                switch($instruction){
                    case "DEFVAR":  // No action needed
                    case "POPS":    // <var>
                        if (!checkVar($lineCodeArray[1]))   // Not a variable
                            err_msg($instArgErr, $lexOrSyntaxErr);

                        xmlCreateArgument(1,"var",$lineCodeArray[1]);
                        break;
                    case "PUSHS":
                    case "WRITE":
                    case "EXIT":
                    case "DPRINT":
                        if (!checkConst($lineCodeArray[1])){
                            if (!checkVar($lineCodeArray[1]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(1,"var",$lineCodeArray[1]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[1]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            xmlCreateArgument(1,$typeOfArg,$valueOfArg);
                        }
                        break;
                    case "LABEL":
                        if (!checkLabel($lineCodeArray[1]))
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else {
                            if (!in_array($lineCodeArray[1], $uniqueLabels))  // Was the label counted already?
                            {
                                $uniqueLabels[] = $lineCodeArray[1];  // This label is counted
                                $numberOfLabels++;
                            }
                            xmlCreateArgument(1,"label",$lineCodeArray[1]);
                        }
                        break;
                    case "CALL":
                    case "JUMP":
                        if (!checkLabel($lineCodeArray[1]))
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else {
                            $numberOfJumps++;   // Bonus
                            xmlCreateArgument(1,"label",$lineCodeArray[1]);
                        }
                        break;
                    default:
                        decideSyntaxOrWrongOpcodeErr($instruction, $unknownOpCodetErr, $instArgErr);
                        
                }
                break;
            case 3:     // Two arguments
                switch($instruction){
                    case "MOVE":
                    case "INT2CHAR":
                    case "STRLEN":
                    case "TYPE":
                    case "NOT":
                        if (!checkVar($lineCodeArray[1]))
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else
                            xmlCreateArgument(1,"var",$lineCodeArray[1]);
                        
                        if (!checkConst($lineCodeArray[2])){
                            if (!checkVar($lineCodeArray[2]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(2,"var",$lineCodeArray[2]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[2]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            xmlCreateArgument(2,$typeOfArg,$valueOfArg);
                        }
                        break;
                    case "READ":
                        if (!checkVar($lineCodeArray[1]))
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else
                            xmlCreateArgument(1,"var",$lineCodeArray[1]);
                        
                        if (!checkType($lineCodeArray[2]))
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else
                            xmlCreateArgument(2,"type",$lineCodeArray[2]);
                        
                        break;
                    default:
                        decideSyntaxOrWrongOpcodeErr($instruction, $unknownOpCodetErr, $instArgErr);
                }
                break;
            case 4:     // Three arguments
                switch ($instruction){
                    case "ADD":
                    case "SUB":
                    case "MUL":
                    case "IDIV":
                    case "LT":
                    case "GT":
                    case "EQ":
                    case "AND":
                    case "OR":
                    case "STRI2INT":
                    case "CONCAT":
                    case "GETCHAR":
                    case "SETCHAR":
                        if (!checkVar($lineCodeArray[1]))       // First arg
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else
                            xmlCreateArgument(1,"var",$lineCodeArray[1]);

                        if (!checkConst($lineCodeArray[2])){    // Second arg
                            if (!checkVar($lineCodeArray[2]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(2,"var",$lineCodeArray[2]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[2]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            xmlCreateArgument(2,$typeOfArg,$valueOfArg);
                        }

                        if (!checkConst($lineCodeArray[3])){    // Third arg
                            if (!checkVar($lineCodeArray[3]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(3,"var",$lineCodeArray[3]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[3]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            xmlCreateArgument(3,$typeOfArg,$valueOfArg);
                        }
                        break;
                    case "JUMPIFEQ":
                    case "JUMPIFNEQ":
                        if (!checkLabel($lineCodeArray[1]))     // First arg
                            err_msg($instArgErr, $lexOrSyntaxErr);
                        else {
                            xmlCreateArgument(1,"label",$lineCodeArray[1]);
                        }

                        if (!checkConst($lineCodeArray[2])){    // Second arg
                            if (!checkVar($lineCodeArray[2]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(2,"var",$lineCodeArray[2]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[2]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            xmlCreateArgument(2,$typeOfArg,$valueOfArg);
                        }

                        if (!checkConst($lineCodeArray[3])){    // Third arg
                            if (!checkVar($lineCodeArray[3]))
                                err_msg($instArgErr, $lexOrSyntaxErr);
                            else
                                xmlCreateArgument(3,"var",$lineCodeArray[3]);
                        }
                        else{
                            $splitArg = explode('@', $lineCodeArray[3]);
                            $typeOfArg = $splitArg[0];
                            $valueOfArg = $splitArg[1];

                            $numberOfJumps++;   // Bonus
                            xmlCreateArgument(3,$typeOfArg,$valueOfArg);
                        }
                        break;
                    default:
                        decideSyntaxOrWrongOpcodeErr($instruction, $unknownOpCodetErr, $instArgErr);
                }
                break;
            default:    // More than three arguments
                err_msg($numOfArgErr, $lexOrSyntaxErr);
        }
        xmlwriter_end_element($xmlWrite); // End the instruction element
    }

    $longopts = array(
      "stats:",
      "loc",
      "comments",
      "labels",
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
            err_msg("Unknown option", $wrongParamErr);
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
            echo "      --loc   Prints the number of instructions\n";
            echo "      --comments   Prints the number of comments\n";
            echo "      --labels   Prints the number of labels\n";
            echo "      --jumps   Prints the number of jumps\n";
            exit(0);
        }
        else {
            return err_msg("--help cannot take any more arguments.", $wrongParamErr);
        }
    }

    $input = fopen("php://stdin","r");
    if ($input === false)
        err_msg("Failed to read from stdin", $inputFileErr);

    // Comments and newlines at the beginning check
    while (!feof($input)){
        $currentLine = (fgets($input)); // Reads first lines from STDIN until header comes
        $currentLine = trim($currentLine); // Trims whitespaces from beginning and the end of the line

        if (strlen($currentLine) == 0){     // It was empty line
            continue;
        }
        else if ($currentLine[0] == '#'){   // It was only comment
            $numberOfComments++;
            continue;
        }
        // First line of actual code
        break;
    }
    
    $commentLine = explode('#', $currentLine);
    if (sizeof($commentLine) > 1) // Checks for comment after header
        $numberOfComments++;

    // Header validity check
    $header = strtoupper(trim($commentLine[0]));
    if (strcmp($header,".IPPCODE20") != 0)    // Verify the header
        err_msg("Wrong header on input", $wrongHeaderErr);

    // XML header
    $xmlWrite = xmlwriter_open_memory();
    xmlwriter_set_indent($xmlWrite, 1);
    xmlwriter_set_indent_string($xmlWrite, '   ');

    xmlwriter_start_document($xmlWrite, '1.0', 'UTF-8');

    // Program
    xmlwriter_start_element($xmlWrite, 'program');

    // Attributes
    xmlwriter_start_attribute($xmlWrite, 'language');
    xmlwriter_text($xmlWrite, 'IPPcode20');
    xmlwriter_end_attribute($xmlWrite);

    // While cycle for whole input
    while (!feof($input)){
        $currentLine = (fgets($input));
        if (strcmp($currentLine, "\n") == 0 || $currentLine == "")     // Ignore empty lines
            continue;

        $commentLine = explode('#', $currentLine);
        if (sizeof($commentLine) > 1){   // Checks for comments
            $numberOfComments++;
            if ($currentLine[0] == '#'){   // It was only comment
                continue;
            }
            $currentLine = trim($commentLine[0]);     // Trims the comments
        }
        
        $numberOfInstructions++;    // Order and also used for --loc option

        $lineCodeArray = preg_split('/\s+/', $currentLine, 0, PREG_SPLIT_NO_EMPTY);  // Delete whitespaces

        
        $inst = strtoupper($lineCodeArray[0]);
        if (!in_array($inst,$instructions))     // Check for valid instruction
            err_msg("Unknown instruction", $wrongOpCodeErr);

        xmlwriter_start_element($xmlWrite, 'instruction');
        xmlwriter_start_attribute($xmlWrite, 'order');
        xmlwriter_text($xmlWrite, "$numberOfInstructions");
        xmlwriter_end_attribute($xmlWrite);

        xmlwriter_start_attribute($xmlWrite, 'opcode');
        xmlwriter_text($xmlWrite, "$inst");
        xmlwriter_end_attribute($xmlWrite);
        whatInstruction($lineCodeArray, $inst);

    }

    // Bonus statistics
    if (count($argv) >= 2){
        if (preg_grep('/--stats=.*/', $argv)){
            $statsFile = $my_args["stats"];

            foreach ($argv as $i => $option){
                if ($i < 1)
                    continue;
                if ($i == 1)
                    if (file_put_contents($statsFile, "") == false);  // Clear the content
                        err_msg("File for statistics cannot be opened", $outputFileErr);
                switch ($option){
                    case "--stats=$statsFile":
                        break;
                    case "--loc":
                        if (file_put_contents($statsFile, "$numberOfInstructions\n",FILE_APPEND) == false)
                            err_msg("File for statistics cannot be opened", $outputFileErr);
                        break;
                    case "--comments":
                        if (file_put_contents($statsFile, "$numberOfComments\n",FILE_APPEND) == false)
                            err_msg("File for statistics cannot be opened", $outputFileErr);
                        break;
                    case "--labels":
                        if (file_put_contents($statsFile, "$numberOfLabels\n",FILE_APPEND) == false)
                            err_msg("File for statistics cannot be opened", $outputFileErr);
                        break;
                    case "--jumps":
                        if (file_put_contents($statsFile, "$numberOfJumps\n",FILE_APPEND) == false)
                            err_msg("File for statistics cannot be opened", $outputFileErr);
                        break;
                    default:
                        err_msg("You're trying to specify stats without '--stats' option", $wrongParamErr);
                }
            }
        }
        else
            err_msg("You're trying to specify stats without '--stats' option", $wrongParamErr);
    }

    // End of the XML document
    xmlwriter_end_element($xmlWrite);
    xmlwriter_end_document($xmlWrite);
    echo xmlwriter_output_memory($xmlWrite);
?>