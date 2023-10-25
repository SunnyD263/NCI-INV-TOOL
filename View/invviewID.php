<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Souhrn inventury ID</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Nučice" />
        <link rel="stylesheet" type="text/css" href="/nci/view/style.css" />
        <link rel="icon" type="image/png" href="images/kn.png">
        <script
            src="https://code.jquery.com/jquery-3.6.4.js"
            integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
            crossorigin="anonymous">
        </script>
    </head>

    <body>
        <header>
        <h2>Souhrn inventury id</h2>
        </header>    
<?php
session_start();
if (!isset($_SESSION['currentDir'])){Find_Dir();}  
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 
require 'invviewFunc.php';
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    if (isset($_GET['LoadForm'])) 
    {
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        View();
    }
    //choice from combobox stat inv lokace
    if (isset($_GET['InvSummary']))
    {           
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}

        switch($_GET['InvSummary'])
        {
        case "All":
            $_SESSION['InvSummary'] ="All";    
            break;
        case "Empty":
            $_SESSION['InvSummary']="Empty";    
            break;
        case "INV":
            $_SESSION['InvSummary']="INV";    
            break;        
        case "INV_Diff": 
            $_SESSION['InvSummary']="INV_Diff";
            break;
        case "INV_NoDiff":
            $_SESSION['InvSummary']="INV_NoDiff";   
            break;
        }
        header("Location: invviewID.php?LoadForm=");
    }
    //choice from combobox lokace
    if (isset($_GET['LocSlct']))
        {      
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 1;}
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        $_SESSION['LocSlct'] = $_GET['LocSlct'];
        header("Location: invviewID.php?LoadForm=");
        }
    //push button "přehled lokace"
    if (isset($_GET['Summary']))
        {
        Loc_detail();
        }
    //push button "Zpět"
    if (isset($_GET['Back']))
    {
        //from Locations detail
        if ($_GET['Back'] == 'InvSummary'  )
        {
        header("Location: invviewID.php?LoadForm=");
        }     
        //from Summary
        if ($_GET['Back'] == 'Menu'  )
        {
        unset($_SESSION["InvSummary"]);
        unset($_SESSION["Round"]);
        unset($_SESSION["LocSlct"]);
        header("Location: \\nci\\menu\main.php");
        }
    }
}

// form view function
function View()
{
    $stmt = Combobox('ID');
    // records counter
    $count = $stmt['count'];
    echo "Počet záznamů: " . $count . "<br>";

    // table creator    
    if ($count !== 0)
    {
        $rows = $stmt['rows'];           
        $columnNames = ['Číslo inventury', 'Lokace Inv1','Počet ID Inv1' ,'Množství Inv1',  'Lokace Inv2','Počet ID Inv2' ,'Množství Inv2', 'Lokace Inv3','Počet ID Inv3' ,'Množství Inv3', 'Lokace po Inv3'] ;
        echo '<table>';
        echo '<tr>';

        $Border = array( 1,3,4,6,7,9,10);
        for ($i = 0; $i < count($columnNames); $i++) {
            if (in_array($i, $Border))
            {
            echo "<th class='number' >" . $columnNames[$i] . '</th>';
            } 
            else
            {
            echo '<th>' . $columnNames[$i] . '</th>';
            }

        }
        echo '</tr>';
        $Border = array("St_Location_Rnd1","ID_Rnd1","Quant_Rnd1","St_Location_Rnd3","ID_Rnd3","Quant_Rnd3");
        $Border1 = array("St_Location_Rnd1","St_Location_Rnd2","St_Location_Rnd3");
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) 
            {
                if (in_array($key, $Border))
                    {
                    echo "<td class='number'>" . $value . '</td>';
                    }
                else
                    {
                    echo "<td class='wide-border' >" . $value . '</td>';
                    }
                if ($key == "InvNum")
                    {   
                    $LocButtonID = $value;
                    }
            }
            echo    "<td>";
            echo    "<form method='GET'>";
            echo    "<button class='button_row' type='submit' name='Summary' id='Summary' value='".$LocButtonID."' >Přehled lokace</button>";
            echo    "</form>";
            echo    "</td>";
            echo '</tr>';
        }
        echo "</table>"; 
    }    
}


function Loc_detail()
{
    echo  "<div class='headButton'>";
    echo  "<form method='GET'>";
    echo  "<button type='submit' name='Back' id='Back' value='InvSummary' >Zpět</button><br><br>";
    echo  "</form>";
    echo  "</div>";

    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}

    $SQL = "EXECUTE NCI_ID_detail @InvNum = :InvNum";
    $params = array('InvNum' => $_GET['Summary']);
    $stmt = $Connection->execute($SQL, $params);

    $SQL=  "SELECT [InvNum], [ID_Rnd1],[Quant_Rnd1],[ID_Rnd2],[Quant_Rnd2],[ID_Rnd3],[Quant_Rnd3] FROM [Liquid].[dbo].[NCI_ID_Details] 
            where ([InvNum] = :InvNum) order by ID_Rnd1,ID_Rnd2,ID_Rnd3";
    $params = array('InvNum' => $_GET['Summary']);
    $stmt = $Connection->select($SQL, $params);
    $count = $stmt['count'];


    echo "<div class='smallheader'>";
    echo "Srovnání id inventur<br>";
    echo "</div>";

    $columnNames = ['Číslo inventury', 'ID Inventury 1','Množství Inv 1' ,'ID Inventury 2','Množství Inv 2','ID Inventury 3','Množství Inv 3'];
    echo '<table>';
    if($count !== 0)
    {
    $rows = $stmt['rows']; 
    

        echo '<tr>';
        for ($i = 0; $i < count($columnNames); $i++) 
        {            
            echo '<th>' . $columnNames[$i] . '</th>';            
        }
        $Border = array("InvNum","ID_Rnd2","Quant_Rnd2");
        foreach ($rows as $row) 
        {
            echo '<tr>';
            foreach ($row as $key => $value) 
            {

                if (in_array($key, $Border))
                {
                echo "<td class='number'>" . $value . '</td>';
                }
                else
                {
                echo "<td class='wide-border' >" . $value . '</td>';
                }

            }
            echo '</tr>';
        }

    }
    echo "</table>";
    echo "<br>";
}

?>
</body>
