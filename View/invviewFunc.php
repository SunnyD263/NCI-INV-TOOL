<?php
function InvSQL($View){
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
if($View !== 'ID')
    {
    $Cmd = "NCI_INV_summary_".$_SESSION['Round']."_view";   
    switch($_SESSION['InvSummary'])
        {        
        // All locations stock-taking    
        case "All":
                    $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose] FROM [Liquid].[dbo].[".$Cmd."] 
                    WHERE  ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) order by St_Location ";
                    $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "9");
                    $stmt = $Connection->select($SQL, $params);
            if($View == '')
                {
                echo "<option id='All' value='All' selected>Všechny</option>";
                echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
                echo "<option id='INV' value='INV' >Pozice s INV</option>";
                echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
                echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
                }
            break;
        
        // Locations without stock-taking  
        case "Empty":    
            $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[".$Cmd."] 
                    WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is null and CIEL_Quant <> 0   order by St_Location ";
                    $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "9");
                    $stmt = $Connection->select($SQL, $params);
            if($View == '')
                {
                echo "<option id='All' value='All' >Všechny</option>";
                echo "<option id='Empty' value='Empty'selected>Pozice bez INV</option>";
                echo "<option id='INV' value='INV' >Pozice s INV</option>";
                echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
                echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
                }

        // Locations with stock-taking  
        case  "INV":   
            $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[".$Cmd."]
                    WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null order by St_Location ";
                    $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "9");
                    $stmt = $Connection->select($SQL, $params);
            if($View == '')
                {
                echo "<option id='All' value='All' >Všechny</option>";
                echo "<option id='Empty' value='Empty'>Pozice bez INV</option>";
                echo "<option id='INV' value='INV' selected>Pozice s INV</option>";
                echo "<option id='INV_Diff' value='INV_Diff' >Pozice s INV a differencí</option>";
                echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
                }
            break;
        // Location with stock-taking a without some differences  
        case "INV_Diff":    
            $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[".$Cmd."]
                    WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null and RowScan <> RowCiel or 
                    ([InvRnd] = :InvRnd1) AND ([St_Location] like :LocSlct1) and (len([St_Location]) < :CharCount1) and Nd_Location is not null and Difference <> 0 order by St_Location";
                    $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "9",'InvRnd1' => $_SESSION["Round"],'LocSlct1' => $_SESSION['LocSlct'].'%','CharCount1' => "9");
                    $stmt = $Connection->select($SQL, $params);
            if($View == '')
                {
                echo "<option id='All' value='All' >Všechny</option>";
                echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
                echo "<option id='INV' value='INV' >Pozice s INV</option>";
                echo "<option id='INV_Diff' value='INV_Diff' selected>Pozice s INV a differencí</option>";
                echo "<option id='INV_NoDiff' value='INV_NoDiff'>Pozice s INV bez differencí</option>";
                }
            break;

        // Location without stock-taking and without some differences  
        case  "INV_NoDiff":    
            $SQL=  "SELECT [St_Location],[Nd_Location],[CIEL_Quant],[Scan_Quant],[Difference],[UserID],[InvNum],[InvRnd],[InvClose]  FROM [Liquid].[dbo].[".$Cmd."] 
                    WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct) and (len([St_Location]) < :CharCount) and Nd_Location is not null and RowScan = RowCiel and Difference = 0 order by St_Location ";
                    $params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "9");
                    $stmt = $Connection->select($SQL, $params);
            if($View == '')
                {
                echo "<option id='All' value='All'>Všechny</option>";
                echo "<option id='Empty' value='Empty' >Pozice bez INV</option>";
                echo "<option id='INV' value='INV' >Pozice s INV</option>";
                echo "<option id='INV_Diff' value='INV_Diff'>Pozice s INV a differencí</option>";
                echo "<option id='INV_NoDiff' value='INV_NoDiff' selected>Pozice s INV bez differencí</option>";
                }
            break;
        }
    }
else
    {
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
        }

    }
    return $stmt;
}

// Closing stock-taking in actual view function
function InvClose()
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
$SQL = "UPDATE [dbo].[NCI_INV] SET [InvClose] = 1 WHERE ([InvRnd] = :InvRnd) AND ([St_Location] like :LocSlct)";
$params = array('InvRnd' => $_SESSION["Round"],'LocSlct' => $_SESSION['LocSlct'].'%' );  
$upd = $Connection->update($SQL,$params);
}

function Combobox($View)
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
        echo  "<div class='headButton'>";
        echo  "<form method='GET'>";
        echo  "<button type='submit' name='Back' id='Back' value='Menu' >Zpět</button><br><br>";
        echo  "</form>";
        echo  "</div>";
    
        if (!isset($Connection)) {
            $Connection = new PDOConnect("Liquid");
        }
        // Combobox 'Kolo inventury'
        echo "<fieldset class='InvView'>";
        echo "<legend>Filtr:</legend>";
if($View !=='ID') 
    {
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
    }    
        // Combobox 'Lokace'
        echo "<form method='GET'>";
        echo "<label for='LocSlct'>Lokace:</label>";
        echo "<select name='LocSlct' ID='LocSlct' onchange='this.form.submit()'>";
    
        // Combobox field 'Lokace' content
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

if($View !=='Stock_import') 
    {
    // Combobox 'Stav Inv lokace'
    echo "<form method='GET'>";
    echo "<label for='InvSummary'>Stav Inv lokace:</label>";
    echo "<select name='InvSummary' ID='InvSummary' onchange='this.form.submit()'>";
    $stmt = InvSQL($View);
    echo "</select>";
    echo "</form><br>";
    echo "</fieldset><br>";
    echo "<div class='ButtonHeader'>";
    echo "<fieldset>";
    echo "<form method='GET'>";
    echo "<button type='submit' name='InvClose' id='InvClose' value=''>Uzavřít inventury</button>";
    echo "</form>";
    echo "<form method='GET'>";
    echo "<button type='submit' name='Print' id='Print' value='Menu'>Tisk lokací</button>";
    echo "</form>";
    echo "</fieldset><br>";
    echo "</div>";
    return $stmt; 
    }
else
    {
    echo "</fieldset><br>";
    echo "<div class='ButtonHeader'>";
    echo "<fieldset>";
    echo "<form method='GET'>";
    echo "<button type='submit' name='Download' id='Download' value=''>Stažení lokací</button>";
    echo "</form>";
    echo "</fieldset><br>";
    echo "</div>";
    }    
}


?>