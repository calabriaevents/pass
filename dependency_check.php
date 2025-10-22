<?php
echo "Controllo dipendenze...\n";
if (extension_loaded('gd')) {
    echo "Libreria GD: Trovata.\n";
} else {
    echo "Libreria GD: NON Trovata.\n";
}

if (extension_loaded('imagick')) {
    echo "Libreria Imagick: Trovata.\n";
} else {
    echo "Libreria Imagick: NON Trovata.\n";
}
?>