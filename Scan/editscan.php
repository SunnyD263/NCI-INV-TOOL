<!DOCTYPE html>
<html lang="cs">

<head>
    <title>Inventura palet</title>
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
if (!isset($_SESSION['currentDir'])){Find_Dir();} 
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 
require $_SESSION['currentDir']."\scan\scanFunc.php"; 
If ($_SERVER["REQUEST_METHOD"] == "POST") 
{
$Array = $_POST;
foreach ($Array as $Key => $Value) 
{
 $ID=$Key;
 $Quantity = $Value;
}
SQL_upd_row($ID,$_SESSION["InvRnd"], $Quantity);
header("Location: editscan.php?LoadForm=");
}
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
// Push button 'Zpět'
if(isset($_GET['Back']))
    {
    header("Location: InvPalScan.php?St_Location=");
    }     
elseif(isset($_GET['PalID']))
{
SQL_del_row($_GET['PalID'],$_SESSION["InvRnd"]);
header("Location: editscan.php?LoadForm=");
}
elseif(isset($_GET['Quantity']))
{

}
elseif(isset($_GET['LoadForm'])) 
    {

    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SESSION['Platform']) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($_SESSION['Platform'], 0, 4))) 
        {
        echo "<div class = 'Info-MOBI'>";
        echo  "<form method='GET'>";
        echo  "<button type='submit' name='Back' id='Back' value=''>Zpět</button><br><br>";
        }
    else
        {
        echo "<div class = 'Info'>";  
        echo  "<form method='GET'>";  
        echo  "<button type='submit' name='Back' id='Back' value=''>Zpět</button>";
        echo  "<button type='submit' name='LoadForm' value='' onclick='window.print()'>Tisk</button><br><br>";
        }
        echo  "</form>";

   // Detail stock-taking view 
   if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
   $SQL=  "SELECT [ID],[PalletID],[Code],[EAN],[Quantity] FROM [Liquid].[dbo].[NCI_INV_ScanEdit_View] WHERE ([InvNum] = :InvNum and [InvRnd] = :InvRnd) order by ID desc";
   $params = array('InvNum' => $_SESSION['InvNum'],'InvRnd' => $_SESSION['InvRnd']);
   $stmt = $Connection->select($SQL, $params);
   $count = $stmt['count'];
   $location =  $_SESSION["St_Location"] . "->" . $_SESSION["Nd_Location"];
   echo "<input type='text' id='Loc' name='Loc'  value='Lokace: ".$location ." - Inv: ". $_SESSION["InvNum"]." - Kolo: ".$_SESSION["InvRnd"]."'><br>"; 
   echo "<input type='text' id='Counter' name='Counter'  value='Počet záznamů:"  . $count . "'><br>";
   echo "<form method='POST' id='quantityInput' style='display: none;' onchange='this.submit()'>";
   echo "<label for='quantity'>Zadejte množství:</label>";
   echo "<input type='text' id='Quantity' name='Quantity' autofocus>";
   echo "</form>";
   if($count > 0)
   {
   $stmt = $Connection->select($SQL, $params);
   $rows = $stmt['rows'];
   $count = $stmt['count'];
   $columnNames = ['ID', 'PalletID','Artikl' ,'EAN' ,'PCE'];

   echo "<table id='data-table'>";
   echo '<tr>';
   $Border = array(0, 1, 4);
   $Number = array(0,1,4);
   for ($i = 0; $i < count($columnNames); $i++) 
       {            
       if (in_array($i, $Border))
           {
           echo "<th class='number-border' >" . $columnNames[$i] . '</th>';
           } 
       elseif ($i == 8)
           {
           echo "<th class='wide-border' >" . $columnNames[$i] . '</th>';
           }
       elseif (in_array($i, $Number))
           {
           echo "<th class='number' >" . $columnNames[$i] . '</th>';
           }
       else
           {
           echo '<th>' . $columnNames[$i] . '</th>';
           }    
       }
   foreach ($rows as $row) 
   {
       echo '<tr>';
       $Border = array("Quantity");
       $Number = array('ID', 'PalletID',"Quantity");
       foreach ($row as $key => $value) 
           {
            if ($key == "ID")
                {   
                $InvButtonID = $value;
                }
            if (in_array($key, $Border))
               {
                echo    "<td>";
                echo    "<button type='submit' name='QuantityID' id='QuantityID' value='".$InvButtonID."' onclick='showQuantityInput(".$InvButtonID.")'>$value</button>";
                echo    "</td>";
               } 
            elseif ($key == "PalletID")
               {
                echo    "<td>";
                echo    "<form method='GET'>";
                echo    "<button  type='submit' name='PalID' id='PalID' value=".$InvButtonID." style='background-color: red'>$value</button>";
                echo    "</form>";
                echo    "</td>";
               }
            elseif (in_array($key, $Number))
               {
               echo "<td class='number'>" . $value . '</td>';
               }
            else
               {
               echo '<td>' . $value . '</td>';
               }   
           }


       echo '</tr>';
    }
    echo "</table>";
    echo "</div>";
    echo "<br>";
    }
}
}
?>
<script>
  // Funkce pro zobrazení vstupního pole a zachycení hodnoty
  function showQuantityInput(invButtonID) {
    // Získání buňky tabulky, do které chcete vložit hodnotu
    var cell = document.querySelector(`button[value="${invButtonID}"]`).parentNode;
    
    // Zobrazení vstupního pole
    var quantityInput = document.getElementById("quantityInput");
    quantityInput.style.display = "block";   
    var quantityOutput = document.getElementById("Quantity");
    quantityOutput.name = invButtonID;
  }


  window.onload = function() {
  // Funkce pro aktualizaci šířky <fieldset> na základě šířky tabulky
  function updateFieldsetWidth() {
    var fieldset = document.getElementById('quantityInput');
    var table = document.getElementById('data-table');

    // Získání šířky <tbody> tabulky
    var tbodyWidth = table.getElementsByTagName('tbody')[0].offsetWidth;

    // Nastavení šířky <fieldset> na základě šířky tabulky
    fieldset.style.width = tbodyWidth + 'px';
    fieldset.style.font = "25px";
    fieldset.style.hight="200px";
  }

  // Zavolání funkce při načtení stránky a při změně velikosti okna (pokud je tabulka responzivní)
  updateFieldsetWidth();
  window.addEventListener('resize', updateFieldsetWidth);
};

</script>

</body>