(function ($) {
    $(document).ready(function () {
        $('.add-tag').click(function () {
            var tagInput = $(this).parent().find('.tag');
            var tagInputVal = tagInput.val();
            var tagsInput = $(this).parent().find('.tags');
            tagInputVal = tagInputVal.toLowerCase();
            if (isTagValid($(this), tagInputVal)) {
                var tagsCount = tagsInput.val().match(/,/g);
                if (tagsInput.val().indexOf(tagInputVal + ',') >= 0) {
                    alert('This tag already added');
                } else if (tagsCount != null && tagsCount.length >= 5) {
                    alert('Max 5 tags');
                } else {
                    tagsInput.val(tagsInput.val() + tagInputVal + ',');
                    var span = $('<span/>', {
                        class: 'glyphicon glyphicon-remove-circle added-tag',
                        text: tagInputVal
                    });
                    span.click(function () {
                        var tagText = $(this).text();
                        var tagsInput = $(this).parent().find('.tags');
                        tagsInput.val(tagsInput.val().replace(tagText + ',', ''));
                        $(this).remove();
                    });

                    $(this).parent().append(span);
                }
                tagInput.val('');
                $(this).parent().find('.unacceptable').show();
                $(this).parent().find('.acceptable').hide();
            } else {
                alert('One word with 3-12 characters');
            }
        });

        $('.added-tag')
            .click(function () {
                var tagText = $(this).text();
                var tagsInput = $(this).parent().find('.tags');
                tagsInput.val(tagsInput.val().replace(tagText + ',', ''));
                $(this).remove();
            })
            .each(function (index) {
                var tagsInput = $(this).parent().find('.tags');
                tagsInput.val(tagsInput.val() + $(this).text() + ',');
            });


        $('.tag').keyup(function () {
            var inputVal = $(this).val();
            isTagValid($(this), inputVal);
        });

        function isTagValid(_this, inputVal) {
            var numericReg = /^[A-Za-z]+$/;
            if (inputVal.length > 2 && inputVal.length < 13 && numericReg.test(inputVal)) {
                _this.parent().find('.unacceptable').hide();
                _this.parent().find('.acceptable').show();
                return true;
            } else {
                _this.parent().find('.unacceptable').show();
                _this.parent().find('.acceptable').hide();
                return false;
            }
        }
    });
})(jQuery);