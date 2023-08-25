<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>NCI administrace</title>
        <meta charset="UTF-8">
        <meta name="author" content="Jan Sonbol" />
        <meta name="description" content="Nučice" />
               <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="icon" type="image/png" href="images/kn.png"/>
        <script
            src="https://code.jquery.com/jquery-3.6.4.js"
            integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
            crossorigin="anonymous">
        </script>
    </head>

    <body>
        <header>
        <h1>NCI</h1>
        <?php require 'projectfunc.php'; ?>
        <?php require 'SQLconn.php'; ?>
        </header>    
<?php
session_start(); 
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    //push button main-bar 'Aktualizace - Aktualní skladová zásoba'     
    if (isset($_GET['WHStock']))
    {
    $InvRnd=$_GET['WHStock'];
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
    $SQL = "exec DelNCI_Stock_".$InvRnd;
    $stmt = $Connection->execute($SQL);

        $dataImporter = new DataImporter();
        $dataImporter->importData($localFilePath.$filename,$InvRnd,"NCI_Stock_".$InvRnd);
        $_SESSION['ImportDone'] = ""; 

    $SQL = "exec EmptyLoc_".$InvRnd;
    $stmt = $Connection->execute($SQL);

    $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $_SESSION["SQLtime"] = $executionTime;

        header("Location: main.php");
    }

    //push button main-bar 'Administace inv - smazat inventuru lokace'
    if (isset($_GET['Delinv']))
    {
        echo "<fieldset>";
        echo "<label for='UserSlc' class='label-TradeIN'>Vyber uživatele:</label><br>";
        echo "<form method='GET'>";
        echo "<select name='UserSlc' ID='UserSlc' class='Combo' onchange='this.form.submit()'>";

        if (!isset($Connection)) {
            $Connection = new PDOConnect("Liquid");
        }
            
        $SQL = "SELECT [UserID] FROM [Liquid].[dbo].[NCI_loc_user_View] GROUP BY UserID";
        $stmt = $Connection->select($SQL);
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        
        foreach ($rows as $row) {
            echo "<option id=" . $row['UserID'] . " value=" . $row['UserID'] . ">" . $row['UserID'] . "</option>";
        }
        echo "<option id=0 value='' selected></option>";
        echo "</select>";
        echo "</form>";
        echo "</fieldset><br>";

        echo    "<div class='responsive'>";
        echo    "<fieldset>";
        echo    "<label for='LocationSlc' class='label-TradeIN'>Potvrzení smazání:</label><br>";
        echo    "<form method='GET'>";
        echo    "<input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'><br>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "</div>";
    }
    elseif (isset($_GET['UserSlc']))
    {
        echo "<fieldset>";
        echo "<label for='UserSlc' class='label-TradeIN'>Vyber uživatele:</label><br>";
        echo "<select name='UserSlc' ID='UserSlc' class='Combo' onchange='this.form.submit()'>";        
        echo "<option id=" . $_GET['UserSlc'] . " value=" . $_GET['UserSlc'] . " disabled selected>" . $_GET['UserSlc'] . "</option>";
        echo "</select>";
        echo "</fieldset><br>";

        echo "<fieldset>";
        echo "<label for='LocSlc' class='label-TradeIN'>Vyber lokaci (původní/nová):</label><br>";
        echo "<form method='GET'>";
        echo "<select name='LocSlc' ID='LocSlc' class='Combo' onchange='this.form.submit()'>";                
        if (!isset($Connection)) {$Connection = new PDOConnect("Liquid");}
        $SQL = "SELECT [St_Location], [Nd_Location] FROM [Liquid].[dbo].[NCI_loc_user_View] where ([UserID] = :UserID) group by [St_Location], [Nd_Location]";
        $params = array('UserID' => $_GET['UserSlc']);
        $stmt = $Connection->select($SQL, $params);
        $rows = $stmt['rows'];
        $count = $stmt['count'];
        $FieldNum=0;
        foreach ($rows as $row) 
        {        
        echo "<option id=" . $rows[$FieldNum]["St_Location"]."/".$rows[$FieldNum]["Nd_Location"]  ." value=" . $rows[$FieldNum]["St_Location"]."/".$rows[$FieldNum]["Nd_Location"]  . ">" . $rows[$FieldNum]["St_Location"]."/".$rows[$FieldNum]["Nd_Location"]  . "</option>";
        $FieldNum++;
        }
        echo "<option id=0 value='' selected>" . "</option>";
        echo "</select>";
        echo "</form>";
        echo "</fieldset><br>";

        echo    "<div class='responsive'>";
        echo    "<fieldset>";
        echo    "<label for='LocationSlc' class='label-TradeIN'>Potvrzení smazání:</label><br>";
        echo    "<form method='GET'>";
        echo    "<input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'><br>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "</div>";
        $_SESSION['UserSlc'] = $_GET['UserSlc'];

    }
    elseif (isset($_GET["LocSlc"]))
    {
        echo "<fieldset>";
        echo "<label for='UserSlc' class='label-TradeIN'>Vyber uživatele:</label><br>";
        echo "<select name='UserSlc' ID='UserSlc' class='Combo' onchange='this.form.submit()'>";        
        echo "<option id=" . $_SESSION['UserSlc']. " value=" .$_SESSION['UserSlc'] . " disabled selected>" . $_SESSION['UserSlc'] . "</option>";
        echo "</select>";
        echo "</fieldset><br>";

        echo "<fieldset>";
        echo "<label for='LocSlc' class='label-TradeIN'>Vyber lokaci (původní/nová):</label><br>";
        echo "<select name='LocSlc' ID='LocSlc' class='Combo' onchange='this.form.submit()'>";                
        echo "<option id=" . $_GET["LocSlc"] ." value=" .$_GET["LocSlc"]. "disabled selected>" .$_GET["LocSlc"]. "</option>";
        echo "</select>";
        echo "</fieldset><br>";

        echo    "<div class='responsive'>";
        echo    "<fieldset>";
        echo    "<label for='LocationSlc' class='label-TradeIN'>Potvrzení smazání:</label><br>";
        echo    "<form method='GET'>";
        echo    "<input type='submit' onclick='' class='Button' name='Delete' id='Delete' value='Smazat'><br>";
        echo    "<input type='submit' onclick='' class='Button' name='Back' id='Back' value='Zpět'><br>";
        echo    "</form>";
        echo    "</fieldset>";
        echo    "</div>";
        $_SESSION['LocSlc'] = $_GET['LocSlc'];
    }
    elseif (isset($_GET["Delete"]))
    {   
        $parts = explode('/',$_SESSION['LocSlc']);
        if (count($parts) === 2) 
        {
        $beforeSlash = trim($parts[0]);
        $afterSlash = trim($parts[1]);
        }
        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
        $SQL = "SELECT [PalletID],[EAN],[Quantity],[St_Location],[Nd_Location],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[NCI_Pallets] where ([UserID] = :UserID) AND ([St_Location] = :St_Location) AND ([Nd_Location]= :Nd_Location)";
        $params = array('UserID' => $_SESSION['UserSlc'],'St_Location' => $beforeSlash,'Nd_Location' => $afterSlash);
        $stmt = $Connection->select($SQL, $params);
        $InvNum = $stmt['rows'][0]['InvNum'];
        $InvRnd = $stmt['rows'][0]['InvRnd'];
        foreach ($stmt['rows'] as $row) {
            $data = array(
                'PalletID' => $row['PalletID'],
                'EAN' => $row['EAN'],
                'Quantity' => $row['Quantity'],
                'St_Location' => $row['St_Location'],
                'Nd_Location' => $row['Nd_Location'],
                'Scantime' => $row['Scantime'],
                'UserID' => $row['UserID'],
                'InvNum' => $row['InvNum'],
                'InvRnd' => $row['InvRnd']
            );


            $Connection->insert('NCI_Pallets_Hist', $data);
        }
        $SQL = "SELECT * FROM [Liquid].[dbo].[NCI_loc_Ciel_View] where ([KPWHLO] = :St_Location)";
        $params = array('St_Location' => $beforeSlash);
        $stmt = $Connection->select($SQL, $params);
        foreach ($stmt['rows'] as $row) {
            $data = array(
                'Article' => $row['Article'],
                'Description' => $row['Description'],
                'KPWHLO' => $row['KPWHLO'],
                'Quantity' => $row['SUMQuantity'],
                'InvNum' => $InvNum,
                'InvRnd' => $InvRnd
            );;
            $Connection->insert('NCI_Stock_Hist', $data);
        }

        $SQL = "DELETE FROM [Liquid].[dbo].[NCI_Pallets] where ([UserID] = :UserID) AND ([St_Location] = :St_Location) AND ([Nd_Location]= :Nd_Location)";
        $params = array('UserID' => $_SESSION['UserSlc'],'St_Location' => $beforeSlash,'Nd_Location' => $afterSlash);
        $stmt = $Connection->execute($SQL, $params);
        unset($_SESSION['UserSlc']);
        unset($_SESSION['LocSlc']);
        header("Location: main.php");
    }
    elseif (isset($_GET["Back"]))
    {
        unset($_SESSION['UserSlc']);
        unset($_SESSION['LocSlc']);
        header("Location: main.php");
    }
}
?>
</body>
