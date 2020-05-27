/* global Promise, NRDB */

(function (ui, $) {

    ui.showBanner = function showBanner(message, type) {
        $('<div class="alert alert-' + (type || 'info') + '">').html(message).prependTo($('#wrapper>.container:first-child'));
    };

    ui.loadImage = function loadImage(image) {
        var image = this,
                src = image.getAttribute('data-src');
        return new Promise(function (resolve, reject) {
            image.addEventListener('load', resolve);
            image.removeAttribute('data-src');
            image.setAttribute('class', 'img-responsive');
            image.setAttribute('src', src);
        });
    };

    ui.setupAllImages = function setupAllImages() {
        $('img[data-src]').not('.lazyload .lazyloaded').on('click', ui.loadImage);
    };

    ui.loadAllImages = function loadAllImages() {
        return Promise.all($('img[data-src]').map(ui.loadImage).get());
    };

    ui.manageImages = function manageImages() {
        var images = $('img[data-src]');
        if(!images.length)
            return;

        var introduction = 'autoloadImages';

        if(NRDB.user.data.is_authenticated && !NRDB.user.data.introductions[introduction]) {
            // first time the user is presented with this feature
            // we load the images, then we display the Intro
            ui.loadAllImages().then(function () {
                var intro = introJs();
                intro.setOptions({
                    steps: [
                        {
                            element: images[0],
                            intro: "You can now prevent the autoloading of images in your <a href=\"" + Routing.generate('user_profile') + "\">User Account</a>. If you opt to, you'll still be able to load each image manually.",
                            position: "top"
                        }
                    ],
                    showStepNumbers: false,
                    showBullets: false,
                    showProgress: false
                });
                intro.start();
                $.post(Routing.generate('user_validate_introduction', {introduction: introduction}));
            });
        } else {
            if(!NRDB.user.data.is_authenticated || NRDB.user.data.autoload_images) {
                // if the user opted to load images, or didn't make a choice, leaving the default value
                ui.loadAllImages();
            } else {
                // if the user opted to not load images, we set them up for manual load by click
                ui.setupAllImages();
            }
        }
    };

    ui.promise = new Promise($);

    Promise.all([NRDB.user.promise, NRDB.data.promise]).then(ui.manageImages);

})(NRDB.ui = {}, jQuery);
