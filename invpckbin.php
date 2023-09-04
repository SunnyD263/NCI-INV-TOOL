<!DOCTYPE html>
<html lang='cs'>

<head>
    <title>Inventura pickbin</title>
    <meta charset='UTF-8'>
    <meta name='author' content='Jan Sonbol' />
    <meta name='description' content='Inventura palet' />
     <link rel='stylesheet' type='text/css' href='css/style.css' />
    <link rel="icon" type="image/png" href="images/kn.png"/>
    <script src='https://code.jquery.com/jquery-3.6.4.js'
        integrity='sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E=' crossorigin='anonymous'>
    </script>
</head>

<body>
    <header>
    <?php require 'projectfunc.php'; ?>
    <?php require 'SQLconn.php'; ?>
    </header>
    <br>
<?php
session_start();
// check exist $_Session['UserID']
Login("Location: invpckbin.php?FirstOpen=");
if (isset($_GET['St_Location'])) {$_SESSION['St_Location'] = $_GET["St_Location"];}

If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    //open default form main.php
if (isset($_GET["FirstOpen"]))
    {
    invpckbin();
    unset($_SESSION["FirstOpen"]);
    }

    //button 'Zpět'
if(isset($_GET['Back']))
    {
    unset($_SESSION['St_Location']);
    header("Location: main.php");
    }   

    //fill field "Výchozí lokace"
if (isset($_SESSION["St_Location"]))
    {
        //check WH location format
    $check= checkSkl($_SESSION["St_Location"]);
    if($check == false)
        {
        $_SESSION["Error"] = "St_Location";
        unset($_SESSION["St_Location"]);
        header("Location: invpckbin.php?FirstOpen=");
        }
    else
        {
        // check St_Location using by InvRnd and InvNum in NCI_INV table
        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
        $SQL = "SELECT [InvNum], [Nd_Location],[InvRnd],[InvClose],[UserID] FROM [Liquid].[dbo].[NCI_INV] WHERE ([St_Location]= :St_Location) and
            InvRnd = (SELECT MAX(InvRnd) FROM [Liquid].[dbo].[NCI_INV] WHERE [St_Location]= :St_Location1 )";
        $St_location=$_SESSION['St_Location'];
        $Nd_location=$_SESSION['St_Location'];    
        $params = array('St_Location' => $St_location, 'St_Location1' => $St_location);
        $stmt = $Connection->select($SQL,$params);               
        $count = $stmt['count'];   
        $UserID = $_SESSION['UserID'];

        if($count !== 0)
            {
            $InvClose = $stmt['rows'][0]['InvClose'];
            $InvRnd = $stmt['rows'][0]['InvRnd'];
            $InvNum = $stmt['rows'][0]['InvNum'];
            $_SESSION['InvNum'] = $InvNum;
            $UserIDinv = $stmt['rows'][0]['UserID']; 
            }

        // create new InvNum
        if($count == 0)
            {
            $SQL = "SELECT MAX(InvNum) as MaxNum FROM [Liquid].[dbo].[NCI_INV]";
            $stmt = $Connection->select($SQL);
            $InvNum = $stmt['rows'][0]['MaxNum'];
            $InvNum = $InvNum + 1;
            $InvRnd = 1;
            $_SESSION['InvNum']=$InvNum;
            $_SESSION['InvRnd']=$InvRnd;
            $data = array('InvNum' => $InvNum, 'Nd_Location' => $Nd_location, 'InvRnd' => $InvRnd, 'St_Location' => $St_location, 'InvClose' => false,  'UserID' => $UserID);
            $Connection->insert('NCI_INV', $data);
            header("Location: invpckbinscan.php?St_Location=");
            }

            // use exist InvNum
        elseif ($count == 1 and $InvClose == false)               
            {
            // check InvNum opening same UserID   
            if($St_location == $stmt['rows'][0]['Nd_Location'] and  $UserID == $UserIDinv)
                {
                $_SESSION['InvNum']=$InvNum;
                $_SESSION['InvRnd']=$InvRnd;
                header("Location: invpckbinscan.php?St_Location=");
                }
            else
                {
                $_SESSION["Error"] = "OtherUsr_St";
                unset($_SESSION["St_Location"]);
                invpckbin();
                }
            }

            // create new InvRnd, use exist InvNum   
        elseif ($count == 1 and $InvClose == true) 
            {
            $SQL = "SELECT [InvNum], [Nd_Location],[InvRnd],[InvClose],[UserID] FROM [Liquid].[dbo].[NCI_INV] WHERE ([St_Location]<> :St_Location) and 
            [InvRnd] = :InvRnd and [InvNum] = :InvNum";
            $params = array('St_Location' => $St_location, 'InvRnd' => $InvRnd + 1, 'InvNum' => $InvNum);
            $stmt = $Connection->select($SQL,$params);                    
            $count1 = $stmt['count'];
            
            //check location not open another UserID
            if($count1 == 0 )
                {
                $_SESSION['InvNum']=$InvNum;
                $_SESSION['InvRnd']=$InvRnd + 1;
                $data = array('InvNum' => $InvNum, 'Nd_Location' => $Nd_location, 'InvRnd' => $InvRnd + 1, 'St_Location' => $St_location, 'InvClose' => false, 'UserID' => $UserID);
                $Connection->insert('NCI_INV', $data);
                header("Location: invpckbinscan.php?St_Location=");
                }
            else
                {
                $_SESSION["Error"] = "OpenInv_St";
                unset($_SESSION["St_Location"]);
                header("Location: invpckbin.php?FirstOpen=");
                }
            }     
        }
    }
}    


function checkSkl($value) {
    if (strlen($value) === 5 && preg_match('/^[P]/', $value) && substr($value, 2, 1) ==='-') 
    {
    return true;
    }
    elseif(substr($value,0,5) == "RAMPA" or substr($value, 0, 4) == 'GATE') 
    {
        return true;
    }
    else 
    {
    return false;
    }
}

// form function 
function invpckbin()
{ 
    
// error report writer    
if (isset($_SESSION["Error"])) 
{   
    if ($_SESSION["Error"]=="St_Location")
    {
    echo '<span class="ErrorMsg">Nesprávný formát výchozí skladové lokace.</span>';
    }
    if ($_SESSION["Error"]=="OpenInv_St")
    {
    echo '<span class="ErrorMsg">Do výchozí lokace již byla v tomto kole umístěna jiná inventura.</span>';
    }
    if ($_SESSION["Error"]=="OtherUsr_St")
    {
    echo '<span class="ErrorMsg">Inventuru výchozí lokace již provádí jiný uživatel.</span>';
    }
    unset($_SESSION["Error"]);
}

// pc or mobile devices
if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SESSION['Platform']) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($_SESSION['Platform'], 0, 4)))
    {   
        echo    "<fieldset class='MOBI'>";
        echo    "<form method='GET' onchange='this.submit()'>";
        echo    "<legend>Naskenuj lokace: </legend><br>";
        echo    "<label for='St_Location' class='label-TradeIN'>Výchozí lokace:</label><br>";
        echo    "<input type='text' id='St_Location' name='St_Location'  value='' autofocus><br><br>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "<div class='responsive'>";
        echo    "<fieldset class='ButtonsMOBI'>";
        echo    "<form method='GET'><br>";
        echo    "<input type='submit' onclick='' class='ButtonMOBI' name='Back' id='Back' value='Zpět'>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "</div>";
    }
    else 
    {
        echo    "<form method='GET' onchange='this.submit()'>";
        echo    "<fieldset>";
        echo    "<legend>Naskenuj lokace: </legend><br>";
        echo    "<label for='St_Location' class='label-TradeIN'>Výchozí lokace:</label><br>";
        echo    "<input type='text' id='St_Location' name='St_Location' value='' autofocus><br><br>";
        echo    "</fieldset><br>";
        echo    "</form>";
        echo    "<div class='responsive'>";
        echo    "<fieldset class='Buttons'>";
        echo    "<form method='GET'><br>";
        echo    "<input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "</div>";
    }    
}
?>
<script>
</script>
</body>