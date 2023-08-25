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
    header("Location: main.php?LostUsr=$LostUsr");       

    break;
    case "Location: invpckbin.php?FirstOpen=":
    header("Location: main.php?LostUsr=$LostUsr");   

    break;
    case "Location: main.php";
    header("Location: main.php?LostUsr=$LostUsr");   
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
        $_GET["Article"] =  strval(substr($_GET["Article"], 3, 13));
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

//Box, pallets counter function
function PalCounter($InvNum,$InvRnd)
{
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    $SQL = "SELECT PalletID as PalCounter FROM [Liquid].[dbo].[NCI_Pallets] where  ([InvNum] = :InvNum) and ([InvRnd] = :InvRnd)";
    $params = array('InvNum' => $InvNum,'InvRnd' => $InvRnd );  
    $stmt = $Connection->select($SQL,$params);                    
    $count = $stmt['count'];
    return $count;

}

//Find info about article by EAN
function EANinfo($EAN,$format)
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 

switch($format)
{ 
case "EAN13": 
    $SQL = "SELECT  [Code],[Material],[Box],[EAN_box],[Crt],[EAN_crt],[Pack],[EAN_pack] FROM [Liquid].[dbo].[NCI_EAN]
    where (left([EAN_box],13) like :EAN1) or ([EAN_crt] like :EAN2) or ([EAN_pack] like :EAN3)";
    $params = array('EAN1' => '%'.$EAN,'EAN2' => '%'.$EAN,'EAN3' => '%'.$EAN);        
    break;
case "EAN16":
    $SQL = "SELECT  [Code],[Material],[Box],[EAN_box],[Crt],[EAN_crt],[Pack],[EAN_pack] FROM [Liquid].[dbo].[NCI_EAN]
    where ([EAN_box] like :EAN1)";
    $params = array('EAN1' => '%'.$EAN);        
    break;
case "Code":
    $SQL = "SELECT  [Code],[Material],[Box],[EAN_box],[Crt],[EAN_crt],[Pack],[EAN_pack] FROM [Liquid].[dbo].[NCI_EAN]
    where ([Code] like :EAN1)";
    $params = array('EAN1' => '%'.$EAN);        
    break;
}
$stmt = $Connection->select($SQL,$params);
$count = $stmt['count'];

if ($count !==0 )
    {
    $rows = $stmt['rows'];
    if($EAN = $stmt['rows'][0]['EAN_box'])
            {
            $Packaging='Box';
            }
        elseif($EAN = $stmt['rows'][0]['EAN_crt'])
            {
            $Packaging='Crt';
            }
        elseif($EAN = $stmt['rows'][0]['EAN_pack'])
            {
            $Packaging='Pck';
            }

    foreach ($rows as $row) 
        {
        foreach ($row as $key => $value) 
            {
            if ($key == 'Code')
                {
                $Code = $value;
                }
            elseif($key == 'Material')
                {
                $Material =  $value;
                }
            elseif($key == 'Box')
                {
                $Box = $value;
                }
            elseif($key == 'Crt')
                {
                $Crt = $value;
                }
            elseif($key == 'Pack')
                {
                $Pck = $value;
                }
            elseif($key == 'EAN_box')
                {
                $EAN_box = $value;
                }
            elseif($key == 'EAN_crt')
                {
                $EAN_crt = $value;
                }
            elseif($key == 'EAN_pack')
                {
                $EAN_pck = $value;
                }
            }

        if (empty($dataArray))          
            {
            $dataArray = array();
            $MaxIndex=0;
            }
        else 
            {
            $dataArray=$dataArray;
            $MaxIndex = max(array_keys($dataArray));
            }        
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Code);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Material);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Box);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Crt);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Pck);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$EAN_box);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$EAN_crt);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$EAN_pck);
        $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Packaging);
        }
    $_SESSION['DataArray']=$dataArray;
    return $count;
    }
else
    {
    unset($_SESSION['EAN_format']);
    return $count;
    }
}

function AddToArray($existingArray, $rowIndex, $record) {
    if (!isset($existingArray[$rowIndex]) || !is_array($existingArray[$rowIndex])){
        $existingArray[$rowIndex] = array(); 
    }

    $existingArray[$rowIndex][] = $record;

    return $existingArray;
}

function ArticleLoc($Code,$format)
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
$txtpath = "c:\\xampp\htdocs\NCI\\var_ext\NCI_Info_import.txt";
$txt = file_get_contents($txtpath);
$items = explode(';', $txt);
$txt = $items[0];
$table = "NCI_ArticleInfo_".$txt."_View";
if($format == 'Code')
    {
    $SQL = "SELECT [Article],[KPWHLO],[Quantity] FROM [Liquid].[dbo].[$table] where ([Article] = :Article)";
    $params = array('Article' => $Code);
    }
else
    {
    $SQL = "SELECT [Article],[KPWHLO],[Quantity] FROM [Liquid].[dbo].[$table] where ([EAN_crt] like :EAN1) or ([EAN_pack] like :EAN2)";
    $params = array('EAN1' => '%'.$Code,'EAN2' => '%'.$Code);
    }
$stmt = $Connection->select($SQL,$params);
$count = $stmt['count'];

if ($count!==0)
    {
    $rows =  $stmt['rows'];
    foreach ($rows as $row) 
        {
        foreach ($row as $key => $value) 
            {
            if ($key == 'Article')
                {
                $Article = $value;
                }
            elseif($key == 'KPWHLO')
                {
                $Location =  $value;
                }
            elseif($key == 'Quantity')
                {
                $Quantity = $value;
                }
            }


        if (empty($dataArray))          
        {
            $dataArray = array();
            $MaxIndex=0;
        }
        else 
        {
        $dataArray=$dataArray;
        $MaxIndex = max(array_keys($dataArray));
        }
        
    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Article );
    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Location);
    $dataArray=AddToArray($dataArray,$MaxIndex + 1,$Quantity);
        }
    return $dataArray;
    }
else
    {
    return 0;
    }
}
?>