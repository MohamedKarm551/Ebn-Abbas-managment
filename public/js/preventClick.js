    // منع الكلك يمين
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    });

    // منع F12 و Ctrl+Shift+I و Ctrl+Shift+J و Ctrl+U
    document.addEventListener('keydown', function(event) {
        if (event.keyCode === 123) { // F12
            event.preventDefault();
        }
        if (event.ctrlKey && event.shiftKey && event.keyCode === 73) { // Ctrl+Shift+I
            event.preventDefault();
        }
        if (event.ctrlKey && event.shiftKey && event.keyCode === 74) { // Ctrl+Shift+J
            event.preventDefault();
        }
        if (event.ctrlKey && event.keyCode === 85) { // Ctrl+U
            event.preventDefault();
        }
    });
