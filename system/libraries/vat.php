<? 
//Validation VAT number Europe (online or GB, Ireland, Belgium, The Netherlands, France, Germany and Luxembourgh offline)
//Include library and send the VAT in any format to validateVAT(), response is a boolean.

function validateVAT($vat, $presetLoc = NULL){
    //Prepare VAT input for validation
    $aUnwanted = array("/", "-", ",", ".", " ", "/\s+/");
    $vatId = str_replace($aUnwanted, '', trim($vat));
    $valid = false;
    if(strlen($vatId) > 4){
        $cc = substr($vatId, 0, 2);
        $vn = substr($vatId, 2);
        $errorSoap = true;
        $client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
        //Connecting to european VIES site to check if the provided VAT number is registered
        if($client){
            $params = array('countryCode' => $cc, 'vatNumber' => $vn);
            try{
                $r = $client->checkVat($params);
                if($r->valid == true){
                    $valid = true;
                    $errorSoap = false;
                }else{
                    $valid = false;
                    $errorSoap = false;
                }
            }catch(SoapFault $e){
                $errorSoap = true;
            }
        }
        //If an error occured while trying to connect to VIES or an error occured after trying to connect, run local validation for VAT
        if(($valid == false) && ($errorSoap == true)){
            //Lengths for the VAT numbers Area Based
            $aLengthRest = array(
                'be'=>10, 'de'=>9, 'nl'=>12, 'lu'=>8, 'fr'=>11, 'ie'=>array(8,9), 'gb'=>array(9,12,5)
            );
            //Letters with their respective number for the Ireland's VAT check
            $relocLibrary = array(
                '0' => 'w', '1' => 'a', '2' => 'b', '3' => 'c', '4' => 'd', '5' => 'e', '6' => 'f', '7' => 'g', '8' => 'h', 
                '9' => 'i', '10' => 'j', '11' => 'k', '12' => 'l', '13' => 'm', '14' => 'n', '15' => 'o', '16' => 'p',
                '17' => 'q', '18' => 'r', '19' => 's', '20' => 't', '21' => 'u', '22' => 'v'
            );
            //Regular Expressions for the VAT numbers Area Based
            $aExpr = array(
                'be'=>'/(1|0){1}[0-9]{9}/i', 'de'=>'/[0-9]{9}/i', 'nl'=>'/[0-9]{9}b[0-9]{2}/i', 'lu'=>'/[0-9]{8}/i', 'fr'=>'/[a-z0-9]{2}[0-9]{9}/i', 
                'ie'=>'/(([0-9]{7}[a-z]{1})|([0-9]{7}[a-z]{1}w)|([0-9]{7}[a-z]{2})|([0-9]{1}[a-z]{1}[0-9]{5}[a-z]{1}))/i',
                'gb'=>'/(([0-9]{9})|([0-9]{12})|(gd[0-4]{1}[0-9]{2})|(ha[5-9]{1}[0-9]{2}))/i'
            );
            $prefix = strtolower(substr($vat, 0, 2));
            $rest = strtolower(substr($vat, 2, (strlen($vat)-2)));
            $rest = str_replace($aUnwanted, '', $rest);
            //check if the length is possible for a VAT number of that area
            $lengthCorrect = false;
            $lengteRest = strlen($rest);
            if(is_array($aLengthRest[$prefix])){
                if(in_array($lengteRest, $aLengthRest[$prefix])){
                    $lengthCorrect = true;
                }
            }else{
                if($aLengthRest[$prefix] == $lengteRest){
                    $lengthCorrect = true;
                }
            }
            //Formula for each area to figure out if the provided VAT number is potentially valid
            if(($lengthCorrect)&&(preg_match($aExpr[$prefix],$rest))){    
                switch($prefix){
                    case 'be':
                        //9 total digits, 7 first = SIREN, 2 last = controle
                        //97-CMOD(SIREN, 97) = controle
                        $rNumber = intval(substr($rest, 1, 7));
                        $cNumber = intval(substr($rest, 8, 2));
                        $controle = 97-intval(bcmod(strval($rNumber), strval(97)));
                        if($controle == $cNumber){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'de':
                        //documentation not added due too much. Check http://zylla.wipos.p.lodz.pl/ut/translation.html or pdf backup for more info
                        $calcDigits = substr($rest, 0, 8);
                        $cDigit = intval(substr($rest, 8, 1));
                        $m = 10;
                        $n = 11;
                        $sum = 0;
                        $product = $m;
                        for($i = 0, $l = strlen($calcDigits); $i < $l; $i++){
                            $calcDigit = intval(substr($calcDigits, $i, 1));
                            $sum = $product + $calcDigit;
                            $sum = intval(bcmod(strval($sum), strval(10)));
                            $product = intval(bcmod(strval((2*$sum)), strval(11)));
                        }
                        $controle = 11 - intval($product);
                        if($controle == $cDigit){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'nl':
                        //documentation not added due too much. Check http://zylla.wipos.p.lodz.pl/ut/translation.html or pdf backup for more info
                        $calcDigits = substr($rest, 0, 8);
                        $cDigit = intval(substr($rest, 8, 1));
                        $sum = 0;
                        for($i = 0, $l = 8, $m = 9; $i<$l; $i++, $m--){
                            $calcDigit = intval(substr($calcDigits, $i, 1));
                            $sum = $sum + ($calcDigit * $m);
                        }
                        $controle = intval(bcmod(strval($sum), strval(11)));
                        if($controle == $cDigit){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'lu':
                        //8 total digits, 6 first = SIREN, 2 last = controle
                        //CMOD(SIREN, 89) == Controle
                        $calcDigits = substr($rest, 0, 6);
                        $cDigit = intval(substr($rest, 6, 2));
                        $controle = intval(bcmod($calcDigits, strval(89)));
                        if($controle == $cDigit){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'fr':
                        //11 total digits, 2 first = controle numbers, 9 last = SIREN
                        //Formula: cmod((12+3*(cmod(SIREN, 97))),97) = controle numbers
                        $cDigit = intval(substr($rest, 0, 2));
                        $calcDigits = substr($rest, 2, 9);
                        $controle = intval(bcmod(strval(12+(3*(intval(bcmod($calcDigits, strval(97)))))),strval(97)));
                        if($controle == $cDigit){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'ie':
                        //documentation not added due too much. Check http://zylla.wipos.p.lodz.pl/ut/translation.html or pdf backup for more info
                        if(preg_match('([0-9]{1}[a-z]{1}[0-9]{5}[a-z]{1})',$rest)){
                            //Reform VAT from old pattern to the new format
                            $first = substr($rest, 0, 1);
                            $middle = substr($rest, 2, 5);
                            $last = substr($rest, 7, 1);
                            $rest = strval(0)+strval($middle)+strval($first)+strval($last);
                        }
                        $calcDigits = substr($rest, 0, 7);
                        $cDigit = substr($rest, 7, 1);
                        $sum = 0;
                        for($i=0, $max=7, $m=8; $i < $max; $i++,$m--){
                            $sum = $sum + (intval(substr($calcDigits, $i, 1))*$m);
                        }
                        $controle = $relocLibrary[strval(bcmod(strval($sum), strval(23)))];
                        if($controle == $cDigit){
                            $valid = true;
                            break;
                        }else{
                            $valid = false;
                            break;
                        }
                    case 'gb':
                        //documentation not added due too much. Check wikipedia for more information about what's being done here.
                        if(((strlen($rest) == 9) ||(strlen($rest) == 12))){
                            $part1 = intval(substr($rest, 0, 7));
                            $modulusCheck = intval(substr($rest, 7, 2));
                            if($modulusCheck < 97){
                                $totaal = 0;
                                for($i = 0, $m=8, $l = strlen($part1); $i<$l; $i++, $m--){
                                    $totaal = intval($totaal) + (intval(substr($part1, $i, 1))*$m);
                                }
                                while($totaal > 0){
                                    $totaal = $totaal - 97;
                                }
                                if($totaal < 0){
                                    $check = $totaal + intval($modulusCheck);
                                    if($check == 0){
                                        $valid = true;
                                        break;
                                    }
                                    else{
                                        break;
                                    }
                                }else{
                                    break;
                                }
                            }else{
                                break;
                            }
                        }else{
                            $valid = true;
                            break;
                        }
                    default:
                        $valid = true;
                        break;
                }
            }
        }
    }
    return $valid;
}