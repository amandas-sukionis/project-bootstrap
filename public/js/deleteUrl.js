(function ($) {
    $(document).ready(function () {
        $('.remove-item').click(function () {
            var url = $(this).data("deleteUrl");
            var removeBtn = $('#myModal').find('.btn-danger');
            removeBtn.attr('href', url);
        });
    });
})(jQuery);