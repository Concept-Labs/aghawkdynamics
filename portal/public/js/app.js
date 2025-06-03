
Portal = {
    init: function () {
    },
    notify: function (message, type) {
        document.getElementById('messages').innerHTML += '<div class="message ' + type + '"><p>' + message + '</p></div>';
        document.querySelectorAll('#messages .message').forEach(msg => {
            requestAnimationFrame(() => msg.classList.add('show'));


            setTimeout(() => {
                msg.classList.remove('show');

                setTimeout(() => msg.remove(), 250);
            }, 3000);
        });
    }

}

//Actions menu
document.addEventListener('DOMContentLoaded', () => {

    // Actions menu
    let openMenu = null;
    document.querySelectorAll('.action-btn').forEach(btn => {
        const menu = btn.parentElement.querySelector('.actions-menu');
        btn.addEventListener('click', e => {
            e.stopPropagation();
            if (openMenu && openMenu !== menu) {
                openMenu.classList.remove('show');
            }
            menu.classList.toggle('show');
            openMenu = menu.classList.contains('show') ? menu : null;
        });
    });
    document.addEventListener('click', () => {
        if (openMenu) {
            openMenu.classList.remove('show');
            openMenu = null;
        }
    });



    cancelRequest = function(id) {
        confirmAction('Are you sure you want to cancel this service request?')
            .then(confirmed => {
                if (confirmed) {
                    window.location.href = '/?q=service/cancel&id=' + id;
                }
            });
    }

    uncancelRequest = function(id) {
        confirmAction('Are you sure you want to restore this service request?')
            .then(confirmed => {
                if (confirmed) {
                    window.location.href = '/?q=service/uncancel&id=' + id;
                }
            });
    };

});

