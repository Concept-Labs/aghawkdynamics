
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

    // Inline search
    document.querySelectorAll('.grid .inline-search').forEach(i => {
        var grid = i.closest('.grid');
        // Find the column index for this search input
        const th = i.closest('th');
        const allThs = Array.from(th.parentElement.children);
        

        function filterRows() {
            const filters = Array.from(grid.querySelectorAll('.inline-search')).map(input => input.value.trim().toLowerCase());
            const rows = grid.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let show = true;
                filters.forEach((filter, idx) => {
                    if (filter && cells[idx] && !cells[idx].textContent.toLowerCase().includes(filter)) {
                        show = false;
                    }
                });
                row.style.display = show ? '' : 'none';
            });
        }

        i.addEventListener('input', filterRows);
        i.addEventListener('change', filterRows);
    });

});