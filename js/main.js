(function (factory) {
    if (typeof define === 'function' && define.amd) {
// AMD. Register as anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
// Node / CommonJS
        factory(require('jquery'));
    } else {
// Browser globals.
        factory(jQuery);
    }
})(function ($) {

    'use strict';
    var console = window.console || {log: function () {
        }};
    function CropAvatar($element) {
        this.$container = $element;
        this.$avatarView = this.$container.find('.avatar-view');
        this.$avatarView2 = this.$container.find('.avatar-view2');
        this.$avatar = this.$avatarView.find('img');
        this.$avatarModal = this.$container.find('#avatar-modal');
        this.$loading = this.$container.find('.loading');
        this.$avatarForm = this.$avatarModal.find('.avatar-form');
        this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
        this.$avatarSrc = this.$avatarForm.find('.avatar-src');
        this.$avatarData = this.$avatarForm.find('.avatar-data');
        this.$avatarInput = this.$avatarForm.find('.avatar-input');
        this.$avatarSave = this.$avatarForm.find('.avatar-save');
        this.$avatarBtns = this.$avatarForm.find('.avatar-btns');
        this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
        this.$avatarPreview = this.$avatarModal.find('.avatar-preview');
        this.init();
    }

    CropAvatar.prototype = {
        constructor: CropAvatar,
        support: {
            fileList: !!$('<input type="file">').prop('files'),
            blobURLs: !!window.URL && URL.createObjectURL,
            formData: !!window.FormData
        },
        init: function () {
            this.support.datauri = this.support.fileList && this.support.blobURLs;
            if (!this.support.formData) {
                this.initIframe();
            }

            this.initTooltip();
            this.initModal();
            this.addListener();
        },
        addListener: function () {
            this.$avatarView2.on('click', $.proxy(this.click, this));
            this.$avatarInput.on('change', $.proxy(this.change, this));
            this.$avatarForm.on('submit', $.proxy(this.submit, this));
            this.$avatarBtns.on('click', $.proxy(this.rotate, this));
        },
        initTooltip: function () {
            this.$avatarView.tooltip({
                placement: 'bottom'
            });
        },
        initModal: function () {
            this.$avatarModal.modal({
                show: false
            });
        },
        initPreview: function () {
            var url = this.$avatar.attr('src');
            this.$avatarPreview.html('<img src="' + url + '">');
        },
        initIframe: function () {
            var target = 'upload-iframe-' + (new Date()).getTime();
            var $iframe = $('<iframe>').attr({
                name: target,
                src: ''
            });
            var _this = this;
            // Ready ifrmae
            $iframe.one('load', function () {

                // respond response
                $iframe.on('load', function () {
                    var data;
                    try {
                        data = $(this).contents().find('body').text();
                    } catch (e) {
                        console.log(e.message);
                    }

                    if (data) {
                        try {
                            data = $.parseJSON(data);
                        } catch (e) {
                            console.log(e.message);
                        }

                        _this.submitDone(data);
                    } else {
                        _this.submitFail('Image upload failed!');
                    }

                    _this.submitEnd();
                });
            });
            this.$iframe = $iframe;
            this.$avatarForm.attr('target', target).after($iframe.hide());
        },
        click: function () {
            this.$avatarModal.modal('show');
            this.initPreview();
        },
        change: function () {
            var files;
            var file;
            if (this.support.datauri) {
                files = this.$avatarInput.prop('files');
                if (files.length > 0) {
                    file = files[0];
                    if (this.isImageFile(file)) {
                        if (this.url) {
                            URL.revokeObjectURL(this.url); // Revoke the old one
                        }

                        this.url = URL.createObjectURL(file);
                        this.startCropper();
                    }
                }
            } else {
                file = this.$avatarInput.val();
                if (this.isImageFile(file)) {
                    this.syncUpload();
                }
            }
        },
        submit: function () {
            $('.avatar-view').show(1000);
            $('#cropbutton').show(1000);
            $('#crop').hide(1000);

            $('.avatar-view2').html('Change Image');

            if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
                return false;
            }

            if (this.support.formData) {
                this.ajaxUpload();
                return false;
            }
        },
        rotate: function (e) {
            var data;
            if (this.active) {
                data = $(e.target).data();
                if (data.method) {
                    this.$img.cropper(data.method, data.option);
                }
            }
        },
        isImageFile: function (file) {
            if (file.type) {
                return /^image\/\w+$/.test(file.type);
            } else {
                return /\.(jpg|jpeg|png|gif)$/.test(file);
            }
        },
        startCropper: function () {
            var _this = this;
            if (this.active) {
                this.$img.cropper('replace', this.url);
            } else {
                this.$img = $('<img src="' + this.url + '">');
                this.$avatarWrapper.empty().html(this.$img);
                this.$img.cropper({
                    aspectRatio: 1.2 / 1,
                    strict: false,
                    guides: false,
                    highlight: false,
                    dragCrop: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    preview: this.$avatarPreview.selector,
                    crop: function (e) {
                        var json = [
                            '{"x":' + e.x,
                            '"y":' + e.y,
                            '"height":' + e.height,
                            '"width":' + e.width,
                            '"rotate":' + e.rotate + '}'
                        ].join();
                        _this.$avatarData.val(json);
                    }
                });
                this.active = true;
            }

            this.$avatarModal.one('hidden.bs.modal', function () {
                _this.$avatarPreview.empty();
                _this.stopCropper();
            });
        },
        stopCropper: function () {
            if (this.active) {
                this.$img.cropper('destroy');
                this.$img.remove();
                this.active = false;
            }
        },
        ajaxUpload: function () {
            var url = this.$avatarForm.attr('action');
            var data = new FormData(this.$avatarForm[0]);
            var _this = this;
            $.ajax(url, {
                type: 'post',
                data: data,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function () {
                    _this.submitStart();
                },
                success: function (data) {
                    _this.submitDone(data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    _this.submitFail(textStatus || errorThrown);
                },
                complete: function () {
                    _this.submitEnd();
                }
            });
        },
        syncUpload: function () {
            this.$avatarSave.hover();
        },
        submitStart: function () {
            this.$loading.fadeIn();
        },
        submitDone: function (data) {
            console.log(data);
            if ($.isPlainObject(data) && data.state === 200) {
                if (data.result) {
                    this.url = data.result;
                    if (this.support.datauri || this.uploaded) {
                        this.uploaded = false;
                        this.cropDone();
                    } else {
                        this.uploaded = true;
                        this.$avatarSrc.val(this.url);
                        this.startCropper();
                    }

                    this.$avatarInput.val('');
                } else if (data.message) {
                    this.alert(data.message);
                }
            } else {
                this.alert('Failed to response');
            }
        },
        submitFail: function (msg) {
            this.alert(msg);
        },
        submitEnd: function () {
            this.$loading.fadeOut();
        },
        cropDone: function () {
            $('.imgValue').val(this.url);
            this.$avatarForm.get(0).reset();
            this.$avatar.attr('src', this.url);
            this.stopCropper();
            this.$avatarModal.modal('hide');
        },
        alert: function (msg) {
            var $alert = [
                '<div class="alert alert-danger avatar-alert alert-dismissable">',
                '<button type="button" class="close" data-dismiss="alert">&times;</button>',
                msg,
                '</div>'
            ].join('');
            this.$avatarUpload.after($alert);
        }
    };
    $(function () {
        return new CropAvatar($('#crop-avatar'));
    });
});
$(document).ready(function () {


    $('#cropbutton').click(function () {
        $('#crop').show(500);
        $('#cropbutton').hide(500);

        var selection = $('#photo').imgAreaSelect({
            aspectRatio: '1.2:1',
            handles: true,
            instance: true,
            x1: 50, y1: 40, x2: 320, y2: 280
        });

        $("#crop").click(function () {
            var width = selection.getSelection().width;
            var height = selection.getSelection().height;
            var x = selection.getSelection().x1;
            var y = selection.getSelection().y1;
            var image = $("#photo");
            var loader = $("#ajax-loader");
            if (height > 80) {
                var request = $.ajax({
                    url: "crop2.php",
                    type: "GET",
                    data: {
                        x: x,
                        y: y,
                        height: height,
                        width: width,
                        image: image.attr("src")
                    },
                    beforeSend: function () {
                        loader.show();
                    }
                }).done(function (msg) {
                    $('.imgValue').val(msg);
                    image.attr("src", msg);
                    $('#crop').hide(500);
                    $('#cropbutton').remove();
                    loader.hide();
                    selection.cancelSelection();
                    $('#photo').imgAreaSelect({
                        remove: true
                    });
                });
            }
        });
    });

    $(".js-ajax-php-json").submit(function () {
        var loader2 = $("#ajax-loader");
        var image = $("#photo");

        var data = $(this).serialize();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "grabber.php", //Relative or absolute path to response.php file
            data: data,
            beforeSend: function () {
                loader2.show();
            },
            success: function (data) {
                $('.theView').show();
                loader2.hide();
                if (!data[0]) {
                    $('.titlebtn').show(500);

                } else {
                    $(".title").html(data[0]);
                }
                if (!data[1]) {
                    $('.descbutton').show(500);

                } else {
                    $(".desc").html(data[1]);
                }

                if (!data[2]) {
                    $('#cropbutton').hide();

                    $('.avatar-view').hide();
                    $('#crop').hide();
                    $('.avatar-view2').html('Add Image');
                } else {
                    $('#cropbutton').show(500);
                    $('#crop').hide(500);
                    $('.imgValue').val('img/' + data[2]);

                    image.attr("src", 'img/' + data[2]);
                }

            }
        });
        return false;
    });
});



