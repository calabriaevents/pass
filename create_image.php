<?php
$image = imagecreatetruecolor(100, 100);
$red = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $red);
imagepng($image, 'test_image.png');
imagedestroy($image);
echo "Test image created successfully.";
