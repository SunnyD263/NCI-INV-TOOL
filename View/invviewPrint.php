<!DOCTYPE html>
<html lang="cs">
    <head>
        <title>Tisk inventury</title>
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
        </header>    

<?php
session_start(); 
require $_SESSION['currentDir']."\projectfunc.php";
require $_SESSION['currentDir']."\SQLconn.php"; 
require 'invviewFunc.php';
If ($_SERVER["REQUEST_METHOD"] == "GET") 
{
    // default view form
    if (isset($_GET['LoadForm'])) 
        {
        echo  "<div class='headButton'>";
        echo  "<form method='GET'>";
        echo  "<button type='submit' name='Back' id='Back' value='InvSummary'>Zpět</button>";
        echo  "<button type='submit' name='LoadForm' onclick='window.print()'>Tisk</button><br><br>";
        echo  "</form>";
        echo  "</div>";
        View();
        }
    elseif(isset($_GET['Back']))
        {
        header("Location: invview.php?LoadForm=");
        }
}
FUNCTION View()
{
$stmt= InvSQL('Print');
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

foreach ($stmt['rows'] as $row) 
    {        
    foreach ($row as $key => $value) 
        {
        if ($key == 'St_Location')
            {
            // Detail stock-taking view 
            if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
            $SQL=  "SELECT  max([InvNum]) as InvNum,[Nd_Location], max(InvRnd) as InvRnd FROM [Liquid].[dbo].[NCI_INV] where ([St_Location] = :St_Location and [InvRnd] = :InvRnd ) group by Nd_Location";
            $params = array('St_Location' =>  $value,'InvRnd' => $_SESSION['Round']);
            $stmt = $Connection->select($SQL, $params);
            $count = $stmt['count'];
            $table = "NCI_compare_View";
            if($count > 0)
                {
                $InvNum= $stmt['rows'][0]['InvNum'];
                $Nd_Location = $stmt['rows'][0]['Nd_Location'];	
                $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[".$table."] where ([InvNum] = :InvNum) order by  Article,InvRnd desc";
                $params = array('InvNum' => $InvNum);
                }
            else
                {
                $SQL=  "SELECT [St_Location],[Nd_Location],[Article],[Description],[CIEL_Quant],[Code],[Material],[Scan_Quant],[Difference],[Scantime],[UserID],[InvNum],[InvRnd] FROM [Liquid].[dbo].[".$table."] where ([St_Location] = :St_Location) order by Article,InvRnd desc";
                $params = array('St_Location' => $value);
                }
            $stmt = $Connection->select($SQL, $params);
            $rows = $stmt['rows'];
            $count = $stmt['count'];
        //Same location inv border
    $Check = 1;
            foreach ($rows as $row) 
                {
                if($Check == 1)
                    {
                    echo "<tr class='wide-row'>";
                    $Check = 0;
                    }
                else
                    {
                    echo "<tr>";    
                    }
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
            }
        }
    }
echo "</table>";
echo "<br>";

}
?>
</body>
