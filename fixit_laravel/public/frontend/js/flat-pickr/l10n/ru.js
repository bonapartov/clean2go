/* flatpickr Russian locale, @license MIT */
(function (global, factory) {
    typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() :
    typeof define === "function" && define.amd ? define(factory) :
    (global = typeof globalThis !== "undefined" ? globalThis : global || self, (global.flatpickr = global.flatpickr || {}, global.flatpickr.l10ns = global.flatpickr.l10ns || {}, global.flatpickr.l10ns.ru = factory()));
}(this, (function () {
    "use strict";

    var fp = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : { l10ns: {} };

    var Russian = {
        weekdays: {
            shorthand: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            longhand: [
                "Воскресенье",
                "Понедельник",
                "Вторник",
                "Среда",
                "Четверг",
                "Пятница",
                "Суббота",
            ],
        },
        months: {
            shorthand: [
                "Янв", "Фев", "Мар", "Апр", "Май", "Июн",
                "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек",
            ],
            longhand: [
                "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
                "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Нед",
        scrollTitle: "Прокрутите для увеличения",
        toggleTitle: "Нажмите для переключения",
        amPM: ["ДП", "ПП"],
        yearAriaLabel: "Год",
        monthAriaLabel: "Месяц",
        hourAriaLabel: "Час",
        minuteAriaLabel: "Минуты",
        time_24hr: true,
    };

    fp.l10ns.ru = Russian;

    return fp.l10ns.ru;
})));
