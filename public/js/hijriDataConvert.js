// Converts Gregorian dates to Hijri
function convertToHijri() {
    document.querySelectorAll(".hijri-date").forEach((element) => {
        const gregorianDate = element.getAttribute("data-date");
        if (gregorianDate) {
            try {
                // Use Intl.DateTimeFormat with 'islamic' calendar - month as LONG text
                const hijriDate = new Intl.DateTimeFormat("ar-SA-islamic", {
                    day: "numeric",
                    month: "long", // تم تغييرها من 'numeric' إلى 'long'
                    calendar: "islamic",
                }).format(new Date(gregorianDate));

                element.textContent = hijriDate;
            } catch (e) {
                console.error("Error converting date:", e);
                element.textContent = ""; // Clear if error
            }
        }
    });
}

// Convert dates when page loads
document.addEventListener("DOMContentLoaded", function () {
    convertToHijri();

    // Also convert when table is updated via AJAX
    document.addEventListener("ajaxTableUpdated", convertToHijri);
});
