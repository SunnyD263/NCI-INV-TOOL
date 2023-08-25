<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Souhrn inventury ID</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Nučice" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
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
        <?php require 'projectfunc.php'; ?>
        <?php require 'SQLconn.php'; ?>
        </header>    
<?php
session_start(); 
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    if (isset($_GET['LoadForm'])) 
    {
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        Combobox();
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
        header("Location: main.php");
        }
    }
}

// form view function
function Combobox()
{
    // Error messages
    if (isset($_SESSION["Error"])) 
    {   
        switch ($_SESSION["Error"]) 
        {
            case "NoOpen":
                echo '<span class="ErrorMsg">U této lokace nebylo ještě uloženo skenování.</span>';
                break;
            case "NoExist":
                echo '<span class="ErrorMsg">Tuto lokaci ještě nikdo nezačal skenovat.</span>';
                break;
            case "LocOpened":
                echo '<span class="DoneMsg">Lokace byla otevřena pro inventuru.</span>';
                break;
            case "LocClosed":
                echo '<span class="DoneMsg">Lokace byla uzavřena pro inventuru.</span>';
                break;
        }        
    unset($_SESSION["Error"]);
    }

    echo  "<form method='GET'>";
    echo  "<button type='submit' class='headButton' name='Back' id='Back' value='Menu' >Zpět</button><br><br>";
    echo    "</form>";

    if (!isset($Connection)) {
        $Connection = new PDOConnect("Liquid");
    }

    // Combobox 'Lokace'
    echo "<fieldset class='InvView'>";
    echo "<legend>Filtr:</legend>";
    echo "<form method='GET'>";
    echo "<label for='LocSlct'>Lokace:</label>";
    echo "<select name='LocSlct' ID='LocSlct' onchange='this.form.submit()'>";

    // Combobox content
    $SQL = "SELECT Location FROM 
    (SELECT CASE
        WHEN LEN(KPWHLO) = 3 THEN LEFT(KPWHLO, 1)
        WHEN LEN(KPWHLO) = 5 AND SUBSTRING(KPWHLO, 3, 1) = '-' THEN LEFT(KPWHLO, 2)
		END AS Location FROM dbo.NCI_Location
        GROUP BY CASE WHEN LEN(KPWHLO) = 3 THEN LEFT(KPWHLO, 1) WHEN LEN(KPWHLO) = 5 AND SUBSTRING(KPWHLO, 3, 1) = '-' THEN LEFT(KPWHLO, 2) END
    ) AS Subquery WHERE Location IS NOT NULL";

    $stmt = $Connection->select($SQL);
    $rows = $stmt['rows'];
    $count = $stmt['count'];
    
    if($_SESSION['LocSlct'] == '')    
        {
        echo "<option id='' value='' selected>Všechny</option>";
        }
    else
        {
        echo "<option id='' value='' >Všechny</option>";   
        }

    foreach ($rows as $row) 
        {
        if($row['Location'] !== $_SESSION['LocSlct'] )
            {
            echo "<option id=" . $row['Location'] . " value=" . $row['Location'] . ">" . $row['Location'] . "</option>";
            }
            else
            {
            echo "<option id=" . $_SESSION['LocSlct'] . " value='" . $_SESSION['LocSlct'] . "' selected>" . $_SESSION['LocSlct'] . "</option>";
            }
        }

    // Combobox 'Stav Inv lokace'
    echo "</select><br><br>";
    echo "</form>";
    echo "<form method='GET'>";
    echo "<label for='InvSummary'>Stav Inv lokace:</label>";
    echo "<select name='InvSummary' ID='InvSummary' onchange='this.form.submit()'>";
    switch($_SESSION['InvSummary'])
    { 
    // All locations stock-taking    
    case "All":
        $SQL=  "SELECT [InvNum],[St_Location_Rnd1],[ID_Rnd1],[Quant_Rnd1],[St_Location_Rnd2],[ID_Rnd2],[Quant_Rnd2],[St_Location_Rnd3],[ID_Rnd3],[Quant_Rnd3],[Nd_Location_Rnd3]
                FROM [Liquid].[dbo].[NCI_INV_Summary_ID_View] WHERE  ([St_Location_Rnd1] like :LocSlct) order by St_Location_Rnd1 ";
                $params = array('LocSlct' => $_SESSION['LocSlct'].'%');
                $stmt = $Connection->select($SQL, $params);
        echo "<option id='All' value='All' selected>Všechny</option>";
        echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
        echo "<option id='INV' value='INV' >Pozice s INV</option>";
        echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
        echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
        break;

    // Locations without stock-taking      
    case "Empty":    
        $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[NCI_INV_summary_view] 
                WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is null and CIEL_Quant <> 0   order by St_Location ";
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "6");
                $stmt = $Connection->select($SQL, $params);
        echo "<option id='All' value='All' >Všechny</option>";
        echo "<option id='Empty' value='Empty'selected>Pozice bez INV</option>";
        echo "<option id='INV' value='INV' >Pozice s INV</option>";
        echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
        echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
        break;

    // Locations with stock-taking  
    case  "INV":   
        $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[NCI_INV_summary_view]
                WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null order by St_Location ";
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "6");
                $stmt = $Connection->select($SQL, $params);
        echo "<option id='All' value='All' >Všechny</option>";
        echo "<option id='Empty' value='Empty'>Pozice bez INV</option>";
        echo "<option id='INV' value='INV' selected>Pozice s INV</option>";
        echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
        echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
        break;

    // Location with stock-taking a without some differences  
    case "INV_Diff":    
        $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[NCI_INV_summary_view]
                WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null and RowScan <> RowCiel or 
                ([InvRnd] = :InvRnd1) AND ([St_Location] like :LocSlct1) and (len([St_Location]) < :CharCount1) and Nd_Location is not null and Difference <> 0 order by St_Location";
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "6",'InvRnd1' => $_SESSION["Round"],'LocSlct1' => $_SESSION['LocSlct'].'%','CharCount1' => "6");
                $stmt = $Connection->select($SQL, $params);
        echo "<option id='All' value='All' >Všechny</option>";
        echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
        echo "<option id='INV' value='INV' >Pozice s INV</option>";
        echo "<option id='INV_Diff' value='INV_Diff' selected>Pozice s INV a differencí</option>";
        echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
        break;

    // Location without stock-taking and without some differences  
    case  "INV_NoDiff":    
        $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[NCI_INV_summary_view] 
                WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null and RowScan = RowCiel and Difference = 0 order by St_Location ";
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "6");
                $stmt = $Connection->select($SQL, $params);
        echo "<option id='All' value='All'>Všechny</option>";
        echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
        echo "<option id='INV' value='INV' >Pozice s INV</option>";
        echo "<option id='INV_Diff' value='INV_Diff'>Pozice s INV a differencí</option>";
        echo "<option id='INV_NoDiff' value='INV_NoDiff' selected>Pozice s INV bez differencí</option>";
        break;
    }
    echo "</select>";
    echo "</form><br>";
    echo "</fieldset><br>";


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
            foreach ($row as $key => $value) {

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
    echo  "<form method='GET'>";
    echo  "<button type='submit' class='headButton' name='Back' id='Back' value='InvSummary' >Zpět</button><br><br>";
    echo    "</form>";

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
