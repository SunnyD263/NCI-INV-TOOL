<!DOCTYPE html>
<html lang="cs">

<head>
    <title>ArtInfo</title>
    <meta charset="UTF-8">
    <meta name="author" content="Jan Sonbol" />
    <meta name="description" content="ArtInfo" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="icon" type="image/png" href="images/kn.png"/>
    <script src="https://code.jquery.com/jquery-3.6.4.js"
        integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E=" crossorigin="anonymous">
    </script>
</head>

<body>
    <header>
    <?php require 'projectfunc.php'; ?>
    <?php require 'SQLconn.php'; 
    ?>
    </header>
    <br>
<?php
session_start();
Login("Location: infoarticle.php?FirstOpen=");
$txtpath = "c:\\xampp\htdocs\NCI\\var_ext\NCI_Info_import.txt";
$items = explode(';', file_get_contents($txtpath));
$startTime = strtotime($items[1]) * 1000;
$nowTime =  strtotime(date('Y-m-d H:i:s')) * 1000;
$elapsedTime=$nowTime-$startTime;
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    if (isset($_GET['FirstOpen']))
    {      
    if (!isset($_SESSION['Article'])) {$_SESSION['Article'] = '';}
    ArticleInfo();
    }
    elseif(isset($_GET['Article']))
    {
    $check= checkValue($_GET['Article'],"INFO");
    if($check == false)
        {
        $_SESSION["Error"] ="Article";
        unset($_SESSION['Article']);
        header("Location: infoarticle.php?FirstOpen=");
        }
        else
        {
        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
            if(!isset($_SESSION['PolishShit']))    
            {
            $count= EANinfo($_GET['Article'],$_SESSION["EAN_format"]);

                if ($count!==0)
                {
                $_SESSION['Article'] =$_GET["Article"];    
                header("Location: infoview.php?FirstOpen=");
                }
                else
                {
                $_SESSION["Error"] ="EAN";
                unset($_SESSION['Article']);
                header("Location: infoarticle.php?FirstOpen=");
                }
            }
            else
            {
            $SQL = "SELECT [Code] FROM [Liquid].[dbo].[NCI_EAN] WHERE (left([EAN_box],13) = :EAN)";
            $params = array('EAN' => $_GET["Article"]);
            $stmt = $Connection->select($SQL,$params);                    
            $count = $stmt['count'];
                if ($count!==0)
                {
                $_SESSION['Article'] =$_GET["Article"];  
                ArticleInfo(); 
                }
                else
                {
                $_SESSION["Error"] ="EAN";
                unset($_SESSION['Article']);                
                unset($_SESSION['PolishShit']);
                header("Location: infoarticle.php?FirstOpen=");
                }
            }
        }    
    }
    elseif(isset($_GET['PolishShit']))
    {
    // take EAN from BoxID function checkValue()
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    $SQL = "SELECT [Code] FROM [Liquid].[dbo].[NCI_EAN] WHERE ([EAN_box] = :EAN)";
    $params = array('EAN' => $_SESSION["Article"]."/".$_GET['PolishShit']);
    $stmt = $Connection->select($SQL,$params);                    
    $count = $stmt['count'];
    if ($count==0)
        {
        $_SESSION["Error"] ="PolishShit";
        ArticleInfo();
        }
    else
        {
        $_SESSION['Article'] = $_SESSION['Article']."/".$_GET['PolishShit'];
        $count= EANinfo($_SESSION['Article'],$_SESSION["EAN_format"]);
        if ($count!==0)
            {
            header("Location: infoview.php?FirstOpen=");
            unset($_SESSION['PolishShit']);
            }
        else
            {
            $_SESSION["Error"] ="EAN";
            unset($_SESSION['Article']);
            header("Location: infoarticle.php?FirstOpen=");
            unset($_SESSION['PolishShit']);
            ArticleInfo();
            }
        }
    }
    elseif(isset($_GET['Back']))
    {
    unset($_SESSION["Code"]);
    unset($_SESSION["Description"]);
    unset($_SESSION["Box"]);
    unset($_SESSION["Crt"]);
    unset($_SESSION["Pck"]);
    unset($_SESSION["EAN_box"]);
    unset($_SESSION["EAN_crt"]);
    unset($_SESSION["EAN_pck"]);
    unset($_SESSION["Packaging"]);
    unset($_SESSION['Article']);
    header("Location: main.php");
    }
}
Function ArticleInfo()
{
    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "PalletID":
                echo '<span class="ErrorMsg">Nesprávný formát PalletID.</span>';
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
        }        
    unset($_SESSION["Error"]);
    }
if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SESSION['Platform']) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($_SESSION['Platform'], 0, 4))) 
    {   
    echo    "<div class='MOBI'>";
    echo    "<div class='Timer-MOBI' id='timer'>00:00</div>";
    echo    "<fieldset>";
    if (!Isset($_SESSION['PolishShit']))
    {
    echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
    echo    "<label for='ArticleInfo' class='label-TradeIN'>Naskenuj EAN artiklu: </label><br>";
    echo    "<input type='text' id='Article' name='Article'  value='" . $_SESSION['Article'] . "' autofocus><br><br>";       
    echo    "</form>";
    }
    else
    {
    echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
    echo    "<label for='ArticleInfo' class='label-TradeIN'>Naskenuj EAN artiklu: </label><br>";
    echo    "<input type='text' id='Article' name='Article'  value='" . $_SESSION['Article'] . "'><br><br>";       
    echo    "</form>";
    echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
    echo    "<label for='PolishShit' class='label-TradeIN'>Varianta tabáku: </label><br>";       
    echo    "<input type='text' id='PolishShit' name='PolishShit'  value='' autofocus><br><br>";
    echo    "</form>";     
    }            
    echo    "</fieldset><br>";
    echo    "</div>";
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
    echo    "<div class='Timer' id='timer'>00:00</div>";
    echo    "<fieldset>";
    echo    "<form method='GET' id='FormField'  onchange='this.submit()'>";
    echo    "<label for='ArticleInfo' class='label-TradeIN'>Naskenuj EAN artiklu: </label><br>";
    echo    "<input type='text' id='Article' name='Article'  value='" .  $_SESSION['Article'] . "' autofocus><br><br>";
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
    var timerElement = document.getElementById('timer');
    var elapsedTime = <?php echo $elapsedTime?>;


    function updateTimer() {

        var hours = Math.floor(elapsedTime / 3600000);
        var minutes = Math.floor((elapsedTime % 3600000) / 60000);
        var seconds = Math.floor((elapsedTime % 60000) / 1000);

        var hoursStr = hours.toString().padStart(2, '0');
        var minutesStr = minutes.toString().padStart(2, '0');
        var secondsStr = seconds.toString().padStart(2, '0');

        timerElement.innerHTML = 'Čas od poslední aktualizace: ' + hoursStr + ':' + minutesStr + ':' + secondsStr;
        elapsedTime += 1000 
        setTimeout(updateTimer, 1000); 
    }

    updateTimer(); 
</script>
</body>