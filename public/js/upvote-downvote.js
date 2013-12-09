(function ($) {
    $(document).ready(function () {
        $('.up-vote').click(function () {
            var url = $(this).data("upvoteUrl");
            var _this = $(this);
            $.getJSON(url, function( data ) {
                console.log(data);
                if (data.status == "login") {
                    alert('Please log in to vote');
                } else if (data.status == "voted") {
                    _this.parent().find('.score').text(data.voteCount);
                } else if (data.status == "ok") {
                    _this.parent().find('.score').text(data.voteCount);
                } else if (data.status == "public") {
                    alert('Not public');
                } else {
                    console.log(data);
                }
            });
        });

        $('.down-vote').click(function () {
            var url = $(this).data("downvoteUrl");
            var _this = $(this);
            $.getJSON(url, function( data ) {
                console.log(data);
                if (data.status == "login") {
                    alert('Please log in to vote');
                } else if (data.status == "voted") {
                    _this.parent().find('.score').text(data.voteCount);
                } else if (data.status == "ok") {
                    _this.parent().find('.score').text(data.voteCount);
                } else if (data.status == "public") {
                    alert('Not public');
                } else {
                    console.log(data);
                }
            });
        });
    });
})(jQuery);