Implementační dokumentace k 1. úloze do IPP 2019/2020  
Jméno a příjmení: Lukas Javorsky  
Login: xjavor20  

# Parse.php

At the start of the script, it will start looking for a possible comments (for bonus STATP)  
and then it will check for the header correctness.  
If the header is correct the `xmlWriter` function ensures the header generation.  
These are main aspects that are set in a first place (`version="1.0"`, `encoding="UTF-8"`, `program`, `language="IPPcode20"`).  

The input is read line after line on stdin, and it's checked for lexical or syntax errors.  
If there is any empty line or comment, it's automatickly ignored.

## Options

`parse.php` script allows user to specify various of options.
These options are focused to get some kind of statistics from the input.
To get these statistics you have to pass `--stats=<file>` option.
This option doesn't have to be first, but if it's not given, and some of the others is, the script ends with an error.
The `<file>` have to be writable file, where the statistics should be printed.
There are 4 kinds of statistics that can be printed.
`--loc` for number of instructions that have been used,
`--coments` for number of comments in code,
`--jumps` for number of jumps done in code,
`--labels` for all unique labels located in code.

## Parsing process

The line read from stdin is split into array of strings which was delimited by whitespaces.
Next thing this script does, is to check for number of arguments in the instruction.
If the number of arguments is correct, the opcode (first word in instruction) is checked.
If it's correct too, the lexical and syntax analyzis for `<symb>`,`<var>`,`<label>` or `<type>`.

### Regex check

The lexical check is made by running the function `preg_grep()` with specific regex and argument as parameters.
There are 4 functions in script that ensures the lexical correctness of arguments.
`checkVar` makes sure that `<var>` is in correct format.
`checkConst` ensures the correctness of constants. The `<symb>` argument can be constant or variable so it's firstly checked as constant and then as variable.
`checkLabel` checks the `<label>`'s format.
`checkType` checks the format of `<type>`.

## XML write

As mentioned above, xml library is used for output.
Every instruction starts with it's own element and have two atributes.
First is `<order>` and the second is `<opcode>`.
Then the `whatInstruction()` function is called to fill the rest.

If the instruction is lexically and syntaxly correct, and have some arguments, the `xmlCreateArgument()` function is called.
This function is called for every argument in instruction.
Every argument have it's own order number stored in the name `argX` where `X` is the order number.

## Bonus

The bonus task for this project was the statistics output which is mentioned above.
To count the specific stats, I used global variables named `numberOf*`.
