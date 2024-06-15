# CSS Generator en PHP - Générateur de Sprites et de Feuilles de Style CSS

## Introduction
CSS Generator est un programme en PHP qui permet de concaténer plusieurs images au format PNG en un seul sprite et de générer un fichier CSS correspondant pour une utilisation facile dans des projets HTML. Ce projet est conçu pour fonctionner sans utiliser la fonction `scandir` ou les classes itératrices telles que `RecursiveDirectoryIterator`.

## Prérequis
Assurez-vous d'avoir les éléments suivants installés sur votre machine :
- PHP 7.4 ou plus récent
- Bibliothèque GD pour la manipulation d'images

## Installation
Clonez le répertoire du projet et installez les dépendances nécessaires.

### 1. Cloner le Répertoire du Projet
```bash
git clone https://github.com/votre-repo/css_generator.git
cd css_generator
```

### 2. Configurer l'Environnement
Assurez-vous que votre environnement PHP est configuré pour utiliser la bibliothèque GD.

## Utilisation
Le programme `css_generator` peut être utilisé via la ligne de commande. Il prend en entrée un dossier contenant des images PNG et génère un sprite ainsi qu'un fichier CSS correspondant.

### Syntaxe
```bash
php css_generator.php [OPTIONS] assets_folder
```

### Options
- `-r, --recursive` : Recherche les images dans le dossier `assets_folder` et tous ses sous-dossiers.
- `-i, --output-image=IMAGE` : Nom de l'image générée. Par défaut, le nom est `sprite.png`.
- `-s, --output-style=STYLE` : Nom de la feuille de style générée. Par défaut, le nom est `style.css`.

### Exemple
Pour générer un sprite à partir des images dans le dossier `assets` et créer les fichiers `mysprite.png` et `mystyle.css` :
```bash
php css_generator.php -r -i mysprite.png -s mystyle.css assets
```

## Structure du Projet
- `css_generator.php` : Script principal pour la génération de sprites et de fichiers CSS.
- `assets/` : Dossier contenant les images PNG à concaténer.
- `output/` : Dossier où les fichiers générés (sprite et CSS) seront sauvegardés.

