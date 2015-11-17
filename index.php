<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="A complete example of Cropper.">
        <title>Grabber</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/cropper.min.css">
        <link rel="stylesheet" href="css/main.css">
        <link href="css/imgareaselect-default.css" rel="stylesheet" type="text/css"/>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="container" id="crop-avatar">
            <!-- Url Form -->
            <form  name="myForm" action="grabber.php"  id="myform" class="js-ajax-php-json" method="post" accept-charset="utf-8">
                <input type="url" name="webUrl" class="web" value="http://" required autofocus />
                <input type="submit" name="submit" class="urlSubmit" value="Done" />
            </form>
            <?php
            ?>
            <!-- Current avatar -->
            <div class="theView" style="display:none">
                <!-- Title Output -->
                <form action="index.php" method="post">
                    <label>title</label><textarea name="titleValue" class="title form-control"></textarea>
                    <!-- Descrption Output -->
                    <label>description</label><textarea name="descValue" class="desc form-control"></textarea>
                    <!-- image output -->
                    <input class="imgValue" name="imgValue" type="hidden" value="" />


                    <div class="avatar-view">
                        <img src="img/" alt="" id="photo">
                    </div>



                    <button class="avatar-view2" value="Change Image">Change Image</button>
                    <input type="button" id="cropbutton" value="Start Crop">
                    <input type="button" id="crop" class="" value="Crop" style="display:none">
                    <input type="submit" name="submit" value="Done" />

                </form>
                <?php
                $str = filter_input(INPUT_POST, 'imgValue');

                if (isset($str)) {
                    $re = "/(.+)[\\/](.+)[.](jpg|gif|png)/";
                    preg_match($re, $str, $matches);
                    if (dirname($str) == 'img') {
                        copy($str, 'output/' . $matches[2] . '.' . $matches[3]);
                        unlink($_POST['imgValue']);
                    }
                }
                ?>
            </div>
            <img src="loading.gif" id="ajax-loader" style="display:none"> 
            <!-- Cropping modal -->
            <div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form class="avatar-form" action="crop.php"  method="post">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="avatar-modal-label">Change Image</h4>
                            </div>
                            <div class="modal-body">
                                <div class="avatar-body">

                                    <!-- Upload image and data -->
                                    <div class="avatar-upload">
                                        <input type="hidden" class="avatar-src" name="avatar_src">
                                        <input type="hidden" class="avatar-data" name="avatar_data">
                                        <label for="avatarInput">Choose</label>
                                        <input type="file" class="avatar-input" id="avatarInput" name="avatar_file">
                                    </div>

                                    <!-- Crop and preview -->
                                    <div class="row">
                                        <div class="col-md-9">
                                            <input type="hidden" class="avatar-src" name="avatar_src">
                                            <input type="hidden" class="avatar-data" name="avatar_data">
                                            <div class="avatar-wrapper"></div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="avatar-preview preview-lg"></div>

                                        </div>
                                    </div>

                                    <div class="row avatar-btns">

                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary btn-block avatar-save">Done</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div> -->
                        </form>

                    </div>
                </div>
            </div><!-- /.modal -->

            <!-- Loading state -->
            <div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
        </div>

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/cropper.min.js"></script>
   
        <script src="js/main.js"></script>
        <script src="js/jquery.imgareaselect.pack.js" type="text/javascript"></script>
    </body>
</html>
