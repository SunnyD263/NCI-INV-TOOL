<?php
//Box, pallets counter function
function PalCounter($InvNum,$InvRnd)
{
    if (!isset($Connection)){$Connection = new PDOConnect("Liquid");} 
    $SQL = "SELECT TOP 1 [PalCounter] FROM [Liquid].[dbo].[NCI_PalCounter_View] where  ([InvNum] = :InvNum) and ([InvRnd] = :InvRnd)";
    $params = array('InvNum' => $InvNum,'InvRnd' => $InvRnd );  
    $stmt = $Connection->select($SQL,$params); 
    if ($stmt["count"] !== 0 ) 
        {
        $count = $stmt['rows'][0]['PalCounter'];
        }
    else 
        {
        $count = 0;
        }

    return $count;

}

function SQL_del_row($ID,$InvRnd)
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
$table = 'NCI_Pallets_'.$InvRnd;
$SQL = "DELETE FROM [dbo].[$table] WHERE [ID] = :ID";
$params = array('ID' =>  $ID);
$stmt = $Connection->execute($SQL, $params);
}
function SQL_upd_row($ID,$InvRnd,$Quantity)
{
if (!isset($Connection)){$Connection = new PDOConnect("Liquid");}
$table = 'NCI_Pallets_'.$InvRnd;
$SQL = "UPDATE [dbo].[$table] SET [Quantity] = $Quantity WHERE [ID] = :ID";
$params = array('ID' =>  $ID);
$stmt = $Connection->update($SQL, $params);
}


?>