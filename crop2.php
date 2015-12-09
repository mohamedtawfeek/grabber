<?php

class crop {

    private $src;
    private $type;

    public function cropThis() {

        //getting image from jquery using get
        $src = $_GET["image"];
        $type = exif_imagetype($src);

        if ($type) {
            $this->src = $src;
            $this->type = $type;
            $this->extension = image_type_to_extension($type);
        }
        //getting image type 
        switch ($this->type) {
            case IMAGETYPE_GIF:
                $src_img = imagecreatefromgif($src);
                break;

            case IMAGETYPE_JPEG:
                $src_img = imagecreatefromjpeg($src);
                break;

            case IMAGETYPE_PNG:
                $src_img = imagecreatefrompng($src);
                break;
        }
        //image width
        $tmp_img_w = $_GET["width"];
        //image height
        $tmp_img_h = $_GET["height"];
        $dst_img_w = 360;
        $dst_img_h = 300;
        $size = getimagesize($src);
        $size_w = $size[0]; // natural width
        $size_h = $size[1]; // natural height

        $src_img_w = $size_w;
        $src_img_h = $size_h;

        $src_x = $_GET["x"];
        $src_y = $_GET["y"];

        if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
            $src_x = $src_w = $dst_x = $dst_w = 0;
        } else if ($src_x <= 0) {
            $dst_x = -$src_x;
            $src_x = 0;
            $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
        } else if ($src_x <= $src_img_w) {
            $dst_x = 0;
            $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
        }

        if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
            $src_y = $src_h = $dst_y = $dst_h = 0;
        } else if ($src_y <= 0) {
            $dst_y = -$src_y;
            $src_y = 0;
            $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
        } else if ($src_y <= $src_img_h) {
            $dst_y = 0;
            $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
        }

// Scale to destination position and size
        $ratio = $tmp_img_w / $dst_img_w;
        $dst_x /= $ratio;
        $dst_y /= $ratio;
        $dst_w /= $ratio;
        $dst_h /= $ratio;

        $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

// Add transparent background to destination image
        imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
        imagesavealpha($dst_img, true);

        $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        $result2 = imagejpeg($dst_img, 'img/output/thumb' . date('YmdHis') . '.jpeg', 200);
        imagedestroy($dst_img);

        return 'output/thumb' . date('YmdHis') . '.jpeg';
    }

}
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' )) {

if (dirname($_GET["image"]) == 'output') {
    echo $_GET["image"];
} else {
    $crop = new crop();
    $imageSrc = $crop->cropThis();
    echo $imageSrc;
//delete natural image 
    unlink($_GET["image"]);

}
}else{
    echo 'you cannot access this file';
}