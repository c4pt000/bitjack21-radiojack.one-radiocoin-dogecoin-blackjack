<?php
require_once 'common.php';
validate_session();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<!--<meta http-equiv="refresh" content="3" /> uncoment this for auto refresh-->
        <meta name="keywords" content="blackjack, bitcoin, honest, honesty, cryptoproof" />
        <meta name="description" content="BitJack21 - Bitcoin Blackjack - CryptoProof Honesty Algorithm" /> 
        <link rel="Shortcut Icon" href="images/favicon.ico">
        <title>BitJack21 - Bitcoin Blackjack - CryptoProof Honesty Algorithm</title> 
        <base target="_self" />
        
<!--Stylesheets--> 
        <link href="css/layout.css" rel="stylesheet" type="text/css" media="screen" /> 
 
<!--Javascript--> 

        <!--[if IE]>
                <script src="js/html5.js"></script>
        <![endif]-->

<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/jquery.backgroundPosition.js_6.js"></script>
<script type="text/javascript" src="js/menu.js"></script>

</head>
<body>
<div id="wrapper">
<div id="header"></div>
<div id="menubar"><?php drawmenu(); ?></div>
<div id="singlecolumnleft">
<h1>Crypto-Proof</h1><br>
It only seems fitting that cryptography, which is the basis for our trust in the bitcoin system, will now be used as the basis for trust in my bitcoin blackjack game.  This system is a mathematical/verifiable PROOF that every hand you play on http://radiojack.one is 100% completely honest.  It allows you to prove/verify that:<br>
1.)  The order of the cards was COMPLETELY 100% random.<br>
2.)  The order of the cards was determined once before any cards were dealt, and did not change during the hand.<br>
<br>
How I do this is by employing cryptographic hashes:<br>
<br>
1.) Embedded in the client side javascript code of the game is a random number generator that generates a 128-bit random number before each hand is dealt. (Call this number R2). The user has the option to either use the number generated by their web browser, or modify it as they see fit.<br>
<br>
2.) Before each hand, the server generates two more 128-bit numbers (using a HARDWARE random number generator).  Call these numbers R1 and RX.<br>
<br>
3.) The server will eventually determine the deck order by using R1 and R2.  However, before the hand starts (ie, before the user clicks on DEAL) the server computes SHA256(R1 + RX) and displays this value to the user.  (Note the '+' is concatenation).<br>
<br>
4.) When the user clicks on "DEAL", their random number (R2) is sent to the server.<br>
<br>
4.) The server generates a very long string of random bits by calling<br>
SHA256(R1 + R2 + 0)<br>
SHA256(R1 + R2 + 1)<br>
SHA256(R1 + R2 + 2)<br>
SHA256(R1 + R2 + 3)<br>
etc...<br>
<br>
This string of random bits essentially *is* the deck order. In other words, you can directly determine the order of the cards in the deck by using this (The function is posted below, I will soon be implementing this in javascript so that users can verify it themselves without having to use PHP).<br>
<br>
5.) AFTER the hand is over, the server displays to the user the actual values of R1 and RX.  The user can then verify that SHA(R1 + RX) == the hash that was displayed prior to the hand starting.  They can also verify that the order of the cards is correct given the values of R1 and R2.  Since R2 was generated randomly by their web browser (or entered directly by the user), and the value of R2 is used as a random seed in the generation of the deck order, the user can be assured that the order of the cards was in fact 100% random.<br>
<br>
Questions / Comments? mr.sizlak.21 [at] gmail [dot] com<br>
<br>
Thanks for playing!<br>
<br>
-Mr. Sizlak<br>

<h1> Relevant code </h1>
<br><br>
<textarea cols="100" rows="130" style="font-family:monospace">
//
// This is the card chuffling PHP function.
// It uses the FISHER-YATES card shuffling algorithm
//
// It takes 4 arguments:
//   $cards - An array, contents do not matter
//   $n -    The number of cards to shuffle.  This is always 416 (8 decks)
//   $R1 -   Server generated 128-bit random number.  (generated with a hardware RNG).
//           Note that this is formatted as a HEX STRING, ie '4f87becbfeb09cac3967293d41b29cc9'
//   $R2 -   This is the CLIENT generated 128-bit random number.  This value was sent from the client
//           web browser as a HEX STRING, ie '4f87becbfeb09cac3967293d41b29cc9'
//

function shufflecardsSHA256(&$cards, $n, $R1, $R2)
{
    if($n > 416)
    {
        throw new Exception('shufflecardsSHA256 can only be called with up to 416 cards!');
    }

    // Note the card values for all 8 decks are 0-51.
    //
    // 0 = 2 of diamonds
    // 1 = 3 of diamonds
    // ...
    // 12 = A of diamonds
    // 13 = 2 of hearts
    // 14 = 3 of hearts
    // ...
    // 25 = A of hearts
    // 26 = 2 of clubs
    // 27 = 3 of clubs
    // ...
    // 38 = A of clubs
    // 39 = 2 of spades
    // 40 = 3 of spades
    // ...
    // 51 = A of spades

    for($i = 0; $i < $n; $i++)
    {
        $cards[$i] = $i % 52;
    }

    if ($n > 1)
    {
        $randbits_hex = '';
        $randbits_bin = '';
        $r = 0;
        $randbits_hex = hash('sha256',''.$R1.$R2.$r);
        $r++;
        hextobin($randbits_bin, $randbits_hex);
        $bitsleft = 256;

        for ($i = ($n - 1); $i >= 1; $i--)
        {
            $bitsneeded = 0;
            $myrand = -1;
            if($i>=256)      { $bitsneeded = 9; }
            else if($i>=128) { $bitsneeded = 8; }
            else if($i>=64)  { $bitsneeded = 7; }
            else if($i>=32)  { $bitsneeded = 6; }
            else if($i>=16)  { $bitsneeded = 5; }
            else if($i>=8)   { $bitsneeded = 4; }
            else if($i>=4)   { $bitsneeded = 3; }
            else if($i>=2)   { $bitsneeded = 2; }
            else if($i>=1)   { $bitsneeded = 1; }
            do
            {
                if($bitsneeded > $bitsleft)
                {
                    $randbits_hex = hash('sha256',''.$R1.$R2.$r);
                    $r++;
                    hextobin($randbits_bin, $randbits_hex);
                    $bitsleft += 256;
                }
                $num = substr($randbits_bin, 0, $bitsneeded);
                $randbits_bin = substr($randbits_bin, $bitsneeded);
                $bitsleft -= $bitsneeded;
                $num = intval(base_convert($num, 2, 10));
                if($num <= $i)
                {
                    $myrand = $num;
                }
            }
            while($myrand == -1);

            $t = $cards[$myrand];
            $cards[$myrand] = $cards[$i];
            $cards[$i] = $t;
        }
    }
}



function hextobin(&$outstr, $instr, $append = true)
{
    if(!$append)
    {
        $outstr = '';
    }

    for($i = 0; $i < strlen($instr); $i++)
    {
        if($instr{$i} == '0') { $outstr .= '0000'; }
        else if($instr{$i} == '1') { $outstr .= '0001'; }
        else if($instr{$i} == '2') { $outstr .= '0010'; }
        else if($instr{$i} == '3') { $outstr .= '0011'; }
        else if($instr{$i} == '4') { $outstr .= '0100'; }
        else if($instr{$i} == '5') { $outstr .= '0101'; }
        else if($instr{$i} == '6') { $outstr .= '0110'; }
        else if($instr{$i} == '7') { $outstr .= '0111'; }
        else if($instr{$i} == '8') { $outstr .= '1000'; }
        else if($instr{$i} == '9') { $outstr .= '1001'; }
        else if($instr{$i} == 'a') { $outstr .= '1010'; }
        else if($instr{$i} == 'b') { $outstr .= '1011'; }
        else if($instr{$i} == 'c') { $outstr .= '1100'; }
        else if($instr{$i} == 'd') { $outstr .= '1101'; }
        else if($instr{$i} == 'e') { $outstr .= '1110'; }
        else if($instr{$i} == 'f') { $outstr .= '1111'; }
    }
}
</textarea>

</div>

</div>

</body>

</html>
