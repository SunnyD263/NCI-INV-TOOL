<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Inventura pickbin</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="Inventura palet" />
    <link rel="stylesheet" type="text/css" href="/nci/scan/style.css" />
    <link rel="icon" type="image/png" href="images/kn.png"/>
    <script src="https://code.jquery.com/jquery-3.6.4.js"
        integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E=" crossorigin="anonymous">
    </script>
</head>

<body>
    <header>
    </header>
    <?php
session_start(); 
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 
// Lost UserID checker
Login("Location: invpckbin.php?FirstOpen=");
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{

// default view/setup
if (isset($_GET['St_Location']))
    {
        $InvNum = $_SESSION['InvNum'];
        $InvRnd = $_SESSION['InvRnd'];
        $_SESSION["BoxCounter"] = PalCounter($InvNum,$InvRnd);
        invpckbinscan_main();
    }

// button 'Prázdné'   
elseif(isset($_GET['Empty']))
    {
    If($_SESSION['BoxCounter'] == 0)
        {

        // create null position for SQL comparing    
        $Pallet = '000000000000000000';
        $EAN = '0000000000000/00';
        $Quant = 0;
        $St_location = $_SESSION['St_Location'];
        $Nd_location = 'Empty';
        $currentDateTime = new DateTime();
        $DateTime = $currentDateTime->format('Y-m-d H:i:s');
        $UserID = $_SESSION['UserID'];
        $InvNum = $_SESSION['InvNum'];
        $InvRnd = $_SESSION['InvRnd'];

        // insert rows to SQLSrv table NCI_Pallets_=> InvRound
        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
        $data = array( 'PalletID' => $Pallet, 'EAN' => $EAN,'Quantity' => $Quant,'St_Location' => $St_location,'Nd_Location' => $Nd_location,'Scantime' => $DateTime,'UserID' => $UserID, 'InvNum' => $InvNum, 'InvRnd' => $InvRnd);
        $Connection->insert("NCI_Pallets_".$InvRnd, $data);

        // create null position for SQL comparing    
        $SQL = "EXECUTE FillEmpty_".$InvRnd." @St_Location = :St_Location";
        $params = array('St_Location' =>  $St_location);
        $stmt = $Connection->execute($SQL, $params);

        unset($_SESSION['BoxtID']);
        unset( $_SESSION['St_Location']);
        unset( $_SESSION['Nd_Location']);
        unset( $_SESSION['BoxCounter']);
        unset($_SESSION['PolishShit']);
        unset($_SESSION["InvNum"]);
        unset($_SESSION['InvRnd']);
        header("Location: invpckbin.php?FirstOpen=");
        }
    else
        {
        $_SESSION["Error"] = "Empty";
        header("Location: invpckbinscan.php?St_Location=");
        }

    } 
elseif(isset($_GET['Back']))
    {
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    $_SESSION["BoxCounter"] = PalCounter($_SESSION['InvNum'],$_SESSION['InvRnd']);
    // if scan pallets = 0 then delete row in NCI_INV   
    If($_SESSION['BoxCounter'] == 0)
        {
        $SQL = "DELETE FROM NCI_INV WHERE  ([InvNum] = :InvNum) AND  ([InvRnd] = :InvRnd)";
        $params = array('InvNum' => $_SESSION["InvNum"],'InvRnd' => $_SESSION['InvRnd']);
        $stmt = $Connection->execute($SQL,$params);
        header("Location: invpckbin.php?FirstOpen=");
        }
        unset( $_SESSION['St_Location']);
        unset( $_SESSION['Nd_Location']);
        unset($_SESSION['BoxID']);
        unset($_SESSION['Article']);
        unset($_SESSION['Quantity']);
        unset($_SESSION['PolishShit']);
        unset( $_SESSION['BoxCounter']);
        unset($_SESSION["InvNum"]);
        unset($_SESSION['InvRnd']);
        header("Location: invpckbin.php?FirstOpen=");    
    }

// filled field 'Varianta' for polish tabbaco
elseif (!empty($_GET['PolishShit']))
    {
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    if(isset($_SESSION['PolishShit']))    
        {
        // take EAN from BoxID function checkValue()
        $SQL = "SELECT [Code] FROM [Liquid].[dbo].[NCI_EAN] WHERE ([EAN_box] = :EAN)";
        $params = array('EAN' => $_SESSION["Article"]."/".$_GET['PolishShit']);
        $stmt = $Connection->select($SQL,$params);                    
        $count = $stmt['count'];
        if ($count==0)
            {
            $_SESSION["Error"] ="PolishShit";
            unset($_SESSION['BoxID']);
            invpckbinscan_main();
            }
        else
            {
            $_SESSION['Article'] = $_SESSION['Article']."/".$_GET['PolishShit'];
            $Quant = 1;
            $currentDateTime = new DateTime();
            $DateTime = $currentDateTime->format('Y-m-d H:i:s');
            $Pallet = $_SESSION['BoxID'];
            $EAN = $_SESSION['Article'];
            $St_location = $_SESSION['St_Location'];
            $Nd_location = $_SESSION['St_Location'];
            $UserID= $_SESSION['UserID'];
            $InvNum =$_SESSION["InvNum"];
            $InvRnd = $_SESSION["InvRnd"];  
            
            if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 

            // send scaned data to SQLSrv table NCI_Pallets_".$InvRnd
            $data = array( 'PalletID' => $Pallet, 'EAN' => $EAN,'Quantity' => $Quant,'St_Location' => $St_location,'Nd_Location' => $Nd_location,'Scantime' => $DateTime,'UserID' => $UserID, 'InvNum' => $InvNum, 'InvRnd' => $InvRnd);
            $Connection->insert("NCI_Pallets_".$InvRnd, $data);

            // SQL procedure fill fictitious ID for compering
            $SQL = "EXECUTE FillEmpty_".$InvRnd." @St_Location = :St_Location";
            $params = array('St_Location' =>  $St_location);
            $stmt = $Connection->execute($SQL, $params);
            $_SESSION["BoxCounter"] = PalCounter($_SESSION['InvNum'],$_SESSION['InvRnd']);
            unset($_SESSION['BoxID']);
            unset($_SESSION['Article']);
            unset($_SESSION['Quantity']);
            unset($_SESSION['PolishShit']);

            invpckbinscan_main();
            }
        }
    }

// display field for variant polish tobacco
elseif(isset($_SESSION['PolishShit']))
    {
    invpckbinscan_main();
    }

// filled field 'BoxID'
elseif(empty($_SESSION["BoxID"]))
    {
    // check BoxID number format and article          
    $check= checkValue($_GET["BoxID"],"BOX");
    if($check == false)
        {
        $_SESSION["Error"] ="BoxID";
        unset($_SESSION['BoxID']);
        header("Location: invpckbinscan.php?St_Location=");
        }
    else
        {        
        if(!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
        
        // Not Polish tabbaco select article
        if(!isset($_SESSION['PolishShit'])) 
            {           
            // take EAN from BoxID function checkValue()
            $SQL = "SELECT [Code] FROM [Liquid].[dbo].[NCI_EAN] WHERE ([EAN_box] = :EAN)";
            $params = array(':EAN' => $_GET["Article"]);
            $stmt = $Connection->select($SQL,$params);
            $count = $stmt['count'];
            if ($count == 0)
                {
                $_SESSION["Error"] ="EAN";
                unset($_SESSION['BoxID']);
                header("Location: invpckbinscan.php?St_Location=");
                }
            else
                {
            
                $_SESSION['BoxID'] =$_GET["BoxID"];
                $_SESSION['Article'] =$_GET["Article"];        
                $Quant = 1;
                $currentDateTime = new DateTime();
                $DateTime = $currentDateTime->format('Y-m-d H:i:s');
                $Pallet = $_SESSION['BoxID'];
                $EAN = $_SESSION['Article'];
                $St_location = $_SESSION['St_Location'];
                $Nd_location = $_SESSION['St_Location'];
                $UserID= $_SESSION['UserID'];
                $InvNum =$_SESSION["InvNum"];
                $InvRnd = $_SESSION["InvRnd"];  
                
                if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    
                // send scaned data to SQLSrv table NCI_Pallets_".$InvRnd
                $data = array( 'PalletID' => $Pallet, 'EAN' => $EAN,'Quantity' => $Quant,'St_Location' => $St_location,'Nd_Location' => $Nd_location,'Scantime' => $DateTime,'UserID' => $UserID, 'InvNum' => $InvNum, 'InvRnd' => $InvRnd);
                $Connection->insert("NCI_Pallets_".$InvRnd, $data);
    
                // SQL procedure fill fictitious ID for compering
                $SQL = "EXECUTE FillEmpty_".$InvRnd." @St_Location = :St_Location";
                $params = array('St_Location' =>  $St_location);
                $stmt = $Connection->execute($SQL, $params);
    
                unset($_SESSION['BoxID']);
                unset($_SESSION['Article']);
                unset($_SESSION['Quantity']);
                unset($_SESSION['PolishShit']);
    
                invpckbinscan_main();                      
                }
            }
        // Polish tabbaco - select article
        else
            {
            $SQL = "SELECT [Code] FROM [Liquid].[dbo].[NCI_EAN] WHERE (left([EAN_box],13) = :EAN)";
            $params = array(':EAN' => $_GET["Article"]);
            $stmt = $Connection->select($SQL,$params);                    
            $count = $stmt['count'];
            if ($count!==0)
                {
                $_SESSION['BoxID'] =$_GET["BoxID"];
                $_SESSION['Article'] =$_GET["Article"];    
                invpckbinscan_main();
                }
            else
                {
                $_SESSION["Error"] ="EAN";
                unset($_SESSION['Article']);
                header("Location: invpckbinscan.php?St_Location=");
                }

            }
        }
    }   
}

// form view function
function invpckbinscan_main()
{
    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "BoxID":
                echo '<span class="ErrorMsg">Nesprávný formát BoxID.</span>';
                break;
            case "Article":
                echo '<span class="ErrorMsg">Nesprávný formát artiklu.</span>';
                break;
            case "Quantity":
                echo '<span class="ErrorMsg">Nesprávný formát počtu beden.</span>';
                break;
            case "Save":
                echo '<span class="ErrorMsg">Nejsou data k uložení.</span>';
                break;
            case "EAN":
                echo '<span class="ErrorMsg">Neznámý EAN kód.</span>';
                break;
            case "PolishShit":
                echo '<span class="ErrorMsg">Nesprávná varianta tabáku.</span>';
                break;
            case "Empty":
                echo '<span class="ErrorMsg">Do této inventury jste již naskenovali palety, nelze již zadat prázdnou lokaci.</span>';
                break;
        }        
    unset($_SESSION["Error"]);
    }
    if (!isset($_SESSION['BoxID'])){$_SESSION['BoxID'] ="";}
        
    // pallets/box counter funtion
    $_SESSION["BoxCounter"] = PalCounter($_SESSION['InvNum'],$_SESSION['InvRnd']);

        // pc or mobile devices
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SESSION['Platform']) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($_SESSION['Platform'], 0, 4))) 
        {   
            echo    "<fieldset class='MOBI'>";
            echo    "<legend>Obsah lokace: ".$_SESSION['St_Location']."</legend><br>";
            echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
            echo    "<label for='BoxCounter' class='label-TradeIN'>Počet skenovaných beden: </label><br>";
            echo    "<input type='text' id='BoxCounter' name='BoxCounter'  value='" . $_SESSION['BoxCounter'] . "' disabled><br><br>";   
            echo    "<label for='BoxID' class='label-TradeIN'>BoxID:</label><br>";
            if ($_SESSION['BoxID'] =="")
            {
            echo    "<input type='text' id='BoxID' name='BoxID'  value='" . $_SESSION['BoxID'] . "' autofocus><br><br>";
            }
            else
            {
            echo    "<input type='text' id='BoxID' name='BoxID'  value='" . $_SESSION['BoxID'] . "' ><br><br>"; 
            }
            echo    "</form>";

            if (Isset($_SESSION['PolishShit']))
            {
            echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
            echo    "<label for='PolishShit' class='label-TradeIN'>Varianta tabáku: </label><br>";       
            echo    "<input type='text' id='PolishShit' name='PolishShit'  value='' autofocus><br><br>";
            echo    "</form>"; 
            }

            echo    "</fieldset><br>";
            echo    "<div class='responsive'>";
            echo    "<fieldset class='ButtonsMOBI'>";
            echo    "<form method='GET'><br>";
            echo    "<input type='submit' onclick='' class='ButtonMOBI' name='Empty' id='Empty' value='Prázdná lokace'>";
            echo    "<input type='submit' onclick='' class='ButtonMOBI' name='Back' id='Back' value='Zpět'>";
            echo    "</form>";
            echo    "</fieldset>";
            echo    "</div>";
        } 
        else 
        {
            echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
            echo    "<fieldset>";
            echo    "<legend>Obsah lokace: ".$_SESSION['St_Location']."</legend><br>";
            echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
            echo    "<label for='BoxCounter' class='label-TradeIN'>Počet skenovaných beden: </label><br>";
            echo    "<input type='text' id='BoxCounter' name='BoxCounter'  value='" . $_SESSION['BoxCounter'] . "' disabled><br><br>";       
            echo    "<label for='BoxID' class='label-TradeIN'>BoxID:</label><br>";
            if ($_SESSION['BoxID'] =="")
            {
            echo    "<input type='text' id='BoxID' name='BoxID'  value='" . $_SESSION['BoxID'] . "' autofocus><br><br>";
            }
            else
            {
            echo    "<input type='text' id='BoxID' name='BoxID'  value='" . $_SESSION['BoxID'] . "' ><br><br>";           
            }
            if (Isset($_SESSION['PolishShit']))
            {
            echo    "<label for='PolishShit' class='label-TradeIN'>Varianta tabáku: </label><br>";       
            echo    "<input type='text' id='PolishShit' name='PolishShit'  value='' autofocus><br><br>";
            }
            echo    "</form>";
            echo    "</fieldset><br>";
            echo    "<div class='responsive'>";
            echo    "<fieldset class='Buttons'>";
            echo    "<form method='GET'><br>";
            echo    "<input type='submit' onclick='' class='Button' name='Empty' id='Empty' value='Prázdná lokace'>";
            echo    "<input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'>";
            echo    "</form>";
            echo    "</fieldset>";
            echo    "</div>";
        }
}  

?>
</body>