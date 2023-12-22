<?php
if ($argc < 2 ){
    exit("Veuillez entrer un fichier\n");
}

//Liste de paramètres de la fonction geropt
$shortopts  = "";
$shortopts .= "i:";
$shortopts .= "s:";
$shortopts .= "p:";
$shortopts .= "r";

$longopts  = array(     
    "output-image:",   
    "output-style:",    
    "padding:",  
    "recursive",       
);
$options = getopt($shortopts, $longopts);

//Récupère tout les images png sans recursive
function getAllImages($folder){
    $imageFiles = [];
    if(is_dir($folder)){
        $pngFiles = glob($folder . '*.png');
        foreach ($pngFiles as $pngFile) {
            if (is_file($pngFile)) {
                $imageFiles[] = $pngFile;
            }
        }
    }
    return($imageFiles);
}

//Récupère tout les images png avec recursive
function getAllImagesRecursive ($folder, &$imageFiles, ){
    if( is_dir($folder) ){
		$me = opendir($folder);
		while( $child = readdir($me) ){
			if( $child != '.' && $child != '..'){
				getAllImagesRecursive($folder.DIRECTORY_SEPARATOR.$child, $imageFiles);
			}
            if(strpos($child, ".png") !== false){
                array_push($imageFiles, $folder.DIRECTORY_SEPARATOR.$child);
            }
		}
	}
    return $imageFiles;
}
$imageFiles = [];

//Concatener les images
function concatenateImages($imageFiles, $output) {
    global $options;
    if (empty($imageFiles)) {
        die('Pas d\'image à concaténer.\n');
    }
    $images = [];
    foreach ($imageFiles as $imageFile) {
        $img = imagecreatefrompng($imageFile);
        $images[] = $img;
    }

    $totalWidth = 0;
    $totalHeight = 0;
    foreach ($images as $img) {
        $totalWidth += isset($options["p"]) && is_numeric($options["p"]) ? imagesx($img)+$options["p"] : imagesx($img);
        // $totalWidth += imagesx($img);
        // $totalHeight = max($totalHeight, imagesy($img));     
        $totalHeight = isset($options["p"]) && is_numeric($options["p"]) ? max($totalHeight, imagesy($img)+$options["p"]*2) : max($totalHeight, imagesy($img));    
    }

    // $sprite = imagecreatetruecolor($totalWidth, $totalHeight);
    $sprite = isset($options["p"]) && is_numeric($options["p"]) ? imagecreatetruecolor($totalWidth+$options["p"], $totalHeight) : imagecreatetruecolor($totalWidth, $totalHeight);
    $background = imagecolorallocatealpha($sprite, 255, 255, 255, 127);
    imagefill($sprite, 0, 0, $background);
    imagealphablending($img, false);
    imagesavealpha($sprite, true);

    $currentX = isset($options["p"]) && is_numeric($options["p"]) ? $options["p"] : 0 ;
    $currentY = isset($options["p"]) && is_numeric($options["p"]) ? $options["p"] : 0;
    foreach ($images as $img) {
        if(array_key_exists("p", $options) && is_numeric($options["p"])){
            imagecopy($sprite, $img, $currentX, $currentY, 0, 0, imagesx($img), imagesy($img));
            $currentX += imagesx($img)+$options["p"];
        }else if (array_key_exists("padding", $options) && is_numeric($options["padding"])){
            imagecopy($sprite, $img, $currentX, $currentY, 0, 0, imagesx($img), imagesy($img));
            $currentX += imagesx($img)+$options["padding"];
        }else{
            imagecopy($sprite, $img, $currentX, 0, 0, 0, imagesx($img), imagesy($img));
            $currentX += imagesx($img);  
        }
    }
    cssGenerate ($images, $options);

    imagepng($sprite, $output);

    foreach ($images as $img) {
        imagedestroy($img);
    }
    imagedestroy($sprite);

}

//Generation du Css
function cssGenerate ($sprite, $options){
    //option pour le Css
    $nameCSS = "";
    if(array_key_exists("s", $options)){
        $nameCSS .= $options["s"] . ".css";
    }elseif(array_key_exists("output-style", $options)) {
        $nameCSS .= $options["output-style"] . ".css";
    }else{
        $nameCSS .= "style.css";
    }
    //Création du Css et de son contenu
    $css = fopen("$nameCSS", "w");
    $content = "";
    $currentX = 0;
    static $i = 1; 
    foreach($sprite as $spriteData_){
        if(array_key_exists("p", $options) && is_numeric($options["p"])){
            $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)."px;\n    height: ".imagesy($spriteData_)."px;\n    padding: ".$options["p"]."px;\n}\n";
            $currentX += imagesx($spriteData_); 
        }else if (array_key_exists("padding", $options) && is_numeric($options["padding"])){
            $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)."px;\n    height: ".imagesy($spriteData_)."px;\n    padding: ".$options["padding"]."px;\n}\n";
            $currentX += imagesx($spriteData_); 
        }else {
            $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)."px;\n    height: ".imagesy($spriteData_)."px;\n}\n";
            $currentX += imagesx($spriteData_);            
        }
 
    }
    fwrite($css, $content);
    fclose($css);
}

//Creation de la tarball sprite
$namePNG = "";
if(array_key_exists("i", $options)){
    $namePNG .= $options["i"] . ".png";
}elseif(array_key_exists("output-image", $options)) {
    $namePNG .= $options["output-image"] . ".png";
}else{
    $namePNG .= "sprite.png";
}
$tarball = fopen("$namePNG", "w");

// recursive ou non 
$recursive = isset($options["r"]) || isset($options["recursive"]);
for($i = 1; $i < $argc; $i++){
    $path = $argv[$i];
    if (!$recursive){
        $imageFiles = (getAllImages($path));
    }else{
        $imageFiles = (getAllImagesRecursive($path, $imageFiles));
    }
}
concatenateImages($imageFiles, $tarball);
fclose($tarball);
