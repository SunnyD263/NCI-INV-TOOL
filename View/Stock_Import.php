<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Import skladové zásoby</title>
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
        <h2>Import skladové zásoby</h2>
        </header>    
<?php
session_start();
if (!isset($_SESSION['currentDir'])){Find_Dir();}  
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 
require 'invviewFunc.php';

If ($_SERVER["REQUEST_METHOD"] == "GET") 
    {
    // default view form
    if (isset($_GET['LoadForm'])) 
    {
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 2;}
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        View();
    }
    // Download select data
    if (isset($_GET['Download']))
        {
        $InvRnd=$_SESSION["Round"];
        $startTime = microtime(true);
        $localFilePath = "\\\\10.47.17.20\pmi-dbo\SQL_script\Workday_1000\\";
        $filename = "NCI_STOCKIMAGE.txt"; 
        $FTPfile = new FTP("Ftp_wedos.txt"); 
        $FTPfile->FTP_download($localFilePath,$filename);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $_SESSION["FTPtime"] = $executionTime;
    
        $startTime = microtime(true);
        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
        $SQL = "exec DelNCI_Stock_import";
        $stmt = $Connection->execute($SQL);
        
            $dataImporter = new DataImporter();
            $dataImporter->importData($localFilePath.$filename,"NCI_stock_import");
            $_SESSION['ImportDone'] = ""; 

        $SQL = "exec DelNCI_Stock_".$InvRnd;
        $stmt = $Connection->execute($SQL);

        $table = "NCI_Stock_".$InvRnd;
        $SQL=  "INSERT INTO [dbo].[$table]([Article],[KPWHZO],[KPWHLO],[KPCASE],[KPAVAL],[Quantity])
                select [Article],[KPWHZO],[KPWHLO],[KPCASE],[KPAVAL],[Quantity] from NCI_Stock_import
                where ([KPWHLO] like :LocSlct) and (len([KPWHLO]) < :CharCount)";
        $params = array('LocSlct' => $_SESSION['LocSlct'].'%','CharCount' => "8");
        $stmt = $Connection->execute($SQL, $params);        

    
        // $SQL = "exec EmptyLoc_".$InvRnd;
        // $stmt = $Connection->execute($SQL);
    
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $_SESSION["SQLtime"] = $executionTime;
    
        header("Location: \\nci\menu\main.php");
        unset($_SESSION["Round"]);
        }
    // Choice from combobox 'Kolo inventury'
    if (isset($_GET['Round']))
        {   
        if(!isset($_SESSION['LocSlct'])){$_SESSION['LocSlct'] = '';}
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        $_SESSION["Round"] = $_GET['Round'];
        header("Location: stock_import.php?LoadForm=");
        }

    // Choice from combobox 'Lokace'    
    if (isset($_GET['LocSlct']))
        {      
        if(!isset($_SESSION["Round"])){$_SESSION["Round"] = 1;}
        if(!isset($_SESSION['InvSummary'])){$_SESSION['InvSummary'] = 'All';}
        $_SESSION['LocSlct'] = $_GET['LocSlct'];
        header("Location: stock_import.php?LoadForm=");
        }

    // Push button 'Zpět'
    if (isset($_GET['Back']))
        {
        unset($_SESSION['Summary']);
        unset($_SESSION["InvSummary"]);
        unset($_SESSION["Round"]);
        unset($_SESSION["LocSlct"]);
        header("Location: \\nci\menu\main.php");
        }
    }

// form view function
function View()
    {
    $stmt = Combobox('Stock_import');
    // records counter
    }
?>
</body>

