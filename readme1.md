Implementační dokumentace k 1. úloze do IPP 2019/2020  
Jméno a příjmení: Lukas Javorsky  
Login: xjavor20  

### Parse.php

At the start of the script, it will start looking for a possible comments (for bonus STATP)  
and then it will check for the header correctness.  
If the header is correct the `xmlWriter` function ensures the header generation.  
These are main aspects that are set in a first place (`version="1.0"`, `encoding="UTF-8"`, `program`, `language="IPPcode20"`).  
