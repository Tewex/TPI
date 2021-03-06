<?php
/*
 * File: cMedia.php
 * Author: Théo Hurlimann
 * Date: 25.05.2020
 * Description: Contient les informations utile pour un media
 * Version: 1.0 
*/
/*
 * La classe cMedia contient les informations complémentaire à un media
 * Ex: nom original, chemin, Tpi associé, etc.
 */
class cMedia{

     /**
     * @brief   Class Constructor avec paramètres par défaut pour construire l'objet
     */
    public function __construct($InMediaId = -1,$InOriginalName = "", $InMediaPath = "", $InMimeType = "", $InTpiId = ""){
        $this->id = $InMediaId;
        $this->originalName = $InOriginalName;
        $this->mediaPath = $InMediaPath;
        $this->mimeType = $InMimeType;
        $this->tpiId = $InTpiId;
    }
    /** @var [int] Id unique du media */
    public $id;

    /** @var [string] Nom original du media */
    public $originalName;

    /** @var [string] Nom du media */
    public $mediaPath;

    /** @var [string] Type du mime du media */
    public $mimeType;

    /** @var [int] TPI associé au media */
    public $tpiId;

}

?>