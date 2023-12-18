<?php
if ($argc < 2 ){
    exit("Veuillez entrer un fichier\n");
}
//Liste de paramètres de la fonction geropt
$shortopts  = "";
$shortopts .= "r:";
$shortopts .= "i:";
$shortopts .= "s:";
$shortopts .= "p:";
$shortopts .= "o:";
$shortopts .= "c:";

$longopts  = array(
    "recursive:",     
    "output-image:",   
    "output-style:", 
    "padding:", 
    "override-size:", 
    "columns_number:",            
);
$options = getopt($shortopts, $longopts);

//Récupère tout les images png sans recursive
function getAllImages($folder){
    $imageFiles = [];
    $pngFiles = glob($folder . '*.png');
    foreach ($pngFiles as $pngFile) {
        if (is_file($pngFile)) {
            $imageFiles[] = $pngFile;
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

//option Recursive ou non
if(isset($options["r"]) || isset($options["recursive"]) ){
    $imageFiles = getAllImagesRecursive($argv[2], $imageFiles);
}else {
    $imageFiles = getAllImages($argv[1]);
}

//Concatener les images
function concatenateImages($imageFiles, $output) {
    global $options;
    if (empty($imageFiles)) {
        die('Pas d\'image à concaténer.');
    }
    $images = [];
    foreach ($imageFiles as $imageFile) {
        $img = imagecreatefrompng($imageFile);
        $images[] = $img;
    }
    
    $totalWidth = 0;
    $totalHeight = 0;
    
    foreach ($images as $img) {
        $totalWidth += imagesx($img);
        $totalHeight = max($totalHeight, imagesy($img));
    }

    $sprite = imagecreatetruecolor($totalWidth, $totalHeight);
    $background = imagecolorallocatealpha($sprite, 255, 255, 255, 127);
    imagefill($sprite, 0, 0, $background);
    imagealphablending($img, false);
    imagesavealpha($sprite, true);

    $currentX = 0;
    foreach ($images as $img) {
        imagecopy($sprite, $img, $currentX, 0, 0, 0, imagesx($img), imagesy($img));
        $currentX += imagesx($img);
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
        $nameCSS .= $options["s"];
    }elseif(array_key_exists("output-style", $options)) {
        $nameCSS .= $options["output-style"];
    }else{
        $nameCSS .= "style.css";
    }
    //Création du Css et de son contenu
    $css = fopen("$nameCSS", "w");
    $content = "";
    $currentX = 0;
    static $i = 1; 
    foreach($sprite as $spriteData_){
        // if(array_key_exists("p", $options) && is_numeric($options["p"])){
        //     $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)+$options["p"]."px;\n    height: ".imagesy($spriteData_)."px;\n}\n";
        //     $currentX += imagesx($spriteData_)+$options["p"];
        //     echo $currentX;
        // }elseif(array_key_exists("padding", $options) && is_numeric($options["padding"])) {
        //     $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)."px;\n    height: ".imagesy($spriteData_)."px;\n    padding: ".$options["padding"]."px;\n}\n";
        //     $currentX += imagesx($spriteData_) + $options["padding"];
        // }else{
            $content.= ".sprite_".$i++." {\n    background-position: -$currentX"."px 0;\n    width: ".imagesx($spriteData_)."px;\n    height: ".imagesy($spriteData_)."px;\n}\n";
            $currentX += imagesx($spriteData_);
        // }
    }
    fwrite($css, $content);
    fclose($css);
}

//Creation de la tarball sprite
$namePNG = "";
if(array_key_exists("i", $options) && str_ends_with($options["i"], ".png")){
    $namePNG .= $options["i"];
}elseif(array_key_exists("output-image", $options) && str_ends_with($options["output-image"], ".png")) {
    $namePNG .= $options["output-image"];
}else{
    $namePNG .= "sprite.png";
}
$tarball = fopen("$namePNG", "w");
// fclose($tarball);

concatenateImages($imageFiles, $tarball);
fclose($tarball);
