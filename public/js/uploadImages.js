(function ($) {
    function hideErrors() {
        $('#file-errors').hide();
    }

    function showErrors(message) {
        $('#file-errors').show().html(message);
    }

    function hideFormElements() {
        $('#uploadImageFile').hide();
        $('#uploadImageFormSubmit').hide();
    }

    function showFormElements() {
        $('#uploadImageFile').show();
        $('#uploadImageFormSubmit').show();
    }

    function showProgress(amount, message) {
        $('#progress').show();
        $('#progress .progress-bar').width(amount + '%');
        $('#progress > p').html(message);
        if (amount < 100) {
            $('#progress .progress')
                .addClass('active')
                .addClass('progress-info')
                .removeClass('progress-success');
        } else {
            $('#progress .progress')
                .removeClass('active')
                .removeClass('progress-info')
                .addClass('progress-success');
        }
    }

    function startProgress() {
        var bar = $('#progress').uploadProgressBar();
        bar.uploadProgressBar("option", "value", 0);
        bar.uploadProgressBar("startProgress");
    }

    function loadImageDiv(thumbUrl, alias) {
        $('<div/>', {
            'class': 'row', html: getImageContainerHtml(thumbUrl, alias)
        }).hide().appendTo('#loadedImages').slideDown('slow');
    }

    function getImageContainerHtml(thumbUrl, alias) {
        var $col = $('<div/>', {
            class: 'col-md-12'
        });

        var $saveImageContainer = $('<div/>', {
            class: 'save-image-container'
        });

        var $imageThumb = $('<img/>', {
            src: thumbUrl,
            class: 'img-thumbnail'
        });


        var $checkButton = $('<button/>', {
            type: 'submit',
            class: 'btn btn-default btn-xs pull-right save-image'
        });

        var $nameInput = $('<input/>', {
            type: 'text',
            name: 'name'
        });

        var $shortDescriptionInput = $('<input/>', {
            type: 'text',
            name: 'shortDescription'
        });

        var $form = $('<form/>', {
            action: finishUploadUrl + '/' + alias,
            class: 'saveImageInfo',
            method: 'POST'
        });

        $form.on('submit', function (e) {
            e.preventDefault();

            $(this).ajaxSubmit({
                success: function (response, statusText, xhr, $form) {

                },
                error: function (a, b, c) {
                    console.log(a, b, c);
                }
            });
        });

        $form.append($nameInput);
        $form.append($shortDescriptionInput);
        $form.append($checkButton);

        $checkButton.click(function () {
            $col.hide('slow');
        });

        var $iconSpan = $('<span/>', {
            class: 'glyphicon glyphicon-ok'
        });

        $checkButton.append($iconSpan);
        $saveImageContainer.append($imageThumb);
        $saveImageContainer.append($form);
        $col.append($saveImageContainer);

        return $col;
    }

    $('#uploadImageForm').on('submit', function (e) {
        e.preventDefault();

        if ($('#uploadImageFile').val() == '') {
            showErrors('No file(s) selected');
            return;
        }

        hideFormElements();
        hideErrors();

        $(this).ajaxSubmit({
            success: function (response, statusText, xhr, $form) {
                //console.log(response);
                $.each(response.images, function (index, value) {
                    loadImageDiv(value.thumbUrl, value.alias);
                });
                showFormElements();
            },
            error: function (a, b, c) {
                console.log(a, b, c);
                showFormElements();
            }
        });
        startProgress();
    });
})(jQuery);