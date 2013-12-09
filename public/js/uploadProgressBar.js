(function ($) {

    $.widget("nfq.uploadProgressBar", {

        options: {
            value: 0
        },

        _create: function () {

            this.element.addClass("help-block");

            var $container = $('<div/>', {
                class: 'progress progress-striped active'
            }).appendTo(this.element);
            $('<div/>', {
                class: 'progress-bar',
                role: 'progressbar',
                text: 'Uploading...'
            }).appendTo($container);

            this.element.hide();
        },

        _setOption: function (key, value) {
            this.options[ key ] = value;
            this._update();
        },

        _update: function () {
            this.element.show();
            this.element.find('.progress-bar').width(this.options.value + '%');
        },

        startProgress: function() {
            var _this = this;
            var interval = setInterval(function(){
                 _this.getProgress();
            },400);
            _this.options['interval'] = interval;
        },

        getProgress: function() {
            var url = '/admin/upload-progress?id=' + $('#progress_key').val();
            var _this = this;

            $.getJSON(url, function (data) {
                console.log(data);
                if (data.status && !data.status.done) {
                    _this._setOption("value", Math.floor((data.status.current / data.status.total) * 100));
                    _this._update();
                } else {
                    _this._setOption("value", 100);
                    _this._update();
                    clearInterval(_this.options.interval);
                }
            });
        },

        destroy: function () {
            this.element.hide();

            $.Widget.prototype.destroy.call(this);
        }

    });
})(jQuery);