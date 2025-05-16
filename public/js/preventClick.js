// منع الكلك يمين
document.addEventListener("contextmenu", function (event) {
    event.preventDefault();
});

// منع F12 و Ctrl+Shift+I و Ctrl+Shift+J و Ctrl+U
document.addEventListener("keydown", function (event) {
    if (event.keyCode === 123) {
        // F12
        event.preventDefault();
    }
    if (event.ctrlKey && event.shiftKey && event.keyCode === 73) {
        // Ctrl+Shift+I
        event.preventDefault();
    }
    if (event.ctrlKey && event.shiftKey && event.keyCode === 74) {
        // Ctrl+Shift+J
        event.preventDefault();
    }
    if (event.ctrlKey && event.keyCode === 85) {
        // Ctrl+U
        event.preventDefault();
    }
    });
    // كود التشويش
    // كود التشويش (في الـ global scope)
//  
// كود بسيط: أي أمر في الكونسول ينفذ تسجيل الخروج مع إشعار أمني

function showOverlayAndLogout() {
    var csrf = document.querySelector('meta[name="csrf-token"]');
    if (!csrf) {
        alert("CSRF Token غير موجود في الصفحة!");
        return;
    }
    fetch("/devtools-logout", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrf.content,
            "Accept": "application/json"
        }
    }).then(() => {
        // alert("تم تسجيل خروجك بسبب محاولة فحص الصفحة.");
        window.location.href = "/login";
    });
}

// مراقبة الكونسول (في الـ global scope)
// ["log", "warn", "error", "info", "debug", "table", "clear"].forEach(function (method) {
//     const original = console[method];
//     console[method] = function () {
//         showOverlayAndLogout();
//         return original.apply(console, arguments);
//     };
// });
