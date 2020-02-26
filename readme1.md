Implementační dokumentace k 1. úloze do IPP 2019/2020  
Jméno a příjmení: Lukas Javorsky  
Login: xjavor20  

# Parse.php

At the start of the script, it will start looking for a comments (for bonus STATP) and empty lines that are located before header and deletes them.
Then it will check the header if it's writen correctly.
If the header is correct the `xmlWriter` function ensures the xml header generation.
These are main things that are set in a first place (`version="1.0"`, `encoding="UTF-8"`, `program`, `language="IPPcode20"`).

The input is then read line after line on stdin, and it's checked for lexical or syntax errors.
If there is any empty line or comment, it's automatickly ignored.

## Options

Script `parse.php` allows user to specify various of options.
These options are focused to get some kind of statistics from the input.
To get these statistics you have to pass `--stats=<file>` option.
This option doesn't have to be first, but if it's not given, and some of the others are, the script ends with an error.
The `<file>` have to be writable file, for further appendations of the statistics.
There are 4 kinds of statistics that can be printed out:  
`--loc` for number of instructions that have been used.  
`--coments` for number of comments in code.  
`--jumps` for number of jumps done in code.  
`--labels` for all unique labels located in code.  
To see a short help/manual use `--help` option.

## Parsing process

The line read from stdin is split into array of strings which was delimited by whitespaces.
Next thing that script does, is checking for number of arguments in the instruction.
If the number of arguments is correct, the opcode (first word in instruction) is then checked.
If it's correct too, the lexical and syntax analyzis comes to place for `<symb>`,`<var>`,`<label>` or `<type>`.

### Regex check

The lexical check is made by running the function `preg_grep()` with specific regex and argument as arguments.
There are 4 functions in script that ensures the lexical correctness of arguments.
`checkVar` makes sure that `<var>` is in correct format.
`checkConst` ensures the correctness of constants. The `<symb>` parameter can be constant or variable so it's firstly checked as a constant and then as variable.
`checkLabel` checks the `<label>`'s format.
`checkType` checks the format of `<type>`.

## XML write

As mentioned above, xml library is used for output of the script.
Every instruction starts with it's own element and have two atributes.
First is `<order>` and the second one is `<opcode>`.
Then the `whatInstruction()` function is called to fill the rest.

If the instruction is lexically and syntactic correct, and also have some arguments, the `xmlCreateArgument()` function is called.
This function is called for every argument in instruction.
Every argument have it's own order number stored in the element `argX` where `X` is the order number.

## Bonus

The bonus task for this project was the statistics output file, which is mentioned above.
To count the specific stats, I used global variables named `numberOf*`.
If the program ends successfully, statistics are then appended to the file specified in `--stats` argument.
