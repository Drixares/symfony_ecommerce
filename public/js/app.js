const toasts = document.querySelectorAll('.toast');

toasts.forEach(toast => {
    setTimeout(() => {
        toast.animate([
            {
                opacity: 1,
            },
            {
                opacity: 0,
                top: "15px",
            }
        ], {duration: 200, easing: 'ease-in-out', fill: 'forwards'});
        setTimeout(() => {
            toast.parentNode.removeChild(toast);
        }, 1000);
    }, 2000);
});
