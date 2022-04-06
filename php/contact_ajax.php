<?php
// include_once('dbconfig.php');
include_once('dbcon.php');

// $PDOconn = PDOCreateConn();
$conn = MySQLCreateConn();

//insert query
$tqsl="INSERT INTO table_name(column1, column2, column3) VALUES ('values1', 'values2', 'values3')";
//query execution
if($conn->query($tsql)===TRUE){
echo "Inserted Successfully";
   $conn->close();
}else{
echo "Insert Failed ".$conn->error;
$conn->close();
}
// }

?>

<!-- example php -->
<?php
include("../aps_db.php");
include("../tools_prov.php");
include("../tools_users.php");

$PDOconn = PDOCreateConn();

session_cache_limiter('private, must-revalidate');
session_start();

$alert = '';
$prvtid  = GetRequest('val_ema','T',200);
$prvnpi  = GetRequest('val_npi','T',10);
$prvlic  = GetRequest('val_lic','T',10);
$mylang  = GetRequest('mylang','T',5);

// echo "Error. id: $prvtid, NPI: $prvnpi, lic: $prvlic, lang: $mylang";
$params = array($prvtid,$prvnpi,$prvlic);

$tsql   = "SELECT COUNT(ppr_key) AS portalcount, COUNT(pr_id) as provcount
            FROM provs pr
            LEFT OUTER JOIN portal_provs pp 
                    ON pp.ppr_npi = pr.pr_npi AND pp.ppr_lic = pr.pr_lic 
                    AND pp.ppr_email = pr.pr_email
            WHERE pr_email = :premail	AND pr_npi = :prnpi AND pr_lic = :prlic ";

// PDO Connection
$statement = $PDOconn->prepare($tsql);
$statement->bindParam(':premail', $prvtid, PDO::PARAM_STR);
$statement->bindParam(':prnpi', $prvnpi, PDO::PARAM_STR);
$statement->bindParam(':prlic', $prvlic, PDO::PARAM_STR);
$statement->execute();
    
$portalcount=0;
$provcount=0;
$row = $statement->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $portalcount  = $row['portalcount'];
    $provcount    = $row['provcount'];
}
    
$myalerts = array();
$newalert = array();
$newalert['id'] = "Este email aparece registrado con nosotros. Puede usar el enlace <Olvido Contraseña> para restablecer la cuenta.";
$newalert['lang_data'] = $newalert['id'];
$myalerts[0] = $newalert;
$newalert = array();
$newalert['id'] = "La informacion no aparece en nuestro record. Intente de nuevo o contactenos para asistirle en el registro.";
$newalert['lang_data'] = $newalert['id'];
$myalerts[1] = $newalert;
$newalert = array();
$newalert['id'] = "Hubo un problema al enviarle un correo electrónico. Vuelva a intentarlo.";
$newalert['lang_data'] = $newalert['id'];
$myalerts[2] = $newalert;
$myalerts_jsn = json_encode($myalerts); 


if ($mylang != 'spa') $myalerts_jsn = translate_array($PDOconn,$myalerts_jsn,$mylang,'spa');
$myalerts = json_decode($myalerts_jsn,true); 




if ($provcount == 0) {
   	$alert = $myalerts[1]['lang_data']; 
    http_response_code(401);
} else {
    if ($portalcount == 0) {
        $alert = '';
        $xti   = PwdCrypt('E', $prvtid);
        $xnp   = PwdCrypt('E', $prvnpi);
        $xli   = PwdCrypt('E', $prvlic);
    
        //clear session variables
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_npi'] = "";
        $_SESSION['reg_pwd'] = "";
        $_SESSION['reg_phone'] = "";
        $_SESSION['reg_carrier'] = "";
        $_SESSION['reg_p1'] = "";
        $_SESSION['reg_p2'] = "";
        $_SESSION['reg_p3'] = "";
        $_SESSION['reg_p4'] = "";
        $_SESSION['reg_code_email'] = "";
        
        //Set Session Token
				$token = random_bytes(10);
				$token = bin2hex($token);
				$_SESSION['portal_token'] = $token;
    
        $_SESSION['reg_email'] = $xti;
        $_SESSION['reg_npi'] = $xnp;
        $_SESSION['reg_lic'] = $xli;
        $_SESSION['reg_lang'] = $mylang;
        $_SESSION['message'] = "";
    
        if (sendProvEmail($PDOconn, $prvtid, 'Validate_Email_' . $mylang)) {
            //SetPortalUserLog($conn,'V','NR','','Information Validated. Goto Register Step 1'.' Info['.$prvtid.'|'.$prvnpi.'|'.$prvlic.']');
            // echo "<script type=\"text/javascript\"> window.onload=function(){ document.forms['regsubForm'].submit();} </script>";
            $ses_code = (isset($_SESSION['reg_code_email'])) ? $_SESSION['reg_code_email'] : '';
            $alert = "Success!" . $ses_code; 
            http_response_code(200);
        } else {
            //Email error
           	$alert = $myalerts[2]['lang_data']; 
            http_response_code(401);
        }
    } else {
        //Email already on system
        $alert = $myalerts[0]['lang_data']; 
        http_response_code(401);
    }
}


echo $alert;

?>