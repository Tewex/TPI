<?php
/*
 * File: listTPI.php
 * Author: Théo Hurlimann
 * Date: 26.05.2020
 * Description: Page pour l'affichage de la liste des TPIS
 * Version: 1.0 
*/
require_once("php/inc.all.php");

if (!islogged()) {

    $messages = array(
        array("message" => "Vous devez être connecté pour voir ceci.", "type" => AL_DANGER)
    );
    setMessage($messages);
    setDisplayMessage(true);

    header('Location: login.php');
    exit;
}

$arrRoles = getRoleUserSession();
$role = min($arrRoles);

$btnModify = filter_input(INPUT_POST, "btnModify", FILTER_SANITIZE_NUMBER_INT);
$btnDelete = filter_input(INPUT_POST, "btnDelete", FILTER_SANITIZE_NUMBER_INT);
$btnInvalidate = filter_input(INPUT_POST, "btnInvalidate", FILTER_SANITIZE_NUMBER_INT);

$radioRole = filter_input(INPUT_GET,'radioRole',FILTER_SANITIZE_STRING);
$radioRoleBtn = filter_input(INPUT_POST,'radioRole',FILTER_SANITIZE_STRING);


if (count($arrRoles) > 1) {
    if ($radioRole != null) {
        $role = $radioRole;
    }
}

if (count($arrRoles) > 1) {
    if ($radioRoleBtn != null) {
        $role = $radioRoleBtn;
    }
}

switch ($role) {
    case RL_ADMINISTRATOR:
        $arrTpi = getAllTpi();
        $tpiExistIn = false;
        if ($btnModify) {
            header('Location: modifyTPI.php?tpiId=' . $btnModify);
            exit;
        }

        if ($btnDelete) {
            $tpi = getTpiByIdWithMedia($btnDelete);
            $listTable = array(
                "wishes", "tpi_validations", "tpi_evaluations", "tpi_evaluations_criterions"
            );

            foreach ($listTable as $t) {
                if (tpiExistIn($tpi, $t)) {
                    $tpiExistIn = true;
                }
            }

            if (!$tpiExistIn && $tpi->tpiStatus == ST_DRAFT) {
                if (deleteTpi($tpi)) {

                    foreach ($arrTpi as $indexArray => $t) {
                        if ($tpi->id == $t->id) {
                            unset($arrTpi[$indexArray]);
                        }
                    }

                    $messages = array(
                        array("message" => "Le TPI a bien été supprimer.", "type" => AL_SUCESS)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                };
            } else {
                # TO DO : Gerer information dans plusieurs table Confirmation bien supprimer tpi
                $messages = array(
                    array("message" => "Impossible de supprimer le TPI.", "type" => AL_SUCESS)
                );
                setMessage($messages);
                setDisplayMessage(true);
            }
        }

        if ($btnInvalidate) {
            $tpi = getTpiByIdInArray($btnInvalidate, $arrTpi);
            if ($tpi->tpiStatus == ST_SUBMITTED) {
                if (invalidateTpi($tpi)) {
                    $tpiUpdate = getTpiByID($tpi->id);
                    foreach ($arrTpi as $indexArray => $tpi) {
                        if ($tpi->id == $tpiUpdate->id) {
                            $tpi->tpiStatus = $tpiUpdate->tpiStatus;
                        }
                    }
                    $messages = array(
                        array("message" => "Le TPI a bien été invalidé.", "type" => AL_SUCESS)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                } else {
                    $messages = array(
                        array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                }
            } else {
                $messages = array(
                    array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                );
                setMessage($messages);
                setDisplayMessage(true);
            }
        }
        $displayTPI = displayTPIAdmin($arrTpi, $arrRoles, $role);
        break;
    case RL_EXPERT:
        $arrTpi = getAllTpiByIdUserExpertSession();
        $idUser = getIdUserSession();
        $tpi = getTpiByIdInArray($btnInvalidate, $arrTpi);

        if ($btnModify) {
            $tpi = getTpiByIdInArray($btnModify, $arrTpi);
            if ($tpi->userExpertId == $idUser || $tpi->userExpertId2 == $idUser) {
                header('Location: modifyTPI.php?tpiId=' . $btnModify);
                exit;
            }
            
        }

        if ($btnInvalidate) {
            

            if (
                $tpi->tpiStatus == ST_SUBMITTED && $tpi->userExpertId == $idUser ||
                $tpi->tpiStatus == ST_SUBMITTED && $tpi->userExpertId2 == $idUser
            ) {
                if (invalidateTpi($tpi)) {
                    $tpiUpdate = getTpiByID($tpi->id);
                    foreach ($arrTpi as $indexArray => $tpi) {
                        if ($tpi->id == $tpiUpdate->id) {
                            $tpi->tpiStatus = $tpiUpdate->tpiStatus;
                            $tpi->pdfPath = $tpiUpdate->pdfPath;
                        }
                    }
                    $messages = array(
                        array("message" => "Le TPI a bien été invalidé.", "type" => AL_SUCESS)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                } else {
                    $messages = array(
                        array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                }
            } else {
                $messages = array(
                    array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                );
                setMessage($messages);
                setDisplayMessage(true);
            }
        }

        $displayTPI = displayTPIExpert($arrTpi, $arrRoles, $role);
        break;
    case RL_MANAGER:
        $btnSubmit = filter_input(INPUT_POST, "btnSubmit", FILTER_SANITIZE_NUMBER_INT);
        $arrTpi = getAllTpiByIdUserManagerSession();
-        $idUser = getIdUserSession();

        if ($btnModify) {
            $tpi = getTpiByIdInArray($btnModify, $arrTpi);
            if ($tpi->userManagerId == $idUser) {
                header('Location: modifyTPI.php?tpiId=' . $btnModify);
                exit;
            }
        }

        if ($btnSubmit) {
            $tpi = getTpiByIdInArray($btnSubmit, $arrTpi);

            if ($tpi->tpiStatus == ST_DRAFT && $tpi->userManagerId == $idUser) {
                if (submitTpi($tpi)) {
                    $tpiUpdate = getTpiByID($tpi->id);
                    foreach ($arrTpi as $indexArray => $tpi) {
                        if ($tpi->id == $tpiUpdate->id) {
                            $tpi->tpiStatus = $tpiUpdate->tpiStatus;
                            $tpi->pdfPath = $tpiUpdate->pdfPath;
                        }
                    }
                    $messages = array(
                        array("message" => "Le TPI a bien été soumis.", "type" => AL_SUCESS)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                } else {
                    $messages = array(
                        array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                    );
                    setMessage($messages);
                    setDisplayMessage(true);
                }
            } else {
                $messages = array(
                    array("message" => "Une erreur est survenue.", "type" => AL_DANGER)
                );
                setMessage($messages);
                setDisplayMessage(true);
            }
        }

        $displayTPI = displayTPIManager($arrTpi, $arrRoles, $role);
        break;
    default:
        $messages = array(
            array("message" => "Vous ne pouvez pas voir la liste de TPI.", "type" => AL_WARNING)
        );
        setMessage($messages);
        setDisplayMessage(true);

        header('Location: home.php');
        exit;
        break;
}


?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste TPI</title>
    <!-- CSS FILES -->
    <link rel="stylesheet" type="text/css" href="css/uikit.css">
    <link rel="stylesheet" href="css/cssNavBar.css">
</head>

<body>
    <?php include_once("php/includes/nav.php");
    echo displayMessage();
    
    echo $displayTPI;
    ?>

    <!-- JS FILES -->
    <script src="js/uikit.js"></script>
    <script src="js/uikit-icons.js"></script>
    <script>
        function changeRole(val) {
            window.location.href = "listTPI.php?radioRole=" + val;
        }
    </script>
</body>

</html>