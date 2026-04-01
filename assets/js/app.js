// EnterClinic — app.js

// Tabs
document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-tab');
            document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            var pane = document.getElementById(target);
            if (pane) pane.classList.add('active');
        });
    });

    // Activate tab from URL hash (e.g. #tab-prontuario)
    if (window.location.hash) {
        var hashId = window.location.hash.substring(1);
        var hashBtn = document.querySelector('[data-tab="' + hashId + '"]');
        if (hashBtn) hashBtn.click();
    }
});

// Confirm delete
function confirmDelete(form) {
    if (!confirm('Tem certeza que deseja excluir? Esta ação não pode ser desfeita.')) {
        return false;
    }
    return true;
}
