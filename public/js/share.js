(function ($) {
    $(document).ready(function () {
        $('.share-item').click(function () {
            var text = "Please make sure this image and your album are public and then use this link";
            var url = $(this).data("shareUrl");
            var body = $('#myModalShare').find('.modal-body');
            body.text("");
            body.append(text);
            body.append(url);
        });
    });
})(jQuery);