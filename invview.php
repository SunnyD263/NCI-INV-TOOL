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
        <?php require 'invviewFunc.php'; ?>
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
        View();
    }

    // button 'uzavřít inventury'
    if (isset($_GET["InvClose"]))
    {
        InvClose();
        View();
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
        $_SESSION['Summary'] = $_GET['Summary'];
        Loc_detail();
        }

    // Push button 'Zpět'
    if (isset($_GET['Back']))
    {
        //from Locations detail
        if ($_GET['Back'] == 'InvSummary'  )
        {
        unset($_SESSION['Summary']);
        header("Location: invview.php?LoadForm=");
        }     
         //from Summary
        if ($_GET['Back'] == 'Menu'  )
        {
        unset($_SESSION['Summary']);
        unset($_SESSION["InvSummary"]);
        unset($_SESSION["Round"]);
        unset($_SESSION["LocSlct"]);
        header("Location: main.php");
        }
    }
    if (isset($_GET['Print']))
    {
        //from Locations detail
        if ($_GET['Print'] == 'InvSummary'  )
        {
            Loc_detail();
        }     
         //from Summary
        if ($_GET['Print'] == 'Menu'  )
        {    
        header("Location: invviewPrint.php?LoadForm=");
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
function View()
{
    $stmt = Combobox('');
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
    echo  "<div class='headButton'>";
    echo  "<form method='GET'>";
    echo  "<button type='submit' name='Back' id='Back' value='InvSummary'>Zpět</button>";
    echo  "<button type='submit' name='Summary' value='" . $_SESSION['Summary'] . "' onclick='window.print()'>Tisk</button><br><br>";
    echo  "</form>";
    echo  "</div>";

    // Detail stock-taking view 
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
    $SQL=  "SELECT  max([InvNum]) as InvNum,[Nd_Location], max(InvRnd) as InvRnd FROM [Liquid].[dbo].[NCI_INV] where ([St_Location] = :St_Location and [InvRnd] = :InvRnd ) group by Nd_Location";
    $params = array('St_Location' => $_SESSION['Summary'],'InvRnd' => $_SESSION['Round']);
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
    $params = array('St_Location' => $_SESSION['Summary']);
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
    $Border = array(1, 4, 7);
    $Number = array(0,1,4,7,8,11,12);
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
        $Border = array("CIEL_Quant","Scan_Quant","Nd_Location");
        $Number = array("St_Location","Nd_Location","CIEL_Quant","Scan_Quant","Difference","InvNum","InvRnd");
        foreach ($row as $key => $value) 
            {
            if (in_array($key, $Border))
                {
                echo "<td class='number-border'>" . $value . '</td>';
                } 
            elseif ($key == "Difference")
                {
                echo "<td class='wide-border'>" . $value . '</td>';
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
    echo "<br>";

    // Detail all stock-taking round by InvNum 
    if($count !== 0 and !empty($InvNum))
    {
        $Nd_Location = $stmt['rows'][0]['Nd_Location'];	
        $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[NCI_compare_View] where ([InvNum] = :InvNum and [InvRnd] <> :InvRnd) order by  Article,InvRnd";
        $params = array('InvNum' => $InvNum,'InvRnd' => $_SESSION["Round"]);
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
            $Border = array(1, 4, 7);
            $Number = array(0,1,4,7,8,11,12);
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
                $Border = array("CIEL_Quant","Scan_Quant","Nd_Location");
                $Number = array("St_Location","Nd_Location","CIEL_Quant","Scan_Quant","Difference","InvNum","InvRnd");
                foreach ($row as $key => $value) 
                    {
                    if (in_array($key, $Border))
                        {
                        echo "<td class='number-border'>" . $value . '</td>';
                        } 
                    elseif ($key == "Difference")
                        {
                        echo "<td class='wide-border'>" . $value . '</td>';
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
        }
    }
}

?>
</body>