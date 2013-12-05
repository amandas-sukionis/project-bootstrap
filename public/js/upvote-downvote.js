(function ($) {
    $(document).ready(function () {
        $('.up-vote').click(function () {
            var url = $(this).data("upvoteUrl");
            alert(url);
        });

        $('.down-vote').click(function () {
            var url = $(this).data("downvoteUrl");
            alert(url);
        });
    });
})(jQuery);