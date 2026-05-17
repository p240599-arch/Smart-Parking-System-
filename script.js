// ============================================================
// script.js - JavaScript File
// University Parking Management System
// Imtiaz Super Mart Peshawar
// ============================================================

/**
 * confirmDelete()
 * Shows a confirmation dialog before deleting any record.
 * Returns true if user clicks OK, false if user clicks Cancel.
 * Used on every delete link/button in the system.
 */
function confirmDelete(itemName) {
    var message = "Are you sure you want to delete this " + itemName + "?\n\nThis action cannot be undone.";
    return confirm(message);
}

/**
 * confirmAction()
 * Generic confirm dialog for any action.
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Auto-hide alert messages after 4 seconds
 */
window.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.display = 'none';
        }, 4000);
    });
});
