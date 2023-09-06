<?php

// lost userID function
function Login($LostUsr)
{

if(!isset($_SESSION['UserID']) OR empty($_SESSION['UserID']))
    {
    if(session_status() === PHP_SESSION_ACTIVE) {session_destroy();}
    switch($LostUsr)
    {
    case "Location: invpal.php?FirstOpen=":
    header("Location: menu\main.php?LostUsr=$LostUsr");       

    break;
    case "Location: invpckbin.php?FirstOpen=":
    header("Location: menu\main.php?LostUsr=$LostUsr");   

    break;
    case "Location: main.php";
    header("Location: menu\main.php?LostUsr=$LostUsr");   
    break;    
    }
    }
}

// EAN or number check function
function checkValue($value,$format) {
    switch($format)
    { 
    // variant for scan ID   
    case "SNNUM":     
        if (strlen($value) === 20  or strlen($value) === 25 )
        {
        $_GET["PalletID"]=  substr($_GET["PalletID"], 2, 18);
        return true;
        } 
        else if(strlen($value) === 15)
        {
        $_GET["PalletID"]=  substr($_GET["PalletID"],0, 15);
        return true;
        }
        else 
        {
        return false;
        }
        break;
    // variant for scan pallets location article
    case "ARTC": 
        if (strlen($value) === 40  or strlen($value) === 50 )
        {
        $_GET["Article"]=  strval(substr($_GET["Article"], 3, 13) ."/". substr($_GET["Article"], 18, 2));
        return true;
        } 
        elseif (strlen($value) === 45 )
        {
        $_GET["Article"] =  strval(substr($_GET["Article"], 3, 13));
        $_SESSION["PolishShit"]="";
        return true;
        }
        elseif (strlen($value) === 46)
        {
        $_GET["Article"] =  strval(substr($_GET["Article"], 3, 13));
        $_SESSION["PolishShit"]="";
        return true;
        }
        elseif (strlen($value) === 16)
        {
        $_GET["Article"]= strval($_GET["Article"]);
        return true;
        }
        else
        {
        return false;
        }
        break;
    //variant for infoarticle
    case "INFO": 
        if (strlen($value) === 40  or strlen($value) === 50)
        {
        $_GET["Article"]=  strval(substr($_GET["Article"], 3, 13) ."/". substr($_GET["Article"], 18, 2));
        $_SESSION["EAN_format"] = 'EAN16'; 
        return true;
        }
        elseif (strlen($value) === 44)
        {
        $_GET["Article"] =  strval(substr($_GET["Article"], -11));
        $_SESSION["EAN_format"] = 'Code'; 
        return true;
        }
        elseif (strlen($value) === 45)
        {
        $_GET["Article"] =  strval(substr($_GET["Article"], 3, 13));
        $_SESSION["EAN_format"] = 'EAN16'; 
        $_SESSION["PolishShit"]="";
        return true;
        }
        elseif (strlen($value) === 46)
        {    
        $_GET["Article"] =  strval(substr($_GET["Article"], 3, 13));
        $_SESSION["EAN_format"] = 'EAN16'; 
        $_SESSION["PolishShit"]="";
        return true;
        }
        elseif (strlen($value) === 16)
        {
        $_GET["Article"]= strval($_GET["Article"]);
        $_SESSION["EAN_format"] = 'EAN16'; 
        return true;
        }
        elseif (strlen($value) === 13 and is_numeric($value))
        {
        $_GET["Article"]= strval($_GET["Article"]);
        $_SESSION["EAN_format"] = 'EAN13'; 
        return true;
        }
        elseif (strlen($value) === 11 and substr($value, 8, 1) == '.')
        {
        $_GET["Article"]= strval($_GET["Article"]);
        $_SESSION["EAN_format"] = 'Code'; 
        return true;
        }
        else
        {
        return false;
        }
        break;
    // variant for scan pickbins location article 
    case "BOX": 
        if (strlen($value) === 40  or strlen($value) === 50)
        {
        $_GET["Article"]=  strval(substr($_GET["BoxID"], 3, 13) ."/". substr($_GET["BoxID"], 18, 2));
        return true;
        } 

        elseif (strlen($value) === 45)
        {
        $_GET["Article"] =  strval(substr($_GET["BoxID"], 3, 13));
        $_SESSION["PolishShit"]="";
        return true;
        }
        elseif (strlen($value) === 46)
        {
        $_GET["Article"] =  strval(substr($_GET["BoxID"], 3, 13));
        $_GET["BoxID"]= substr($_GET["BoxID"], 0, 34).substr($_GET["BoxID"], 35, 11);
        $_SESSION["PolishShit"]="";
        return true;
        }
        else
        {
        return false;
        }
        break; 
    // variant for entered value
    case "QUANT": 
        if (is_numeric($value))
        {
        return true;
        } 
        else 
        {
        return false;
        }
        break;   
    }
}

// import NCI_Stock table
class DataImporter
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = PDOConnect::getInstance('Liquid');
    }

    public function importData($filePath,$Table)
    {

        $startTime = microtime(true);
        $file = fopen($filePath, 'r');

        fgets($file);
        while (($line = fgets($file)) !== false) {
            $data = explode("\t", $line);

            $article = $data[0];
            $description = $data[1];
            $kpwhzo = $data[2];
            $kpwhlo = $data[3];
            $kpcase = $data[4];
            $kpaval = $data[5];
            $quantity = intval($data[6]);

            $row = array(
                'Article' => $article,
                'KPWHZO' => $kpwhzo,
                'KPWHLO' => $kpwhlo,
                'KPCASE' => $kpcase,
                'KPAVAL' => $kpaval,
                'Quantity' => $quantity,
            );

            $this->pdo->insert($Table, $row);
        }
        fclose($file);
    }
}



function AddToArray($existingArray, $rowIndex, $record) {
    if (!isset($existingArray[$rowIndex]) || !is_array($existingArray[$rowIndex])){
        $existingArray[$rowIndex] = array(); 
    }

    $existingArray[$rowIndex][] = $record;

    return $existingArray;
}


?>