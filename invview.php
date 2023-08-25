<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Souhrn inventury lokací</title>
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
        <h2>Souhrn inventury lokací</h2>
        <?php require 'projectfunc.php'; ?>
        <?php require 'SQLconn.php'; ?>
        </header>    
<?php
session_start(); 
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    // default view form
    if (isset($_GET['LoadForm'])) 
    {
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 1;}
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        Combobox();
    }

    // button 'uzavřít inventury'
    if (isset($_GET["InvClose"]))
    {
        InvClose();
        Combobox();
    }

    // Choice from combobox 'Stav Inv lokace'
    if (isset($_GET['InvSummary']))
    {           
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 1;}
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
        header("Location: invview.php?LoadForm=");
    }

    // Choice from combobox 'Kolo inventury'
    if (isset($_GET['Round']))
        {   
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        $_SESSION["Round"] = $_GET['Round'];
        header("Location: invview.php?LoadForm=");
        }

    // Choice from combobox 'Lokace'    
    if (isset($_GET['LocSlct']))
        {      
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 1;}
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        $_SESSION['LocSlct'] = $_GET['LocSlct'];
        header("Location: invview.php?LoadForm=");
        }

    // Push button 'Přehled lokace'
    if (isset($_GET['Summary']))
        {
        Loc_detail();
        }

    // Push button 'Zpět'
    if (isset($_GET['Back']))
    {
        //from Locations detail
        if ($_GET['Back'] == 'InvSummary'  )
        {
        header("Location: invview.php?LoadForm=");
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

    // Push button 'Kolo O/Z' 
    if(isset($_GET['OpenLoc']))
    {
        if ($_GET['OpenLoc'] == "")
            {
                $_SESSION['Error'] = "NoExist";
                header("Location: invview.php?LoadForm=");
            }
        else
            {
            if (!isset($Connection)) {$Connection = new PDOConnect("Liquid");}
            $SQL=  "Select [InvClose] from [dbo].[NCI_INV]  WHERE ([InvNum] = :InvNum) and ([InvRnd] = :InvRnd)";
            $params = array('InvRnd' => $_SESSION["Round"],'InvNum' =>$_GET['OpenLoc']);
            $stmt = $Connection->select($SQL, $params);
            $count = $stmt['count'];

            if ($count == 0) 
                {
                    $_SESSION['Error'] = "NoOpen";
                    header("Location: invview.php?LoadForm=");
                }
            else
                {
                if($stmt['rows'][0]['InvClose']== 0)
                    { 
                    $InvClose = 1;
                    $_SESSION["Error"] = 'LocClosed';
                    }
                elseif($stmt['rows'][0]['InvClose']== 1)
                    {
                    $InvClose = 0;
                    $_SESSION["Error"] = 'LocOpened';      
                    }


            $SQL=  "UPDATE [dbo].[NCI_INV] SET [InvClose] = :InvClose WHERE ([InvNum] = :InvNum) and ([InvRnd] = :InvRnd) ";
            $params = array('InvClose' => $InvClose, 'InvNum' => $_GET['OpenLoc'] ,'InvRnd' => $_SESSION["Round"] );  
            $upd = $Connection->update($SQL,$params);

            header("Location: invview.php?LoadForm=");
            }
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
                echo '<span class="ErrorMsg">U tétolokace nebylo ještě uloženo skenování.</span>';
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
    // Combobox 'Kolo inventury'
    echo "<fieldset class='InvView'>";
    echo "<legend>Filtr:</legend>";
    echo "<form method='GET'>";
    echo "<label for='Round'>Kolo inventury:</label>";
    echo "<select name='Round' ID='Round' onchange='this.form.submit()'>";
        switch($_SESSION["Round"])
            {
            case 1:
                echo "<option id='R1' value= '1' selected>Kolo 1</option>";
                echo "<option id='R2' value= '2' >Kolo 2</option>";
                echo "<option id='R3' value= '3' >Kolo 3</option>";   
                break;
            case 2:
                echo "<option id='R1' value= '1' >Kolo 1</option>";
                echo "<option id='R2' value= '2' selected>Kolo 2</option>";
                echo "<option id='R3' value= '3' >Kolo 3</option>";
                break;
            case 3:
                echo "<option id='R1' value= '1' >Kolo 1</option>";
                echo "<option id='R2' value= '2' >Kolo 2</option>";
                echo "<option id='R3' value= '3' selected>Kolo 3</option>";
                break;
            }

    echo "</select><br><br>";
    echo "</form>";

    // Combobox 'Lokace'
    echo "<form method='GET'>";
    echo "<label for='LocSlct'>Lokace:</label>";
    echo "<select name='LocSlct' ID='LocSlct' onchange='this.form.submit()'>";

    // Combobox content
    $SQL = "SELECT Location FROM 
    (SELECT CASE
        WHEN LEN(KPWHLO) = 3 THEN LEFT(KPWHLO, 1)
        WHEN LEN(KPWHLO) = 5 AND SUBSTRING(KPWHLO, 3, 1) = '-' THEN LEFT(KPWHLO, 2)
        ELSE LEFT(KPWHLO, 2)
		END AS Location FROM dbo.NCI_Location
        GROUP BY CASE WHEN LEN(KPWHLO) = 3 THEN LEFT(KPWHLO, 1) WHEN LEN(KPWHLO) = 5 AND SUBSTRING(KPWHLO, 3, 1) = '-' THEN LEFT(KPWHLO, 2) ELSE LEFT(KPWHLO, 2) END
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

    
    echo "</select><br><br>";
    echo "</form>";

    // Combobox 'Stav Inv lokace'
    echo "<form method='GET'>";
    echo "<label for='InvSummary'>Stav Inv lokace:</label>";
    echo "<select name='InvSummary' ID='InvSummary' onchange='this.form.submit()'>";
    $Cmd = "NCI_INV_summary_".$_SESSION['Round']."_view";   

    switch($_SESSION['InvSummary'])
    { 
 
    // All locations stock-taking    
    case "All":
        $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose] FROM [Liquid].[dbo].[".$Cmd."] 
                WHERE  ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) order by St_Location ";
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "8");
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
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "8");
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
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "8");
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
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "8",'InvRnd1' => $_SESSION["Round"],'LocSlct1' => $_SESSION['LocSlct'].'%','CharCount1' => "8");
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
                $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "7");
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
    echo "<form method='GET'>";
    echo "<button type='submit' class='ButtonHeader' name='InvClose' id='InvClose' value='' >Uzavřít inventury</button>";
    echo "</form>";
    echo "</fieldset><br>";


    // records counter
    $count = $stmt['count'];
    echo "Počet záznamů: " . $count . "<br>";
    if ($count !== 0)
    {
        $rows = $stmt['rows'];
    
    // table creator    
        $columnNames = ['Původní lokace', 'Nová lokace', 'Součet lokace Ciel', 'Součet lokace Scan', 'Rozdíl','Skenující' ,'InvNum','InvRnd','Stav kola','Detail lokace','Správa INV'] ;
        echo '<table>';
        echo '<tr>';
        for ($i = 0; $i < count($columnNames); $i++) {
            echo '<th>' . $columnNames[$i] . '</th>';
        }
        echo '</tr>';
        $Border = array("CIEL_Quant","Scan_Quant","Difference","InvNum","InvRnd");
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) {
                if (in_array($key, $Border))
                {
                echo "<td class='number'>" . $value . '</td>';
                }
                else
                {
                echo '<td>' . $value . '</td>';
                }
                if ($key == "St_Location")
                {   
                $LocButtonID = $value;
                }
                if ($key == "InvNum")
                {   
                $InvButtonID = $value;
                }

            }
            echo    "<td>";
            echo    "<form method='GET'>";
            echo    "<button class='button_row' type='submit' name='Summary' id='Summary' value='".$LocButtonID."' >Přehled lokace</button>";
            echo    "</form>";
            echo    "</td>";
            echo    "<td>";
            echo    "<form method='GET'>";
            echo    "<button class='button_row' type='submit' name='OpenLoc' id='OpenLoc' value=".$InvButtonID." >Kolo O/Z</button>";
            echo    "</form>";
            echo    "</td>";

            echo '</tr>';
        }
        echo "</table>"; 
    }    
}

// form view function
function Loc_detail()
{
    echo  "<form method='GET'>";
    echo  "<button type='submit' class='headButton' name='Back' id='Back' value='InvSummary' >Zpět</button><br><br>";
    echo    "</form>";

    // Detail stock-taking view 
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
    $SQL=  "SELECT  max([InvNum]) as InvNum,[Nd_Location], max(InvRnd) as InvRnd FROM [Liquid].[dbo].[NCI_INV] where ([St_Location] = :St_Location and [InvRnd] = :InvRnd ) group by Nd_Location";
    $params = array('St_Location' => $_GET['Summary'],'InvRnd' => $_SESSION['Round']);
    $stmt = $Connection->select($SQL, $params);
    $count = $stmt['count'];
    $table = "NCI_compare_".$_SESSION['Round']."_View";
    if($count > 0)
    {
    $InvNum= $stmt['rows'][0]['InvNum'];
    $Nd_Location = $stmt['rows'][0]['Nd_Location'];	
    $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[".$table."] where ([InvNum] = :InvNum) order by  Article";
    $params = array('InvNum' => $InvNum);
    }
    else
    {
    $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[".$table."] where ([St_Location] = :St_Location) order by Article";
    $params = array('St_Location' => $_GET['Summary']);
    }

    $stmt = $Connection->select($SQL, $params);
    $rows = $stmt['rows'];
    $count = $stmt['count'];
    echo "<div class='smallheader'>";
    echo "Aktuální skenování<br>";
    echo "</div>";

    $columnNames = ['Původní lokace', 'Nová lokace','Materiál CIEL' ,'Popis zboží CIEL' ,'Lokace Ciel','Materiál SCAN' ,'Popis zboží SCAN', 'Lokace SCAN', 'Rozdíl','ScanTime' ,'Id skenujícího','Id inv', 'Id kola'];
    echo '<table>';
    echo '<tr>';
    $Border = array( 4, 7);
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
        else
        {
        echo '<th>' . $columnNames[$i] . '</th>';
        }
        
    }
    $Border = array("CIEL_Quant","Scan_Quant");
    foreach ($rows as $row) 
    {
        echo '<tr>';
        foreach ($row as $key => $value) 
        {

            if (in_array($key, $Border))
            {
            echo "<td class='number-border'>" . $value . '</td>';
            }
            elseif ($key=="Difference")
            {
            echo "<td class='wide-border' >" . $value . '</td>';
            }
            else
            {
            echo '<td>' . $value . '</td>';
            }

        }
        echo '</tr>';
    }
    echo "</table>";
    echo "<br>";

    // Detail all stock-taking round by InvNum 
    if($count !== 0 and !empty($InvNum))
    {
        $Nd_Location = $stmt['rows'][0]['Nd_Location'];	
        $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[NCI_compare_View] where ([InvNum] = :InvNum) order by  Article,InvRnd";
        $params = array('InvNum' => $InvNum);
        $stmt = $Connection->select($SQL, $params);
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        
        if($count !== 0)
        {
            echo "<div class='smallheader'>";
            echo "Předchozí skenování<br>";
            echo "</div>";
            
            $columnNames = ['Původní lokace', 'Nová lokace','Materiál CIEL' ,'Popis zboží CIEL' ,'Lokace Ciel','Materiál SCAN' ,'Popis zboží SCAN', 'Lokace SCAN', 'Rozdíl','ScanTime' ,'Id skenujícího','Id inv', 'Id kola'];
            echo '<table>';
            echo '<tr>';
            $Border = array( 4, 7);
            for ($i = 0; $i < count($columnNames); $i++) 
            {            
                if (in_array($i, $Border))
                {
                echo "<th class='number-border' >" . $columnNames[$i] . '</th>';
                } 
                elseif ($i==8)
                {
                echo "<th class='wide-border' >" . $columnNames[$i] . '</th>';
                }
                else
                {
                echo '<th>' . $columnNames[$i] . '</th>';
                }
                
            }
            $Border = array("CIEL_Quant","Scan_Quant",);
            foreach ($rows as $row) 
            {
                echo '<tr>';
                foreach ($row as $key => $value) 
                {
    
                    if (in_array($key, $Border))
                    {
                    echo "<td class='number-border'>" . $value . '</td>';
                    }
                    elseif ($key=="Difference")
                    {
                    echo "<td class='wide-border' >" . $value . '</td>';
                    }
                    else
                    {
                    echo '<td>' . $value . '</td>';
                    }
    
                }
                echo '</tr>';
            }
            echo "</table>";
        }
    }
}

// Closing stock-taking in actual view function
function InvClose()
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
$SQL = "UPDATE [dbo].[NCI_INV] SET [InvClose] = 1 WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct AND ([InvOpen] = 0) )";
$params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%' );  
$upd = $Connection->update($SQL,$params);
}
?>
</body>
