<?php
session_start();
if (!isset($_SESSION['currentDir'])){Find_Dir();}  
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 

//
$RowHunt = 0;
$CloseParcel = 0;
$RowInsert=0;
$txtpath = "c:\\xampp\htdocs\NCI\\var_ext\NCI_Info_import.txt";
$txt = file_get_contents($txtpath);
$items = explode(';', $txt);
$txt = $items[0];

if($txt == 'A') 
    {
    $DelTbl = 'exec DelNCI_StockInfo_A';
    $ImpTbl = 'NCI_StockInfo_A';
    $filecreate = dataimport($DelTbl, $ImpTbl);
    file_put_contents($txtpath,'B;'.$filecreate);
    }
elseif($txt == 'B') 
    {
    $DelTbl = 'exec DelNCI_StockInfo_B';
    $ImpTbl = 'NCI_StockInfo_B';
    $filecreate= dataimport($DelTbl, $ImpTbl);
    file_put_contents($txtpath,'A;'.$filecreate);
    }
else
    {


    }

function dataimport($DelTbl, $ImpTbl)
{
set_time_limit(900);
$startTime = microtime(true);
$txt = file_get_contents('http://localhost/proxy.txt');
$items = explode(';', $txt);
$parameters = [
    'proxy_host'     => $items[0],
    'proxy_port'     => $items[1],
     'stream_context' => stream_context_create(
        array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            )
        )
    )
];
    try
        {
        $startTimeFtp = microtime(true);
        $localFilePath = "\\\\10.47.17.20\pmi-dbo\SQL_script\Workday_1000\\imported\\";
        $filename = "NCI_STOCKIMAGE.txt"; 
        $FTPfile = new FTP("Ftp_wedos.txt",$parameters); 
        $FTPfile->FTP_download($localFilePath,$filename);
        $filecreate = $FTPfile->getRemoteFileCreationTime($filename);
        $endTimeFtp = microtime(true);
        $executionTimeFtp = $endTimeFtp - $startTimeFtp;
        $startTimeSQL = microtime(true);

        if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}

        $SQL = $DelTbl;
        $stmt = $Connection->execute($SQL);

        $dataImporter = new DataImporter();
        $dataImporter->importData($localFilePath.$filename,$ImpTbl);
        $_SESSION['ImportDone'] = "";
        $endTimeSQL = microtime(true);
        $executionTimeSQL = $endTimeSQL - $startTimeSQL;
        }
    catch (PDOException $exception) 
        {
        echo "Db connect error: " . $e->getMessage() . "\n";
        } 
    catch (Exception $e) 
        {
        echo "Error: " . $e->getMessage() . "\n";
        }
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    echo "Script time: ".$executionTime."sec <br>";
    echo "FTP time: ".$executionTimeFtp."<br>";
    echo "SQL time: ".$executionTimeSQL."<br>";
    echo "FTP file created: ".$filecreate."<br>";
    return $filecreate;
}
?>