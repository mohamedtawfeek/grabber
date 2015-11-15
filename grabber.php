<?php

error_reporting(0);
/*
 * Copyright (c) 2015 by Mohamed Sayed 
 * email: mohameds.tawfeek@gmail.com
 * "PHP & Jquery image upload & crop"
 * Date: 2015-9-21
 * Ver 1.0
 */

class grabber {

//grab link 
    public function grabLink() {
        $link = filter_input(INPUT_POST, 'webUrl', FILTER_VALIDATE_URL);
        if (!empty($link)) {
            //get link contents
            $ch = curl_init($link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
            curl_close($ch);
            $content = html_entity_decode($output); //decode page
            return $content; //return decoded entites
        }
    }

    //get title from the link

    public function getTitle() {
        if (strlen($this->grabLink()) > 0) {
            //trim spaces
            $content = trim(preg_replace('/\s+/', ' ', $this->grabLink()));
            //find <title> pattern 
            preg_match("/(\\s+)?\\<title(\\s)?( )?([a-z]+)?([=][\\\"].+[\\\"])?\\s?\\>(.*)?\\<\\/title\\>(\\s)?/i", $content, $title); // ignore case
            if ($title) {
                return $title;
            }
        }
    }

    //get description from the link

    public function getDescription() {
        if (strlen($this->grabLink()) > 0) {
            //trim spaces

            $content = trim(preg_replace('/\s+/', ' ', $this->grabLink())); // supports line breaks inside <p>
            //get first <p> tag
            preg_match_all("/(<)(p)(\\s+)?([a-z]+)?([=][\"].+[\"])?([a-z])?([=][\"].+[\"])?\\s?\\>(.{150,})(<\\/)(p)(>)/Uim", $content, $ecp); // ignore case
            if ($ecp) {
                return $ecp;
            } else {
                echo 'Nothing';
            }
        }
    }

    //get image from the link
    public function getImage() {
        if (strlen($this->grabLink()) > 0) {
            //get all <img tag  .(jpg|gif|png)
            preg_match_all("/\\<img\\s?(.*)?([=][\"].+[\"])?(src)[=][\"].+(jpg|gif|png)[\"](.*)(\\/)?\\>/i", $this->grabLink(), $image); // ignore case
            if (is_array($image)) {
                return $image;
            }
        }
    }

//echo multi images
    public function imgResult() {
        $imgs = $this->getImage();
        if (is_array($imgs)) {
            foreach ($imgs as $value) {
                foreach ($value as $output) {
                    //grab image link 
                    preg_match("/(http:\\/\\/)(.*)(jpg|gif|png)/i", $output, $noimg);
                    if (array_key_exists(0, $noimg)) {
                        //getting width of image
                        $imageSize = getimagesize($noimg[0]);
                        if ($imageSize == TRUE) {
                            $imageArr = array($imageSize, $noimg[0]);
                            //if image width more than 350 save the image

                            if ($imageArr[0][0] > 350) {
                                $imgurl = $imageArr[1];
                                $imagename = basename($imgurl);
                                if (file_exists('./img/' . $imagename)) {
                                    unlink('img/' . $imagename);
                                }
                                $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
                                $headers[] = 'Connection: Keep-Alive';
                                $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
                                $process = curl_init($imgurl);
                                curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($process, CURLOPT_HEADER, 0);
                                curl_setopt($process, CURLOPT_TIMEOUT, 30);
                                curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($process, CURLOPT_FOLLOWLOCATION, true);
                                $return = curl_exec($process);
                                curl_close($process);
                                $image = $return;
                                //saving image 
                                file_put_contents('img/' . $imagename, $image);
                                return $imagename;
                            }
                        }
                    }
                }
            }
        }
    }

}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' )) {
    $grabber = new grabber();
    $desc = $grabber->getDescription();
    $descrption = strip_tags($desc[0][1]);
    $title1 = $grabber->getTitle();
    $title = strip_tags($title1[6]);
    $image = $grabber->imgResult();
    $result = array($title, $descrption, $image);
    echo json_encode($result);
} else {
    die('nothing here');
}


