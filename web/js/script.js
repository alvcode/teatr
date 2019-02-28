//Общие скрипты, подходящие для всех страниц
var NOTIF_RED = '#dc3545';
var NOTIF_GREEN = '#28a745';
var NOTIF_TEXT_ERROR = 'Ошибка! Обновите страницу и попробуйте еще раз. Если ничего не вышло- сообщите разработчику.';

//PRELOADER
function goPreloader() {
    $('.preloader-container').css('display', 'block');
}
function stopPreloader() {
    setTimeout(function () {
        $('.preloader-container').css('display', 'none');
    }, 500);
}

/**
 * Show Notification
 * @param text
 * @param time
 * @param color
 */
var notificationInterval = false;
var intervalCounter = 0;
var intervalStep = 500;
function showNotifications(text, time, color) {
    $('.pop-up-notification').css('background-color', color).html(text);
    $('.pop-up-notification').fadeIn(200);
    if (intervalCounter) {
        clearInterval(notificationInterval);
        intervalCounter = 0;
    }
    notificationInterval = setInterval(function () {
        if (intervalCounter >= time) {
            intervalCounter = 0;
            $('.pop-up-notification').fadeOut(200);
            $('.pop-up-notification').delay(200).html("");
            clearInterval(notificationInterval);
            notificationInterval = false;
        }
        intervalCounter = intervalCounter + intervalStep;
    }, intervalStep);
}

var clientHeight = document.documentElement.clientHeight;
var clientWidth = document.documentElement.clientWidth;
